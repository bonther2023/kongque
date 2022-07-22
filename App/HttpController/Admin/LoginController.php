<?php

namespace App\HttpController\Admin;

use ank\IpLookup;
use App\HttpController\BaseController;
use App\Model\Admins;
use App\Model\Fingerprints;
use App\Utility\Captcha;
use App\Utility\JwtToken;
use EasySwoole\Http\Message\Status;
use EasySwoole\Utility\Hash;

class LoginController extends BaseController
{

    public function login()
    {
        try {
            $data = $this->getParams();
            $user = Admins::create()->get(['username' => $data['username'], 'status' => Admins::STATUS_1]);
            if(!$user){
                return $this->writeJson(1, null, '管理员账户不存在或者被锁定，请联系超级管理员');
            }
            if(Hash::validatePasswordHash($data['password'], $user['password']) == false){
                return $this->writeJson(1, null, '密码输入错误');
            }
            if((new Captcha())->check($data['captcha'], $data['captchaKey']) == false){
                return $this->writeJson(1, null, '验证码输入错误');
            }
            $settings = settings();
            if($settings['login_limit']){
                //验证登录用户IP是否合法
                $ip = $this->getIp();
                $result = (new IpLookup())->getInfo($ip,0);
                if(!$result || $result['country'] !== '柬埔寨'){
                    return $this->writeJson(1, null, '非法登录');
                }
            }
            $jwtToken = new JwtToken();
            $token = $jwtToken->token($user['id']);
            $return = ['token' => $token, 'username' => $user['nickname']];
            return $this->writeJson(0,encrypt_data($return),'登录成功');
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }



    public function captcha()
    {
        try {
            $captcha = (new Captcha())->create();
            return $this->writeJson(0, encrypt_data($captcha));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }



}
