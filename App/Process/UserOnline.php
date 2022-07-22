<?php

namespace App\Process;

use ank\IpLookup;
use App\Model\Configs;
use App\Model\Users;
use Carbon\Carbon;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\RedisPool\RedisPool;

class UserOnline extends AbstractProcess{
    private $isRun = false;
    public function run($arg){
        //每10秒执行一次清理
        $this->addTick(10000,function (){
            if(!$this->isRun){
                $this->isRun = true;
                go(function (){
                    try{
                        $redis = RedisPool::defer('redis');
                        $setting = $redis->get('setting');
                        if (!$setting) {
                            $setting = Configs::create()->getList();
                            $redis->set('setting', $setting);
                        }
                        $time = time() - (int)($setting['online_time']*60);
                        $redis->zRemRangeByScore('online',0,(int)$time);
                    }catch (\Throwable $e){
                        write_log('user-online:' . $e->getMessage());
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
