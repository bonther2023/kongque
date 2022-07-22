<?php

namespace App\Process;

use ank\IpLookup;
use App\Model\Users;
use Carbon\Carbon;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\RedisPool\RedisPool;

class UserLogin extends AbstractProcess{
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
                            $task = $redis->lPop('queue:user-login');
                            if (!$task) {
                                break;
                            }
                            $task = unjson($task);
                            if($task && $task['uid']){
                                $result = (new IpLookup())->getInfo($task['ip'],0);
                                $address = '';
                                if($result && $result['country']){
                                    $address = $result['country'].'/'.$result['province'].'/'.$result['city'].'/'.$result['isp'];
                                }
                                //更新登录信息
                                Users::create()->update([
                                    'ip_address' => $address,
                                    'ip' => $task['ip'],
                                    'app_release'=> $task['version'],
                                    'app_version'=> $task['app_version'],
                                    'app_vendor' => $task['vendor'],
                                    'app_model'  => $task['model'],
                                    'app_network'=> $task['network'],
                                    'app_system' => $task['name'],
                                    'login_at' => Carbon::createFromTimestamp($task['time'])->toDateTimeString()
                                ],['id' => $task['uid']]);
                            }else{
                                break;
                            }
                        }catch (\Throwable $e){
                            write_log('update-login:' . $e->getMessage());
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
