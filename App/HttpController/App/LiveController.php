<?php

namespace App\HttpController\App;

use App\HttpController\BaseController;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\RedisPool\RedisPool;


class LiveController extends AuthController
{


    public function platform()
    {
        try {
            //http://api.sk4t.com/mf/json.txt
            $cache = RedisPool::defer('redis');
            $key = 'live:platform';
            $platform = $cache->get($key);
            if($platform){
                $platform['buy'] = $this->userLiveStatus();
                return $this->writeJson(0,encrypt_data($platform));
            }
            $request = new HttpClient('http://api.sk4t.com/mf/json.txt');
            $response = $request->get();
            $body = $response->getBody();
            $result = unjson($body);
            $result['buy'] = $this->userLiveStatus();
            $cache->set($key,$result,120);
            return $this->writeJson(0,encrypt_data($result));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }

    public function anchor()
    {
        try {
            //http://api.sk4t.com/mf/77zhibo.txt
            $name = $this->request()->getRequestParam('name');
            if(!$name || strpos($name, '.txt') === false){
                return $this->writeJson(1,$name,'参数错误');
            }
            $cache = RedisPool::defer('redis');
            $key = 'live:anchor:'.$name;
            $anchor = $cache->get($key);
            if($anchor){
                return $this->writeJson(0,encrypt_data($anchor));
            }
            $request = new HttpClient('http://api.sk4t.com/mf/'.$name);
            $response = $request->get();
            $body = $response->getBody();
            $result = unjson($body);
            $cache->set($key,$result['zhubo'],120);
            return $this->writeJson(0,encrypt_data($result['zhubo']));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }

    public function test(){
        $request = new HttpClient('http://api.sk4t.com/mf/json.txt');
        $response = $request->get();
        $body = $response->getBody();
        $result = unjson($body);
        return $this->writeJson(0,$result);
    }



}
