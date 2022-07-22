<?php

namespace App\Process;

use App\Model\Agents;
use App\Model\Canals;
use App\Model\Trades;
use Carbon\Carbon;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class UserSettlement extends AbstractProcess{
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
                            $task = $redis->lPop('queue:user-settlement');
                            if (!$task) {
                                break;
                            }
                            $task = unjson($task);
                            if($task && $task['type']){
                                if($task['type'] == 'agent'){
                                    $userModel = Agents::create();
                                }else{
                                    $userModel = Canals::create();
                                }
                                $res = $userModel->update(['balance' => QueryBuilder::dec($task['balance'])],['id' => $task['id']]);
                                if($res){
                                    $insert = [
                                        'date' => date('Y-m-d'),
                                        'type' => $task['type'],
                                        'userid' => $task['id'],
                                        'username' => $task['username'],
                                        'money' => $task['balance'],
                                        'name' => $task['name'],
                                        'bank' => $task['bank'],
                                        'card' =>$task['card'],
                                        'created_at' => Carbon::now()
                                    ];
                                    Trades::create()->data($insert,false)->save();
                                }
                            }else{
                                break;
                            }
                        }catch (\Throwable $e){
                            write_log('user-settlement:' . $e->getMessage());
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
