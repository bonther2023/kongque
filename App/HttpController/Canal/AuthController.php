<?php

namespace App\HttpController\Canal;

use App\HttpController\BaseController;
use App\Model\Canals;
use App\Utility\JwtToken;
use EasySwoole\Http\Message\Status;

class AuthController extends BaseController
{

    protected $userid;

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
        if($auth == 2027 || $auth == 2029){
            write_log('2027IP:'.$this->getIp());
            $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            return false;
        }
        $canal = Canals::create()->get($auth);
        if(!$canal){
            $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            return false;
        }
        $this->userid = $auth;
        return true;
    }



}
