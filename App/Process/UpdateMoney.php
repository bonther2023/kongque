<?php

namespace App\Process;

use ank\IpLookup;
use App\Model\Agents;
use App\Model\Canals;
use App\Model\RecordLogs;
use App\Model\Records;
use App\Model\Users;
use App\Model\VideoBilis;
use App\Model\VideoDefaults;
use Carbon\Carbon;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class UpdateMoney extends AbstractProcess{
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
                            $task = $redis->lPop('queue:update-money');
                            if (!$task) {
                                break;
                            }
                            $task = unjson($task);
                            if($task && $task['money'] && $task['rid']){
                                //代理提成
                                $agentProfit = number_format($task['money'] * $task['agentRebate'] * 0.01,2);
                                //渠道提成
                                $canalProfit = number_format($task['money'] * $task['canalRebate'] * 0.01,2);
                                //计算利润
                                $profit = $task['money'] - $agentProfit - $canalProfit;
                                //更新代理提成
                                Agents::create()->update([
                                    'balance' => QueryBuilder::inc($agentProfit),
                                ],['id' => $task['agent_id']]);
                                //更新渠道提成
                                Canals::create()->update([
                                    'balance' => QueryBuilder::inc($canalProfit),
                                ],['id' => $task['canal_id']]);
                                //平台利润
                                Records::create()->update([
                                    'payable' => QueryBuilder::inc($agentProfit + $canalProfit),
                                    'settlement' => QueryBuilder::inc(),
                                    'profit' => QueryBuilder::inc($profit),
                                    'money' => QueryBuilder::inc($agentProfit),
                                    'rebate' => QueryBuilder::inc($canalProfit),
                                ],['id' => $task['rid']]);
                            }else{
                                break;
                            }
                        }catch (\Throwable $e){
                            write_log('update-money:' . $e->getMessage());
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
