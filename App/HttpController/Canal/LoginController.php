<?php

namespace App\HttpController\Canal;

use App\HttpController\BaseController;
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
            if(!isset($data['cid']) || !isset($data['aid']) || !$data['cid'] || !$data['aid']){
                return $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            }
            $model = Canals::create();
            $user = $model->get(['agent_id' => $data['aid'], 'status' => Canals::STATUS_1, 'id' => $data['cid']]);
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
            if(!$data['username']){
                return $this->writeJson(1, null, '请输入渠道账户');
            }
            $user = Canals::create()->get(['username' => $data['username'], 'status' => Canals::STATUS_1]);
            if(!$user){
                return $this->writeJson(1, null, '账户不存在或者被锁定，请联系超级管理员');
            }
            if(Hash::validatePasswordHash($data['password'], $user['password']) == false){
                return $this->writeJson(1, null, '密码输入错误');
            }
            if((new Captcha())->check($data['captcha'], $data['captchaKey']) == false){
                return $this->writeJson(1, null, '验证码输入错误');
            }
            $user->update(['login_ip' => $this->getIp()]);
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

    public function xvhr(){
        try {
            $params =  base64_encode('/ging/axb/sing/xvhr.html?qdid=2090');
            $setting = settings();
            $target = trim($setting['web_link'],'/');
            $domain = $target."/api.php?auth=".$params;
            return $this->writeJson(0, $domain);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
