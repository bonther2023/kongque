<?php

namespace App\HttpController\Admin;


use ank\IpLookup;
use App\HttpController\BaseController;
use App\Model\Admins;
use App\Model\Configs;
use App\Utility\JwtToken;
use EasySwoole\Http\Message\Status;

class AuthController extends BaseController
{

    public function onRequest(?string $action): ?bool
    {
        //判断登录
        $header = $this->request()->getHeaders();
        if (!isset($header['authorization'])) {
            $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            return false;
        }
        list ($bearer, $token) = explode(' ', $header['authorization'][0]);
        if (!$token) {
            $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            return false;
        }
        $auth = (new JwtToken())->check($token);
        if ($auth === false) {
            $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            return false;
        }
        //验证登录用户的数据正确性
        $admin = Admins::create()->get($auth);
        if(!$admin){
            $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            return false;
        }
        $limit = Configs::create()->get(['config_key' => 'login_limit']);
        if($limit['config_value']){
            //验证登录用户IP是否合法
            $ip = $this->getIp();
            $result = (new IpLookup())->getInfo($ip,0);
            if(!$result || $result['country'] !== '柬埔寨'){
                $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
                return false;
            }
        }
        return true;
    }

//    protected function getOnline(){
//        $redis = (new RedisClient())->connection(16);
//        $time = time() + 100;
//        $model = new Configs();
//        $config = $model->where('config_key' , 'online_time')->first();
//        $online_time = intval($config['config_value']) ? intval($config['config_value']) : 10;
//        $min = time() - $online_time * 60;
//        return $redis->zCount('online',$min, (int)$time);
//    }



}
