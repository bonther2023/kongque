<?php

namespace App\Crontab;

use App\Model\Canals;
use App\Model\Orders;
use App\Model\Users;
use Carbon\Carbon;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;

class ClearUser extends AbstractCronTask
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
        return 'ClearUser';
    }

    function run(int $taskId, int $workerIndex)
    {
        // TODO: Implement run() method.
        // 定时任务处理逻辑：删除10天之前所有没有VIP和免费VIP的订单数据
        $task = TaskManager::getInstance();
        $task->async(function (){
            Users::create()->where("(vip = '' or vip = 'free_vip')")
                ->where('created_at',Carbon::now()->subDays(10)->toDateString(),'<')
                ->destroy(null,true);
        });
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        write_log($throwable->getMessage());
    }
}
