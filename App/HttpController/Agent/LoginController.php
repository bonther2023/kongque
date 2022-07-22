<?php

namespace App\HttpController\Agent;

use App\HttpController\BaseController;
use App\Model\Agents;
use App\Model\Canals;
use App\Utility\Captcha;
use App\Utility\JwtToken;
use EasySwoole\Http\Message\Status;
use EasySwoole\Utility\Hash;

class LoginController extends BaseController
{

    public function authorize(){
        try {
            $data = $this->getParams();
            if(!isset($data['aid']) || !$data['aid']){
                return $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            }
            $model = Agents::create();
            $user = $model->get(['status' => Agents::STATUS_1, 'id' => $data['aid']]);
            if(!$user){
                return $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
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

    public function login()
    {
        try {
            $data = $this->getParams();
            $user = Agents::create()->get(['username' => $data['username'], 'status' => Agents::STATUS_1]);
            if(!$user){
                return $this->writeJson(1, null, '管理员账户不存在或者被锁定，请联系超级管理员');
            }
            if(Hash::validatePasswordHash($data['password'], $user['password']) == false){
                return $this->writeJson(1, null, '密码输入错误');
            }
            if((new Captcha())->check($data['captcha'], $data['captchaKey']) == false){
                return $this->writeJson(1, null, '验证码输入错误');
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
