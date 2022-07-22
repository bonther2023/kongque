<?php

namespace App\HttpController\App;

use App\HttpController\BaseController;
use App\Model\Orders;
use App\Utility\Pay;
use App\Utility\RedisClient;
use EasySwoole\RedisPool\RedisPool;

class NotifyController extends BaseController
{
    protected function parseData($data){
        $list = explode("\r\n", $data);
        foreach($list as $value){
            if($value){
                if(strstr($value, '--')) continue;
                if(strpos($value, '-')){
                    $key = str_replace('"', '', strchr($value, '"'));
                    continue;
                };
                if($value){
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }

    protected function testReturn($params,$name){
        //获取支付配置信息
        $pay = new Pay();
        $config = $pay->getPayInfo($name);
        $check = $pay->checkSign($params,$name,$config['pay_secret']);
        if(!$check) return $this->writeEcho('fail,check sign fail');
        return $this->writeEcho($config['return_msg']);
    }

    public function yinyue(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'yinyue';
        return $this->notify($params,$name);
    }

    public function taimei(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'taimei';
        return $this->notify($params,$name);
    }

    public function hengyuan(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'hengyuan';
        return $this->notify($params,$name);
    }

    public function xizi(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'xizi';
        return $this->notify($params,$name);
    }

    public function baoying(){
        $request = $this->request();
        $params = $request->getBody()->__toString();
        $params = unjson($params);
        //获取支付平台标识
        $name = 'baoying';
        return $this->notify($params,$name);
    }



    public function weipay(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'weipay';
        return $this->notify($params,$name);
    }

    public function lizhi(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'lizhi';
        return $this->notify($params,$name);
    }

    public function yangyu(){
        $request = $this->request();
        $params = $request->getBody()->__toString();
        $params = unjson($params);
        //获取支付平台标识
        $name = 'yangyu';
        return $this->notify($params,$name);
    }


    public function xiaoding(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'xiaoding';
        return $this->notify($params,$name);
    }


    public function zhongbao(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'zhongbao';
        return $this->notify($params,$name);
    }


    public function xiaoyi(){
        $request = $this->request();
        $params = $request->getBody()->__toString();
        $params = unjson($params);
        //获取支付平台标识
        $name = 'xiaoyi';
        return $this->notify($params,$name);
    }


    public function rongyi(){
        $request = $this->request();
        $params = $request->getRequestParam();
        write_log($params);
        //获取支付平台标识
        $name = 'rongyi';
        return $this->notify($params,$name);
    }

    public function rongyicode(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'rongyicode';
        return $this->notify($params,$name);
    }

    public function cang(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'cang';
        return $this->notify($params,$name);
    }


    public function ergou(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'ergou';
        return $this->notify($params,$name);
    }

    public function panshi(){
        $request = $this->request();
        $params = $request->getBody()->__toString();
        $params = json_decode($params, true);
        //获取支付平台标识
        $name = 'panshi';
        return $this->notify($params,$name);
    }


    public function xunbo(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'xunbo';
        return $this->notify($params,$name);
    }

    public function notifyXunbop(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'xunbop';
        return $this->notify($params,$name);
    }

    public function kuanzhai(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'kuanzhai';
        return $this->notify($params,$name);
    }

    public function fuxib(){
        $request = $this->request();
        $params = $request->getBody();
        $params = $this->parseData($params);
        //获取支付平台标识
        $name = 'fuxib';
        return $this->notify($params,$name);
    }

    public function fuxi(){
        $request = $this->request();
        $params = $request->getBody();
        $params = $this->parseData($params);
        //获取支付平台标识
        $name = 'fuxi';
        return $this->notify($params,$name);
    }

    public function fuxiy(){
        $request = $this->request();
        $params = $request->getBody();
        $params = $this->parseData($params);
        //获取支付平台标识
        $name = 'fuxiy';
        return $this->notify($params,$name);
    }


    public function qinaguia(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'qinaguia';
        return $this->notify($params,$name);
    }

    public function qinaguicode(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'qinaguicode';
        return $this->notify($params,$name);
    }

    public function xiaoxuan(){
        $request = $this->request();
        $params = $request->getBody();
        $params = $this->parseData($params);
        write_log($params['return_msg']);
        //获取支付平台标识
        $name = 'xiaoxuan';
//        return $this->notify($params,$name);
    }

    public function bossyun(){
        $request = $this->request();
        $params = $request->getBody()->__toString();
        $params = unjson($params);
        write_log($params);
        //获取支付平台标识
        $name = 'bossyun';
        return $this->notify($params,$name);
    }

    public function xiyuan(){
        $request = $this->request();
        $params = $request->getRequestParam();
        //获取支付平台标识
        $name = 'xiyuan';
        return $this->notify($params,$name);
    }



    protected function notify($params,$name){
        write_log($name);
        write_log($params);
        if(empty($params)){
            return $this->writeEcho('fail,params is empty');
        }
        //获取支付配置信息
        $pay = new Pay();

        $config = $pay->getPayInfo($name);

        //订单信息
        $orderFiled = $config['return_order'];

        $orderModel = Orders::create();
        $info = $orderModel->where('order_id', $params[$orderFiled])->order('created_at','desc')->get();
        if(!$info) return $this->writeEcho('fail,no order info');

        //如果订单状态为已支付状态，而支付平台未返回，则特殊处理一下
        if($info['status'] == Orders::STATUS_2){
            return $this->writeEcho($config['return_msg']);
        }

        //根据标识获取相应的支付订单回调状态
        if($config['return_ok_field'] && ($params[$config['return_ok_field']] != $config['return_ok_msg'])){
            return $this->writeEcho('fail,order status is failed');
        }

        //验签
        $check = $pay->checkSign($params,$name,$config['pay_secret']);
        if(!$check) return $this->writeEcho('fail,check sign fail');
        //更新订单等结算信息,必须这样放在异步去执行，只能开一个进程，部分支付同一秒可以返回很多回调的请求
        $redis = RedisPool::defer('redis');
        $job = json([
            'oid' => $info['id']
        ]);
        $redis->rPush('queue:update-user',$job);

        //异步通知成功
        return $this->writeEcho($config['return_msg']);

    }


    public function page(){
        $request = $this->request();
        $orderid = $request->getRequestParam('orderid') ?: '';
        $cache = RedisPool::defer('redis');
        $key = 'orderPages:id-'.$orderid;
        $form = $cache->get($key);
        if(empty($form)){
            return $this->response()->write('订单数据不存在');
        }
        return $this->response()->write($form);
    }

}
