<?php

namespace App\HttpController\App;

use App\Model\Canals;
use App\Model\Users;
use App\Utility\JwtToken;
use Carbon\Carbon;
use EasySwoole\RedisPool\RedisPool;

class LoginController extends AuthController
{

    public function login(){

        try {
            $data = $this->getParams();
            $uuid     = $data['uuid'] ?? '';
            $canal_id = $data['canal_id'] ?? 3000;
            $username = $data['username'] ?? '';
            $name     = $data['name'] ?? '';
            $app_version  = $data['version'] ?? '';
            $version  = $data['vs'] ?? '';
            $model    = $data['model'] ?? '';
            $network  = $data['network'] ?? '';
            $vendor   = $data['vendor'] ?? '';
            $umodel = Users::create();
            if(!is_numeric($canal_id)){
                $canal_id = 3000;
            }
            $user  = $umodel->where('uuid', $data['uuid'])->get();
            $ip = $this->getIp();
            $redis = RedisPool::defer('redis');
            if($user){
                //登录
                if($user['vip'] && $user['vip_at']){
                    $now = time();
                    if ($now > $user['vip_at']) {
                        $user->update(['vip' => '', 'vip_at' => 0]);
                        $user['vip'] = '';
                        $user['vip_at'] = 0;
                    }
                }
                $job = json([
                    'uid'      => $user['id'],
                    'version'  => $version,
                    'model'    => $model,
                    'vendor'   => $vendor,
                    'app_version'=> $app_version,
                    'ip'       => $ip,
                    'network'  => $network,
                    'name'     => $name,
                    'time'     => time()
                ]);
                $redis->rPush('queue:user-login',$job);
                $token  = $this->token($user['id']);
                $return = $this->user($user);
            }else{
                //注册
                $canalModel = Canals::create();
                $canal = $canalModel->field('id,agent_id,register_rebate,order_num')->where('id',$canal_id)->get();
                if (empty($canal)) {
                    $agent_id = 3000;
                    $canal_id = 3000;
                    $rebate = 0;
                } else {
                    $agent_id = $canal['agent_id'];
                    $rebate  = $canal['register_rebate'];
                }
//                $go = $this->canalBack($canal_id,$agent_id,$rebate,$canal['order_num']);
//                $agent_id = $go['agent_id'];
//                $canal_id = $go['canal_id'];
//                $rebate = $go['register_rebate'];
                $setting = settings();
                $insert  = [
                    'agent_id'   => $agent_id,
                    'uuid'       => $uuid,
                    'canal_id'   => $canal_id,
                    'username'   => $username,
                    'vip'        => 'free_vip',
                    'vip_at'     => time() + $setting['free_vip_time'] * 60,
                    'ip'         => $ip,
                    'login_at'   => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'app_release'=> $version,
                    'app_version'=> $app_version,
                    'app_vendor' => $vendor,
                    'app_model'  => $model,
                    'app_network'=> $network,
                    'app_system' => $name,
                ];
                $userId = $umodel->data($insert,false)->save();
                $insert['id'] = $userId;
                $job = json([
                    'aid'   => $agent_id,
                    'cid'   => $canal_id,
                    'uuid'  => $uuid,
                    'uid'   => $userId,
                    'rebate'=> $rebate,
                    'ip'    => $ip,
                    'time'  => time(),
                ]);
                $redis->rPush('queue:user-register',$job);
                $token = $this->token($userId);
                $return = $this->user($insert);
            }
            $back = ['uuid' => $uuid, 'user' => $return, 'token' => $token];
            return $this->writeJson(0, encrypt_data($back));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    protected function canalBack($cid,$aid,$rebate,$order){
        $agent_id = $aid;
        $canal_id = $cid;
        $register_rebate = $rebate;
//        if($order > 1){
//            if($cid == 3000){
//                $a = rand(1,5);
//                $month = date('m');
//                if($a == 1 || $a == 2 || $a == 3  || $a == 4){
//                    $agent_id = 3001;
//                    $canal_id = 3001;
//                    $register_rebate = 60;
//                }
//            }else{
//                $b = rand(1,100);
//                if($b == 1 || $b == 2){
//                    $agent_id = 3001;
//                    $canal_id = 3001;
//                    $register_rebate = 60;
//                }
//            }
//        }
        return ['agent_id' => $agent_id, 'canal_id' => $canal_id, 'register_rebate' => $register_rebate];
    }



    protected function token($uid){
        $jwtToken = new JwtToken();
        return $jwtToken->token($uid);
    }

    protected function user($user){
        return [
            'id'      => $user['id'],
            'username'=> $user['username'],
            'vip'     => $user['vip'],
            'vip_at'  => $user['vip_at'] ? date('Y-m-d H:i',$user['vip_at']) : '',
        ];
    }
}
