<?php

namespace App\Process;

use App\Model\Orders;
use App\Model\Users;
use Carbon\Carbon;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\RedisPool\RedisPool;

class UpdateUser extends AbstractProcess{
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
                            $task = $redis->lPop('queue:update-user');
                            if (!$task) {
                                break;
                            }
                            $task = unjson($task);
                            if($task && $task['oid']){
                                $orderModel = Orders::create();
                                $info = $orderModel->get($task['oid']);
                                if($info['status'] == 2){
                                    break;
                                }else{
                                    //更新订单状态
                                    $info->update(['status' => 2, 'pay_at' => Carbon::now()]);
                                    //更新用户VIP信息
                                    $userModel = Users::create();
                                    $user = $userModel->get($info['user_id']);
                                    $vipAt = $this->getVipAt($info['vip'],$user['vip_at']);
                                    $user->update(['vip' => $info['vip'], 'vip_at' => $vipAt]);
                                    //结算队列
                                    $job = json([
                                        'oid' => $info['id']
                                    ]);
                                    $redis->rPush('queue:update-rebate',$job);
                                }
                            }else{
                                break;
                            }
                        }catch (\Throwable $e){
                            write_log('update-user:' . $e->getMessage());
                            break;
                        }
                    }
                    $this->isRun = false;
                });
            }
        });
    }

    protected function getVipAt($orderVip, $oldVip){
        $oldVip = $oldVip ?: time();
        switch ($orderVip) {
            case 'month_vip':
                $vipAt = (int)$oldVip + 30 * 86400;
                break;
            case 'quarter_vip':
                $vipAt = (int)$oldVip + 180 * 86400;
                break;
            case 'year_vip':
                $vipAt = (int)$oldVip + 365 * 86400;
                break;
            case 'forever_vip':
                $vipAt = (int)$oldVip + 3 * 365 * 86400;
                break;
            default:
                $vipAt = (int)$oldVip + 86400;
                break;
        }
        return $vipAt;
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
