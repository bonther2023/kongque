<?php

namespace App\Utility;

use App\Model\PayErrors;
use App\Model\Pays;
use Carbon\Carbon;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Spl\SplString;

class Pay
{

    public function getOrderInfo($order,$func,$config)
    {
        switch ($func){
            case 'xiaoxuan':
                $orderInfo = [
                    'orderid' => $order['orderid'],//订单号
                    'price' => (int)$order['amount'] * 100,//订单金额
                    'istype'  => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'],//支付编码
                    'return_url' => $order['return'],//同步回调
                    'notify_url' => 'http://xuan.kohing.com/app/notify/xiaoxuan',//异步回调
                ];
                $orderInfo['uid']  = $config['pay_number'];//商户号
                $orderInfo['goodsname']= $order['subject'];//订单标题
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['ip'] = $order['ip']; //ip
                break;
            case 'xiaoding'://小鼎支付
                $orderInfo = [
                    'mchOrderNo'   => $order['orderid'],//订单号
                    'amount' => $order['amount']*100,//订单金额
                    'productId' =>  $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'notifyUrl'=> $order['notify'], //异步回调
                    'returnUrl'=> $order['return'], //同步回调
                ];
                $orderInfo['appId']= '894d2aaa46d94ef8b75e3fe5cd3fe41b';
                $orderInfo['currency']= 'cny';
                $orderInfo['mchId']  = $config['pay_number'];//商户号
                $orderInfo['subject']  = $order['subject'];
                $orderInfo['body']  = $order['subject'];
                $orderInfo['extra']  = $order['subject'];
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'weipay'://小微话费
                $orderInfo = [
                    'sdorderno' => $order['orderid'],//订单号
                    'total_fee' => number_format($order['amount'],2),//订单金额
                    'paytype'  => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'],//支付编码
                    'returnurl' => $order['return'],//同步回调
                    'notifyurl' => $order['notify'],//异步回调
                    'version' => '1.0',
                ];
                $orderInfo['customerid']  = $config['pay_number'];//商户号
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['paymentform'] = 2;
                break;
            case 'taimei'://台妹支付
                $orderInfo = [
                    'pay_orderid'    => $order['orderid'],//订单号
                    'pay_amount'     => $order['amount'],//订单金额
                    'pay_applydate'  => date('Y-m-d H:i:s'),//订单时间
                    'pay_bankcode'   => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'],//支付编码
                    'pay_notifyurl'  => $order['notify'],//异步回调
                    'pay_callbackurl'=> $order['return'],//同步回调
                ];
                $orderInfo['pay_memberid']  = $config['pay_number'];//商户号
                $orderInfo['pay_md5sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['pay_productname']= $order['subject'];//订单标题
                break;
            case 'yinyue'://音乐支付
            case 'lizhi'://荔枝支付
                $orderInfo = [
                    'out_trade_no'    => $order['orderid'],//订单号
                    'total_fee'     => $order['amount'],//订单金额
                    'order_type'   => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'],//支付编码
                    'notify_url'  => $order['notify'],//异步回调
                    'return_url'=> $order['return'],//同步回调
                ];
                $orderInfo['mch_id']  = $config['pay_number'];//商户号
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['body']= $order['subject'];//订单标题
                break;
            case 'rongyicode'://融易扫码
                $orderInfo = [
                    'pay_orderid'    => $order['orderid'],//订单号
                    'pay_amount'     => $order['amount'],//订单金额
                    'pay_applydate'  => date('Y-m-d H:i:s'),//订单时间
                    'pay_bankcode'   => '937',//支付编码
                    'pay_notifyurl'  => $order['notify'],//异步回调
                    'pay_callbackurl'=> $order['return'],//同步回调
                ];
                $orderInfo['pay_memberid']  = $config['pay_number'];//商户号
                $orderInfo['pay_md5sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['pay_productname']= $order['subject'];//订单标题
                break;
            case 'rongyi'://融易支付
                $orderInfo = [
                    'order_sn'    => $order['orderid'],//订单号
                    'money'     => $order['amount'],//订单金额
                    'goods_desc'  => $order['subject'],//订单标题
                    'pay_code'   => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'],//支付编码
                    'notify_url'  => urlencode($order['notify']),//异步回调
                    'return_url'=> urlencode($order['return']),//同步回调
                    'time'    => time(),
                ];
                $orderInfo['mch_id']  = $config['pay_number'];//商户号
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'cang'://小苍支付
            case 'ergou'://二狗支付
                $orderInfo = [
                    'pay_orderid'    => $order['orderid'],//订单号
                    'pay_amount'     => $order['amount'],//订单金额
                    'pay_applydate'  => date('Y-m-d H:i:s'),//订单时间
                    'pay_bankcode'   => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'],//支付编码
                    'pay_notifyurl'  => $order['notify'],//异步回调
                    'pay_callbackurl'=> $order['return'],//同步回调
                ];
                $orderInfo['pay_memberid']  = $config['pay_number'];//商户号
                $orderInfo['pay_md5sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['pay_productname']= $order['subject'];//订单标题
                break;
            case 'panshi'://磐石支付
                $orderInfo = [
                    'merNo'          => $config['pay_number'], //商户号
                    'requestNo'      => $order['orderid'], //商家订单号
                    'signType'       => 'MD5',
                    'transId'        => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'goodsName'      => $order['subject'],
                    'amount'         => number_format($order['amount'], 2), //订单金额
                    'notifyUrl'      => $order['notify'], //异步回调
                    'timeStamp'      => date('Y-m-d H:i:s'),
                    'appUserId'      => (string) rand(111111, 999999),
                    'passbackParams' => (string) rand(111111, 999999),
                ];
                $orderInfo['sign'] = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'xunbo':
            case 'xunbop':
            case 'kuanzhai':
                $orderInfo = [
                    'pay_orderid'    => $order['orderid'],//订单号
                    'pay_amount'     => $order['amount'],//订单金额
                    'pay_applydate'  => date('Y-m-d H:i:s'),//订单时间
                    'pay_bankcode'   => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'],//支付编码
                    'pay_notifyurl'  => $order['notify'],//异步回调
                    'pay_callbackurl'=> $order['return'],//同步回调
                ];
                $orderInfo['pay_memberid']  = $config['pay_number'];//商户号
                $orderInfo['pay_md5sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['pay_productname']= $order['subject'];//订单标题
                $orderInfo['type']= $order['payment'] == 1 ? 'wx' : 'alipay'; //支付方式
                break;
            case 'fuxiy': //伏羲原生
            case 'fuxi': //伏羲支付
            case 'fuxib':
                $orderInfo = [
                    'fxddh'    => $order['orderid'], //订单号
                    'fxfee'     => number_format($order['amount'], 2), //订单金额
                    'fxpay'    => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'fxnotifyurl'  => $order['notify'], //异步回调
                    'fxbackurl'  => $order['return'], //同步回调
                ];
                $orderInfo['fxid'] = $config['pay_number']; //商户号
                $orderInfo['fxdesc']= $order['subject'];//订单标题
                $orderInfo['fxip'] = $order['ip']; //ip
                $orderInfo['fxsign']     = $this->sign($orderInfo, $config['pay_secret'], $func); //签名
                break;
            case 'qinaguicode'://钱柜扫码
            case 'qinaguia'://钱柜支付宝
                $orderInfo = [
                    'ordersn'   => $order['orderid'],//订单号
                    'total_fee' => $order['amount'],//订单金额
                    'mode_type' => 112,//支付模式
                    'notify_url'=> $order['notify'], //异步回调
                    'return_url'=> $order['return'], //同步回调
                ];
                $orderInfo['client_ip']= $order['ip'];//订单标题
                $orderInfo['mer_no']  = $config['pay_number'];//商户号
                $orderInfo['subject']  = $order['subject'];
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'xizi'://戏子支付
                $orderInfo = [
                    'order_sn'   => $order['orderid'],//订单号
                    'money' => $order['amount'],//订单金额
                    'pay_code' =>  $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'notify_url'=> $order['notify'], //异步回调
                    'return_url'=> $order['return'], //同步回调
                ];
                $orderInfo['mch_id']  = $config['pay_number'];//商户号
                $orderInfo['time']  = time();
                $orderInfo['goods_desc']  = $order['subject'];
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'baoying'://保盈支付
                $orderInfo = [
                    'orderid'   => $order['orderid'],//订单号
                    'amount'  => $order['amount'] * 100,//订单金额
                    'paytype' => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'synurl'  => $order['notify'], //异步回调
                    'return_url'=> $order['return'], //同步回调
                ];
                $orderInfo['ip']= $order['ip'];
                $orderInfo['cpid']  = $config['pay_number'];
                $orderInfo['product']  = $order['subject'];
                $orderInfo['describe']  = $order['subject'];
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'hengyuan'://恒远支付
                $orderInfo = [
                    'customer_order_no'   => $order['orderid'],//订单号
                    'money'  => number_format($order['amount'], 2),//订单金额
                    'pay_type' => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'notify_url'  => $order['notify'], //异步回调
                    'return_url'=> $order['return'], //同步回调
                ];
                $orderInfo['customer_id']  = $config['pay_number'];
                $orderInfo['version']  = 1;
                $orderInfo['return_type']  = 1;
                $orderInfo['remark']  = $order['subject'];
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'zhongbao'://中宝支付
                $orderInfo = [
                    'out_trade_no' => $order['orderid'],//订单号
                    'amount' => $order['amount'],//订单金额
                    'success_url' => $order['return'],//同步回调
                    'error_url' => $order['return'],//同步回调
                    'callback_url' => $order['notify'],//异步回调
                ];
                $orderInfo['appid']  = $config['pay_number'];//商户号
                $orderInfo['out_uid']  = (string)time();
                $orderInfo['version']  = 'v1.1';
                $orderInfo['return_type']  = 'app';
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'yangyu': //洋芋支付
                $orderInfo = [
                    'cpid'     => $config['pay_number'], //商户号
                    'amount'   => $order['amount'] * 100, //金额
                    'product'  => $order['subject'],
                    'orderid'  => $order['orderid'], //商家订单号
                    'describe' => $order['subject'], //商品名称
                    'synurl'   => $order['notify'], //异步回调
                ];
                $orderInfo['sign']    = $this->sign($orderInfo, $config['pay_secret'], $func); //签名
                $orderInfo['ip']      = $order['ip']; //ip
                $orderInfo['jumpurl'] = $order['return'];
                $orderInfo['paytype'] = $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a']; //支付编码
//                $config['pay_target'] = $order['payment'] == 1 ? $config['pay_target'].$config['payment_w'] : $config['pay_target'].$config['payment_a'];
                break;
            case 'xiaoyi': //小翼支付
                $orderInfo = [
                    'order_num' => $order['orderid'],//订单号
                    'pay_amount' => number_format($order['amount'], 2),//订单金额
                    'pay_type' => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'return_url' => $order['return'],//同步回调
                    'notify_url' => $order['notify'],//异步回调
                    'app_tm'  => date('Y-m-d H:i:s'),//订单时间
                ];
                $orderInfo['app_key']  = $config['pay_number'];//商户号
//                $orderInfo['client_type']  = 'wap';
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'bossyun': //BOSS云支付
                $orderInfo = [
                    'orderno' => $order['orderid'],//订单号
                    'money' => $order['amount'] * 100,//订单金额
                    'type' => $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'notifyurl' => $order['notify'],//异步回调
                ];
                $orderInfo['channel']  = $config['pay_number'];//商户号
                $orderInfo['attach']  = $order['subject'];
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                break;
            case 'xiyuan':
                $orderInfo = [
                    'order_sn'   => $order['orderid'],//订单号
                    'money' => $order['amount'],//订单金额
                    'channel' =>  $order['payment'] == 1 ? $config['payment_w'] : $config['payment_a'], //支付编码
                    'notify_url'=> $order['notify'], //异步回调
                    'success_url'=> $order['return'], //同步回调
                ];
                $orderInfo['member_id']  = $config['pay_number'];//商户号
                $orderInfo['sign']   = $this->sign($orderInfo,$config['pay_secret'],$func);//签名
                $orderInfo['return_type']  = 1;
                $orderInfo['param']  = 0;
                break;
            default:
                $orderInfo = [];
                break;
        }
        return ['orderInfo' => $orderInfo, 'orderMethod' => $config['pay_method'],
            'orderFormat' => $config['pay_format'], 'orderPayUrl' => $config['pay_target']];
    }


    /**
     * 生成签名结果，可以共用
     * @param $data
     * @param $secret
     * @param $func
     * @return string
     */
    private function sign($data, $secret, $func)
    {
        switch ($func) {
            case 'xiaoyi'://小翼支付
                unset($data['app_key']);
                ksort($data);
                $md5str = "";
                foreach ($data as $key => $val) {
                    if ($key != 'sign' && $val != null && $val != '' && !is_array($val)) {
                        $md5str .= $key . "=" . $val . "&";
                    }
                }
                $sign = strtolower(md5($md5str . $secret));
                break;
            case 'yinyue'://音乐支付
            case 'lizhi'://荔枝支付
                $sign = md5($data['mch_id'] . "|". $data['order_type'] . "|". $data['out_trade_no'] . "|". $data['total_fee'] . "|". $secret);
                break;
            case 'fuxib':
            case 'fuxiy': //伏羲原生
            case 'fuxi':
                $sign = md5($data['fxid'] . $data['fxddh'] . $data['fxfee'] . $data['fxnotifyurl'] . $secret);
                break;
            case 'hengyuan'://恒远支付
            case 'qinaguicode'://钱柜扫码
            case 'qinaguia'://钱柜支付宝
            case 'rongyi':
            case 'xizi'://戏子支付
                if(isset($data['sign'])) unset($data['sign']);
                ksort($data);
                $sign = md5(http_build_query($data).'&key='.$secret);
                break;
            case 'xiaoxuan':
                if(isset($data['sign'])) unset($data['sign']);
                ksort($data);
                $sign_str = '';
                foreach($data as $k=>$v)
                {
                    $sign_str.=($k.'='.$v);
                }
                $sign_str.=$secret;
                $sign = md5($sign_str);
                break;
            case 'panshi'://磐石支付
                $md5str = [];
                ksort($data);
                foreach ($data as $key => $val) {
                    if ($key != 'sign' && $val != null && $val != '' && !is_array($val)) {
                        $md5str[] = $key . "=" . $val;
                    }
                }
                $str  = implode('&', $md5str);
                $sign = md5($str . '&' . $secret);
                break;
            case 'yangyu':
                $md5str = [];
                ksort($data);
                foreach ($data as $key => $val) {
                    if ($key != 'sign' && $val != null && $val != '' && !is_array($val)) {
                        $md5str[] = $val;
                    }
                }
                $str  = implode('|', $md5str);
                $sign = strtolower(md5($str . "|" . $secret));
                break;
            case 'weipay'://小微话费
                $md5str = 'version='.$data['version'].'&customerid='.$data['customerid'].'&total_fee='.$data['total_fee'].'&sdorderno='.$data['sdorderno'].'&notifyurl='.$data['notifyurl'].'&returnurl='.$data['returnurl'].'&'.$secret;
                //把拼接后的字符串再与安全校验码直接连接起来并加密，获得签名结果
                $sign = md5($md5str);
                break;
            case 'baoying'://保盈支付
                //amount、cpid、describe、orderid、product，synurl
                $sign = strtolower(md5($data['amount'] . "|". $data['cpid'] . "|" . $data['describe'] . "|". $data['orderid'] . "|". $data['product'] . "|". $data['synurl'] . "|". $secret));
                break;
            case 'xiyuan':
                if(isset($data['sign'])) unset($data['sign']);
                if(isset($data['param'])) unset($data['param']);
                ksort($data);
                $md5str = "";
                foreach ($data as $key => $val) {
                    if (strtolower($key) != 'sign' && $key != 'sign_type' && $val != null && $val != '' && !is_array($val)) {
                        $md5str .= $key . "=" . $val . "&";
                    }
                }
                $md5str = trim($md5str,'&');
                $sign = strtolower(md5($md5str.$secret));
                break;
            default:
                ksort($data);
                //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
                $md5str = "";
                foreach ($data as $key => $val) {
                    if (strtolower($key) != 'sign' && $key != 'sign_type' && $val != null && $val != '' && !is_array($val)) {
                        $md5str .= $key . "=" . $val . "&";
                    }
                }
                //把拼接后的字符串再与安全校验码直接连接起来并加密，获得签名结果
                $sign = strtoupper(md5($md5str."key=".$secret));
                break;
        }
        return $sign;
    }



    public function checkSign($params, $func, $secret)
    {
        $sign = $params['sign'] ?? '';//部分支付不是这个字段，在switch中自行重置
        switch($func){
            case 'xiaoyi'://小翼支付
                $check = strtolower(md5($params['trade_order_no'] . $params['serial_no'] . $params['payment_code'] . $params['payment_amount'] . $params['real_amount'] . $secret));
                break;
            case 'fuxib':
            case 'fuxiy': //伏羲原生
            case 'fuxi':
                $sign  = $params['fxsign'];
                $check = md5($params['fxstatus'] . $params['fxid'] . $params['fxddh'] . $params['fxfee'] . $secret);
                break;
            case 'weipay'://小微话费
                $md5str = 'customerid='.$params['customerid'].'&status='.$params['status'].'&sdpayno='.$params['sdpayno'].'&sdorderno='.$params['sdorderno'].'&total_fee='.$params['total_fee'].'&paytype='.$params['paytype'].'&'.$secret;
                $check = md5($md5str);
                break;
            case 'baoying'://保盈支付
                //amount、cpid、describe、orderid、product，synurl
                $check = strtolower(md5($params['amount'] . "|". $params['cpid'] . "|". $params['orderid'] . "|". $params['paytime'] . "|". $secret));
                break;
            case 'yangyu':
                $signData = [
                    'cpid'     => $params['cpid'],
                    'orderid'  => $params['orderid'],
                    'amount'   => $params['amount'],
                    'paytime' => $params['paytime'],
                ];
                $check = $this->sign($signData, $secret, $func);
                break;
            default:
                $check = $this->sign($params,$secret,$func);
                break;
        }
        return $sign === $check;
    }


    public function sendOrderPay($order,$func){
        if($order['orderMethod'] == 'GET'){
            $order['orderPayUrl'] .= http_build_query($order['orderInfo']);
        }
        $request = new HttpClient($order['orderPayUrl']);
        //设置等待超时时间
        $request->setTimeout(120);
        //设置连接超时时间
        $request->setConnectTimeout(120);
        switch($func){
            case 'xiaoxuan':
//                $request->setContentTypeFormUrlencoded();
                $response = $request->get();
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['orderid'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['orderid'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'xiaoyi'://小翼支付
                $request->setContentTypeFormUrlencoded();
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['order_num'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['order_num'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'weipay'://小微支付
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['sdorderno'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['sdorderno'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'yangyu'://洋芋支付
                $response = $request->postJson(json($order['orderInfo']));
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['status'] == 0){
                    return ['status' => 0,'payurl' => $result['payurl']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['orderid'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'panshi'://磐石支付
                $response = $request->postJson(json($order['orderInfo']));
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['code'] == 0){
                    return ['status' => 0,'payurl' => $result['obj']['payInfo']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['requestNo'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'fuxib':
            case 'fuxiy': //伏羲原生
            case 'fuxi'://伏羲支付
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['status'] == 1){
                    return ['status' => 0,'payurl' => $result['payurl']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['fxddh'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'cang'://小苍支付
            case 'ergou'://二狗支付
                $request->setContentTypeFormUrlencoded();
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
//                if(isset($result) && $result['status'] == 1){
//                    return ['status' => 0,'payurl' => $result['payurl']];
//                }else{
//                    $error = $result['msg'] ?? $response->getErrMsg();
//                    $error = $error ?: '未知错误';
//                    $orderId = $order['orderInfo']['pay_orderid'];
//                    write_log($orderId.':'.$error);
//                    return ['status' => 1,'msg' => $error];
//                }


                return ['status' => 0,'payurl' => $result];
                break;
            case 'kuanzhai'://宽窄支付
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['pay_orderid'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['pay_orderid'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'qinaguicode'://钱柜扫码
            case 'qinaguia'://钱柜支付宝
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['code'] == 10000){
                    return ['status' => 0,'payurl' => $result['cont']['url']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['ordersn'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'yinyue'://音乐支付
            case 'lizhi':
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['return_code'] == 'Success'){
                    return ['status' => 0,'payurl' => $result['pay_url']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['out_trade_no'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'taimei'://台妹支付
                $request->setContentTypeFormUrlencoded();
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['pay_orderid'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['pay_orderid'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'xizi'://戏子支付
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['order_sn'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['order_sn'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'rongyi'://融易支付
            case 'rongyicode'://融易扫码
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['order_sn'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['order_sn'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'baoying'://保盈支付
                $response = $request->get();
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['status'] == 0){
                    return ['status' => 0,'payurl' => $result['payurl']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['orderid'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'hengyuan'://恒远支付
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['status'] == 10000){
                    return ['status' => 0,'payurl' => $result['data']];
                }else{
                    $res = unjson($result);
                    $error = $res['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['customer_order_no'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'zhongbao'://中宝支付
                $request->setContentTypeFormUrlencoded();
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['code'] == 200){
                    return ['status' => 0,'payurl' => $result['url']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['out_trade_no'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'xiaoding'://小鼎支付
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $cache = RedisPool::defer('redis');
                $key = 'orderPages:id-'.$order['orderInfo']['mchOrderNo'];
                $setting = $cache->get('setting');
                $appUrl = trim($setting['notify_url'],'/');
                $target = $appUrl.'/app/page?orderid='.$order['orderInfo']['mchOrderNo'];
                $cache->set($key,$body,600);
                return ['status' => 0,'payurl' => $target];
                break;
            case 'bossyun'://BOSS支付
                $request->setContentTypeFormUrlencoded();
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['data']['result'] == true){
                    return ['status' => 0,'payurl' => $result['data']['pay_url']];
                }else{
                    $error = $result['message'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['orderno'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            case 'xiyuan':
                $request->setContentTypeFormUrlencoded();
                $response = $request->post($order['orderInfo']);
                $body = $response->getBody();
                $result = unjson($body);
                if(isset($result) && $result['code'] == 0){
                    return ['status' => 0,'payurl' => $result['data']['url']];
                }else{
                    $error = $result['msg'] ?? $response->getErrMsg();
                    $error = $error ?: '未知错误';
                    $orderId = $order['orderInfo']['order_sn'];
                    write_log($orderId.':'.$error);
                    return ['status' => 1,'msg' => $error];
                }
                break;
            default:
                return ['status' => 'ERROR','msg' => '支付失败'];
                break;
        }
    }



    public function getPayInfo($name){
        $cache = RedisPool::defer('redis');
        $key = 'pays:'.$name;
        $info = $cache->get($key);
        if(!$info){
            $info = Pays::create()->where('name',$name)->get();
            switch ($info['pay_method']){
                case 2:
                    $info['pay_method'] = 'GET';
                    break;
                case 3:
                    $info['pay_method'] = 'PJSON';
                    break;
                default:
                    $info['pay_method'] = 'POST';
                    break;
            }
            switch ($info['pay_format']){
                case 2:
                    $info['pay_format'] = 'FORM';
                    break;
                case 3:
                    $info['pay_format'] = 'CURL';
                    break;
                default:
                    $info['pay_format'] = 'JSON';
                    break;
            }
            $cache->set($key,$info);
        }
        return $info;
    }



















}
