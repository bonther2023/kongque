<?php

namespace App\HttpController\App;

use App\Model\Orders;
use App\Model\Users;
use App\Utility\Pay;
use Carbon\Carbon;
use EasySwoole\RedisPool\RedisPool;

class OrderController extends AuthController
{


    public function create(){
        try {
            $data = $this->getParams();
            $setting = settings();
            $limit = $this->limit($this->userid,$setting['limit_order']);
            if($limit){
                return $this->writeJson(1, null, '您的操作太频繁，请稍后再试');
            }
            $show = (intval($data['money']) % 10)== 0 ? 2 : 1;//2话费 1普通
            //订单重复验证
            $orderModel = Orders::create();
            $orderInfo = $orderModel->where('order_id', $data['order_id'])->order('created_at','desc')->get();
            if($orderInfo){
                return $this->writeJson(1,null,'请勿重复提交订单');
            }

            //订单基本信息
            $vip = $this->getVipLevel(intval($data['money']),$show);
            $platform = $this->getPayPlatform($data['payment'],$show);
            if(!$platform){
                return $this->writeJson(1, null, '抱歉，暂无可用的支付渠道');
            }

            //生成订单
            $userModel = new Users();
            $userInfo = $userModel->get($this->userid);
            $new = Carbon::now()->diffInSeconds($userInfo['created_at']) > 86400 ? 2 : 1;//注册时间超过24小时算老用户充值
            $order = [
                'order_id'  => $data['order_id'],
                'user_id'   => $userInfo['id'],
                'username'  => $userInfo['username'],
                'system'    => $userInfo['app_system'],
                'agent_id'  => $userInfo['agent_id'],
                'canal_id'  => $userInfo['canal_id'],
                'money'     => $data['money'],
                'title'     => $data['title'],
                'channel'   => $show,
                'vip'       => $vip,
                'is_new'    => $new,
                'platform'  => $platform,
                'payment'   => $data['payment'],
                'status'    => 1,
                'deduct'    => 1,
                'created_at'=> Carbon::now(),
            ];
            $oid = $orderModel->data($order,false)->save();
            if($oid){
                // 订单日志
                $redis = RedisPool::defer('redis');
                $job = json([
                    'aid' => $userInfo['agent_id'],
                    'cid' => $userInfo['canal_id'],
                    'time' => time()
                ]);
                $redis->rPush('queue:update-order',$job);
                $appUrl = trim($setting['notify_url'],'/');
                return $this->writeJson(0, encrypt_data([
                    'payurl' => $appUrl.'/app/defray?orderid='.$order['order_id'],
                ]));
            }else{
                return $this->writeJson(1, null, '抱歉，生成订单失败');
            }
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function check(){
        try {
            $data = $this->getParams();
            $orderId = $data['order_id'] ?? '';
            if($orderId){
                $model = Orders::create();
                $info = $model->where('order_id', $orderId)->order('created_at','desc')->get();
                if($info && $info['status'] == 2){
                    return $this->writeJson(0);
                }
            }
            return $this->writeJson(1);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


//$pay = new Pay();
//$config = $pay->getPayInfo($platform);
//$payOrder = [
//"payment" => $order['payment'], // 支付类型
//"orderid" => $order['order_id'], // 商户订单号
//"amount"  => $order['money'], // 支付金额，单位元
//"notify"  => $appUrl.'/app/notify/'.$config['return_notify'], // 异步回调，支付结果以异步为准
//"return"  => 'https://www.baidu.com', // 同步回调，不作为最终支付结果为准，请以异步回调为准
//"subject" => '用户VIP充值', // 商品名称
//"ip" => $this->getIp(),
//];
//$payOrderInfo = $pay->getOrderInfo($payOrder,$platform,$config);
//$curlPayInfo = $pay->sendOrderPay($payOrderInfo,$platform);
//if($curlPayInfo['status']){
//return $this->writeJson(1, null, $curlPayInfo['msg']);
//}
//return $this->writeJson(0, encrypt_data([
//    'payurl' => $curlPayInfo['payurl'],
//]));


    protected function getVipLevel($money,$show){
        $settings = settings();
        if($show == 2){
            //话费
            $vips = [
                'day_vip' => $settings['tel_day_vip'],
                'month_vip' => $settings['tel_month_vip'],
                'quarter_vip' => $settings['tel_quarter_vip'],
                'year_vip' => $settings['tel_year_vip'],
                'forever_vip' => $settings['tel_forever_vip'],
            ];
        }else{
            //普通
            $vips = [
                'day_vip' => $settings['day_vip'],
                'month_vip' => $settings['month_vip'],
                'quarter_vip' => $settings['quarter_vip'],
                'year_vip' => $settings['year_vip'],
                'forever_vip' => $settings['forever_vip'],
            ];
        }
        $vip = 'day_vip';
        foreach ($vips as $key => $item){
            if($item == $money){
                $vip = $key;
                break;
            }
        }
        return $vip;
    }


    protected function getPayPlatformsddd($payment,$show){
        $setting = settings();
        if($show == 2){
            //话费
            if ($payment == 1) {
                $payClassArr = explode('-',$setting['tel_payment_type_wechat']);
            } else {
                $payClassArr = explode('-',$setting['tel_payment_type_alipay']);
            }
        }else{
            //普通
            if ($payment == 1) {
                $payClassArr = explode('-',$setting['payment_type_wechat']);
            } else {
                $payClassArr = explode('-',$setting['payment_type_alipay']);
            }
        }
        $payClassKey = array_rand($payClassArr,1);
        $payClassName = $payClassArr[$payClassKey];
        return $payClassName;
    }

    protected function getPayPlatform($payment,$show){
        $setting = settings();
        if($show == 2){
            //话费
            switch ($payment){
                case 1:
                    $payClassStr = $setting['tel_payment_type_wechat'];
                    break;
                case 2:
                    $payClassStr = $setting['tel_payment_type_alipay'];
                    break;
                default:
                    $payClassStr = $setting['tel_payment_type_code'];
                    break;
            }
        }else{
            switch ($payment){
                case 1:
                    $payClassStr = $setting['payment_type_wechat'];
                    break;
                case 2:
                    $payClassStr = $setting['payment_type_alipay'];
                    break;
                default:
                    $payClassStr = $setting['payment_type_code'];
                    break;
            }
        }
        if($payClassStr){
            $payClassArr = explode('-',$payClassStr);
            $payClassKey = array_rand($payClassArr,1);
            $payClassName = $payClassArr[$payClassKey];
        }else{
            $payClassName = '';
        }

        return $payClassName;
    }

    protected function limit($uid = 0,$limit = 15, $time = 600){
        $redis = RedisPool::defer('redis');
        $key = 'limit:user_'.$uid;
        $exists = $redis->exists($key);
        if ($exists) {
            $count = $redis->get($key);
            if($count > $limit){
                return true;
            }else{
                $redis->incr($key);
                return false;
            }
        } else {
            $redis->incr($key);
            // 首次计数 设定过期时间
            $redis->expire($key, $time);
            return false;
        }
    }

}
