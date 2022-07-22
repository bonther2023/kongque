<?php

namespace App\Process;

use ank\IpLookup;
use App\Model\RecordLogs;
use App\Model\Records;
use App\Model\Users;
use App\Model\VideoBilis;
use App\Model\VideoDefaults;
use Carbon\Carbon;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class UpdateOrder extends AbstractProcess{
    private $isRun = false;
    public function run($arg){
        //定时500ms检测有没有任务，有的话就while死循环执行
        $this->addTick(500,function (){
            if(!$this->isRun){
                $this->isRun = true;
                go(function (){
                    $redis = RedisPool::defer('redis');
                    while (true){
                        try{
                            $task = $redis->lPop('queue:update-order');
                            if (!$task) {
                                break;
                            }
                            $task = unjson($task);
                            if($task && $task['aid'] && $task['cid']){
                                //结算记录
                                $date = date('Y-m-d',$task['time']);
                                $key = 'record:'.$date.'_aid_'.$task['aid'].'_cid_'.$task['cid'];
                                $model = Records::create();
                                if(!$redis->exists($key)){
                                    // 首次设定过期时间
                                    $log = [
                                        'date' => $date,
                                        'agent_id' => $task['aid'],
                                        'canal_id' => $task['cid'],
                                    ];
                                    $recordId = $model->data($log,false)->save();
                                    if($recordId){
                                        $redis->set($key, $recordId, 24*3600);
                                    }
                                }

                                //小时记录
                                $hour = date('H',$task['time']);
                                $logModel = RecordLogs::create();
                                $logKey = 'record_log:'.$date.'_hour_'.$hour.'_aid_'.$task['aid'].'_cid_'.$task['cid'];
                                if(!$redis->exists($logKey)){
                                    $rlog = [
                                        'date' => $date,
                                        'agent_id' => $task['aid'],
                                        'canal_id' => $task['cid'],
                                        'hour' => $hour,
                                    ];
                                    $recordLogId = $logModel->data($rlog,false)->save();
                                    if($recordLogId){
                                        $redis->set($logKey, $recordLogId, 3600);
                                    }
                                }
                                $recordLogId = $redis->get($logKey);
                                $logModel->update(['orders' => QueryBuilder::inc()],['id' => $recordLogId]);
                            }else{
                                break;
                            }
                        }catch (\Throwable $e){
                            write_log('update-order:' . $e->getMessage());
                            break;
                        }
                    }
                    $this->isRun = false;
                });
            }
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str, ...$args)
    {
        // TODO: Implement onReceive() method.
    }
}
