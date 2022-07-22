<?php

namespace App\HttpController\App;

use App\HttpController\BaseController;
use App\Model\Users;
use App\Utility\JwtToken;
use EasySwoole\RedisPool\RedisPool;

class AuthController extends BaseController
{

    protected $userid = 0;

    public function onRequest(?string $action): ?bool
    {
        //判断登录
        $header = $this->request()->getHeaders();
        if (isset($header['authorization']) && $header['authorization']) {
            list ($bearer, $token) = explode(' ', $header['authorization'][0]);
            if($token){
                $auth = (new JwtToken())->check($token);
                if ($auth !== false) {
                    $this->userid = $auth;
                    $this->online($this->userid);
                }
            }
        }
        return true;
    }


    protected function userVipStatus(){
        $buy = 0;
        if($this->userid){
            $userModel = Users::create();
            //查看用户VIP信息
            $user = $userModel->field('vip,vip_at')->get($this->userid);
            if($user['vip'] && $user['vip_at']){
                $now = time();
                if ($now > $user['vip_at']) {
                    $userModel->update(['vip' => '', 'vip_at' => 0], ['id' => $this->userid]);
                    $user['vip'] = '';
                }
                if($user['vip'] && ($user['vip'] != 'free_vip') && ($user['vip'] !=  'day_vip')){
                    $buy = 1;
                }
            }
        }
        return $buy;
    }

    protected function userLiveStatus(){
        $buy = 0;
        if($this->userid){
            $userModel = Users::create();
            //查看用户VIP信息
            $user = $userModel->field('vip,vip_at')->get($this->userid);
            if($user['vip'] && $user['vip_at']){
                $now = time();
                if ($now > $user['vip_at']) {
                    $userModel->update(['vip' => '', 'vip_at' => 0], ['id' => $this->userid]);
                    $user['vip'] = '';
                }
                if($user['vip'] && ($user['vip'] == 'forever_vip') ){
                    $buy = 1;
                }
            }
        }
        return $buy;
    }


    //统计在线人数
    protected function online($uid){
        $redis = RedisPool::defer('redis');
        $time = (int)time();
        $redis->zAdd('online',$time,(string)$uid);
    }


}
