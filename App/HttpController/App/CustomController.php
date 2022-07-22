<?php

namespace App\HttpController\App;

use App\Model\Customs;
use App\Model\Users;
use Carbon\Carbon;

class CustomController extends AuthController
{


    public function index(){
        $params = $this->getParams();
        $user_id = (int)$params['uid'] ?: 0;
        $cModel = Customs::create();
        $userInfo = $cModel->where('user_id', $user_id)->get();
        $userModel = Users::create();
        $user = $userModel->field('id,username,mobile,ip,ip_address,vip,vip_at,app_system,app_model,app_network,app_vendor,app_release,created_at')->get($user_id);
        if($userInfo){
            $update = [
                'platform' => 'micao',
                'username' => $user['username'],
                'user_id' => $user['id'],
                'mobile' => $user['mobile'],
                'ip' => $user['ip'],
                'ip_address' => $user['ip_address'],
                'vip' => $user['vip'],
                'vip_at' => date('Y-m-d H:i:s',$user['vip_at']),
                'app_system' => $user['app_system'],
                'app_model' => $user['app_model'],
                'app_network' => $user['app_network'],
                'app_vendor' => $user['app_vendor'],
                'app_release' => $user['app_release'],
                'reg_at' => $user['created_at'],
                'created_at' => Carbon::now(),
            ];
            $userInfo->update($update);
        }else{
            $userInfo = [
                'platform' => 'micao',
                'username' => $user['username'],
                'user_id' => $user['id'],
                'mobile' => $user['mobile'],
                'ip' => $user['ip'],
                'ip_address' => $user['ip_address'],
                'vip' => $user['vip'],
                'vip_at' => date('Y-m-d H:i:s',$user['vip_at']),
                'app_system' => $user['app_system'],
                'app_model' => $user['app_model'],
                'app_network' => $user['app_network'],
                'app_vendor' => $user['app_vendor'],
                'app_release' => $user['app_release'],
                'reg_at' => $user['created_at'],
                'created_at' => Carbon::now(),
            ];
            $cModel->data($userInfo,false)->save();
        }
        $settings = settings();
        $websock = $settings['socket_link'];
        $return = [
            'user' => $userInfo,
            'websock' => $websock
        ];
        return $this->writeJson(0, encrypt_data($return));
    }



}
