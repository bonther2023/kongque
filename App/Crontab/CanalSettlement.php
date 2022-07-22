<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-11-6
 * Time: 下午3:30
 */

namespace App\Crontab;

use App\Model\Canals;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;

class CanalSettlement extends AbstractCronTask
{

    public static function getRule(): string
    {
        // TODO: Implement getRule() method.
//        return '*/1 * * * *';//每分钟执行一次
        return '0 0 * * *';//每天凌晨12点执行一次
    }

    public static function getTaskName(): string
    {
        // TODO: Implement getTaskName() method.
        // 定时任务名称
        return 'CanalSettlement';
    }

    function run(int $taskId, int $workerIndex)
    {
        // TODO: Implement run() method.
        // 定时任务处理逻辑：删除3天之前所有未支付的订单数据
        $task = TaskManager::getInstance();
        $setting = settings();
        //结算代理
        $agentLists = Canals::create()->field('balance,id,username,name,bank,card')
            ->where('status',Canals::STATUS_1)
            ->where('name','', '<>')
            ->where('bank','', '<>')
            ->where('card','', '<>')
            ->where('balance',$setting['trade_money'],'>=')
            ->all();
        foreach ($agentLists as $key => $agentItem){
            if($agentItem['id'] != 2000){
                $job = [];
                $job['balance'] = $agentItem['balance'];
                $job['id'] = $agentItem['id'];
                $job['username'] = $agentItem['username'];
                $job['name'] = $agentItem['name'];
                $job['bank'] = $agentItem['bank'];
                $job['card'] = $agentItem['card'];
                $job['type'] = 'canal';
                $task->async(function () use($job){
                    settlement($job);
                });
            }
        }

    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        write_log($throwable->getMessage());
    }
}
