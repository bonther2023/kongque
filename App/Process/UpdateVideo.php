<?php

namespace App\Process;

use App\Model\VideoBilis;
use App\Model\VideoDefaults;
use App\Model\VideoShayus;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class UpdateVideo extends AbstractProcess{
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
                            $task = $redis->lPop('queue:update-video');
                            if (!$task) {
                                break;
                            }
                            $task = unjson($task);
                            if($task && $task['id'] && $task['resource']){
                                switch ($task['resource']){
                                    case 'bili':
                                        $model = VideoBilis::create();
                                        break;
                                    case 'shayu':
                                        write_log('update-video:' . $task['resource']);
                                        $model = VideoShayus::create();
                                        break;
                                    default:
                                        $model = VideoDefaults::create();
                                        break;
                                }
                                $model->update(['view' => QueryBuilder::inc()],['id' => $task['id']]);
                            }else{
                                break;
                            }
                        }catch (\Throwable $e){
                            write_log('update-video:' . $e->getMessage());
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
