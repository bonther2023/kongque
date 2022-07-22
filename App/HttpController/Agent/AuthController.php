<?php

namespace App\HttpController\Agent;

use App\HttpController\BaseController;
use App\Model\Agents;
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
        //验证登录用户的数据正确性
        $agent = Agents::create()->get($auth);
        if(!$agent){
            $this->response()->withStatus(Status::CODE_UNAUTHORIZED);
            return false;
        }
        $this->userid = $auth;
        return true;
    }



}
