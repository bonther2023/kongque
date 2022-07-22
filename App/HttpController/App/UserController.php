<?php

namespace App\HttpController\App;

use App\Model\Users;
use App\Utility\JwtToken;

class UserController extends AuthController
{



    public function user()
    {
        try {
            if($this->userid){
                $userModel = Users::create();
                //用户信息
                $user = $userModel->field('id,username,mobile,vip,canal_id,vip_at')->get($this->userid);
                if($user['vip'] && $user['vip_at']){
                    $now = time();
                    if ($now > $user['vip_at']) {
                        $userModel->update(['vip' => '', 'vip_at' => 0], ['id' => $this->userid]);
                        $user['vip'] = '';
                    }
                }
                $user['vip_at'] = date('Y-m-d H:i',$user['vip_at']);
            }else{
                $user = [];
            }
            return $this->writeJson(0, encrypt_data($user));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }

    //绑定账户
    public function mobile()
    {
        try {
            $data = $this->getParams();
            $mobile = $data['mobile'] ?? '';
            if($mobile){
                $userModel = Users::create();
                $user = $userModel->where('mobile',$mobile)->get();
                if($user){
                    return $this->writeJson(1,null,'该手机号码已被绑定');
                }
                $userModel->update(['mobile' => $mobile],['id' => $this->userid]);
                return $this->writeJson(0);
            }
            return $this->writeJson(1,null,'请输入需要绑定的手机号码');
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }

    //找回账户
    public function account()
    {
        try {
            $data = $this->getParams();
            $mobile = $data['mobile'] ?? '';
            if($mobile){
                $userModel = Users::create();
                $info = $userModel->where('mobile',$mobile)->order('created_at','desc')->get();
                if($info){
                    $jwtToken = new JwtToken();
                    $token = $jwtToken->token($info['id']);
                    $return = [
                        'id' => $info['id'],
                        'canal_id' => $info['canal_id'],
                        'username' => $info['username'],
                        'uuid' => $info['uuid'],
                    ];
                    return $this->writeJson(0,encrypt_data(['user' => $return, 'token' => $token]));
                }
            }
            return $this->writeJson(1,null,'请输入需要找回的手机号码');
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }


    public function check(){
        try {
            $data = $this->getParams();
            $version = $data['version'] ?? '';
            if(empty($version)){
                return $this->writeJson(1);
            }
            $vs = explode('.',$version);
            if($vs && count($vs) == 3){
                $settings = settings();
                $link = $settings['update_page'];
                $_version = $settings['app_version'];
                if($_version != $version){
                    return $this->writeJson(0,encrypt_data($link));
                }
            }
            return $this->writeJson(1);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }


    public function custom(){
        try {
            $settings = settings();
            $link = $settings['kefu_link'];
            return $this->writeJson(0,encrypt_data($link));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }
}
