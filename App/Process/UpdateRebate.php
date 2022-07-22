<?php

namespace App\Process;

use App\Model\Agents;
use App\Model\Canals;
use App\Model\Orders;
use App\Model\RecordLogs;
use App\Model\Records;
use App\Model\Users;
use Carbon\Carbon;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class UpdateRebate extends AbstractProcess{
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
                            $task = $redis->lPop('queue:update-rebate');
                            if (!$task) {
                                break;
                            }
                            $task = unjson($task);
                            if($task && $task['oid']){
                                $model = Records::create();
                                $orderModel = Orders::create();
                                $agentModel = Agents::create();
                                $canalModel = Canals::create();
                                $logModel = RecordLogs::create();
                                //订单信息
                                $info = $orderModel->get($task['oid']);
                                //代理信息
                                $agentInfo = $agentModel->get($info['agent_id']);
                                //渠道信息
                                $canalInfo = $canalModel->get($info['canal_id']);
                                //统计结算
                                $date = Carbon::parse($info['created_at'])->format('Y-m-d');
                                $key = 'record:'.$date.'_aid_'.$info['agent_id'].'_cid_'.$info['canal_id'];
                                $recordId = $redis->get($key);
                                //免单内不扣量
                                if ($canalInfo['order_num'] >= $canalInfo['amount_new_user']) {
                                    //购买会员VIP等级
                                    $effect = $canalInfo[$info['vip'] . '_rebate'];
                                    //结算
                                    $deduction = deduction($effect);
                                    if ($deduction == 'settlement'){
                                        $job = json([
                                            'rid' => $recordId,//日志记录ID
                                            'money' => $info['money'],//订单价格
                                            'canalRebate' => $canalInfo['canal_rebate'],//渠道分成
                                            'agent_id' => $info['agent_id'],
                                            'canal_id' => $info['canal_id'],
                                            'agentRebate' => $agentInfo['rebate'],//代理分成
                                        ]);
                                        $redis->rPush('queue:update-money',$job);
                                    }else{
                                        //扣量
                                        $model->update([
                                            'profit' => QueryBuilder::inc($info['money']),
                                            'deduct' => QueryBuilder::inc()
                                        ],['id' => $recordId]);
                                        $info->update(['deduct' => 2]);
                                    }
                                } else {
                                    $job = json([
                                        'rid' => $recordId,//日志记录ID
                                        'money' => $info['money'],//订单价格
                                        'canalRebate' => $canalInfo['canal_rebate'],//渠道分成
                                        'agent_id' => $info['agent_id'],
                                        'canal_id' => $info['canal_id'],
                                        'agentRebate' => $agentInfo['rebate'],//代理分成
                                    ]);
                                    $redis->rPush('queue:update-money',$job);
                                    //新增渠道订单数量
                                    $canalInfo->update(['order_num' => QueryBuilder::inc()]);
                                }

                                //小时记录
                                $hour = Carbon::parse($info['created_at'])->format('H');
                                $logKey = 'record_log:'.$date.'_hour_'.$hour.'_aid_'.$info['agent_id'].'_cid_'.$info['canal_id'];
                                $recordLogId = $redis->get($logKey);
                                $logModel->update(['order_pays' => QueryBuilder::inc()],['id' => $recordLogId]);
                            }else{
                                break;
                            }
                        }catch (\Throwable $e){
                            write_log('update-rebate:' . $e->getMessage());
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
