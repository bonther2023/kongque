<?php

namespace App\Crontab;

use App\Model\Canals;
use App\Model\Orders;
use App\Model\Trades;
use Carbon\Carbon;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;

class ClearOrder extends AbstractCronTask
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
        return 'ClearOrder';
    }

    function run(int $taskId, int $workerIndex)
    {
        // TODO: Implement run() method.
        // 定时任务处理逻辑：删除3天之前所有未支付的订单数据
        $task = TaskManager::getInstance();
        $task->async(function (){
            //删除3天之前所有未支付的订单数据
            Orders::create()->where('status',Orders::STATUS_1)
                ->where('created_at',Carbon::now()->subDays(3)->toDateString(),'<')
                ->destroy(null,true);
            //删除30天之前所有订单数据
            Orders::create()->where('created_at',Carbon::now()->subDays(30)->toDateString(),'<')
                ->destroy(null,true);
            //删除15天之前所有结算数据
            Trades::create()->where('created_at',Carbon::now()->subDays(15)->toDateString(),'<')
                ->destroy(null,true);
        });
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        write_log($throwable->getMessage());
    }
}
