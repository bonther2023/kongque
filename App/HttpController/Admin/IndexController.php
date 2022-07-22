<?php

namespace App\HttpController\Admin;

use App\Model\Configs;
use App\Model\MuVideo;
use App\Model\Orders;
use App\Model\Pays;
use App\Model\Records;
use App\Model\Users;
use App\Model\Videos;
use Carbon\Carbon;
use EasySwoole\RedisPool\RedisPool;

class IndexController extends AuthController{

    public function socket(){
        try {
            $setting = settings();
            $ws = trim($setting['socket_link'],'/');
            return $this->writeJson(0, encrypt_data($ws));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function monitor(){
        try {
            $settings = settings();
            $aPays    = $settings['payment_type_alipay'];
            $wPays    = $settings['payment_type_wechat'];
            $hfaPays  = $settings['tel_payment_type_alipay'];
            $hfwPays  = $settings['tel_payment_type_wechat'];
            $cPays    = $settings['payment_type_code'];
            $hfcPays  = $settings['tel_payment_type_code'];

            $aPays    = $aPays ? explode('-', $aPays) : [];
            $wPays    = $wPays ? explode('-', $wPays) : [];
            $hfaPays  = $hfaPays ? explode('-', $hfaPays) : [];
            $hfwPays  = $hfwPays ? explode('-', $hfwPays) : [];
            $cPays    = $cPays ? explode('-', $cPays) : [];
            $hfcPays  = $hfcPays ? explode('-', $hfcPays) : [];
            $result  = [];
            if(count($cPays)){
                $cPays = array_unique($cPays);
                foreach ($cPays as $cPay){
                    $result[] = $this->getPaygod($cPay,1,3);
                }
            }
            if(count($hfcPays)){
                $hfcPays = array_unique($hfcPays);
                foreach ($hfcPays as $hfcPay){
                    $result[] = $this->getPaygod($hfcPay,2,3);
                }
            }
            if(count($aPays)){
                $aPays = array_unique($aPays);
                foreach ($aPays as $aPay){
                    $result[] = $this->getPaygod($aPay,1,2);
                }
            }
            if(count($wPays)){
                $wPays = array_unique($wPays);
                foreach ($wPays as $wPay){
                    $result[] = $this->getPaygod($wPay,1,1);
                }
            }
            if(count($hfaPays)){
                $hfaPays = array_unique($hfaPays);
                foreach ($hfaPays as $hfaPay){
                    $result[] = $this->getPaygod($hfaPay,2,2);
                }
            }
            if(count($hfwPays)){
                $hfwPays = array_unique($hfwPays);
                foreach ($hfwPays as $hfwPay){
                    $result[] = $this->getPaygod($hfwPay,2,1);
                }
            }
            $userModel = Users::create();
            $iosNum = $userModel->field('id')->where('app_system', 'iOS')
                ->where('created_at', date('Y-m-d H:i:s',time()-300), '>=')
                ->count();
            $andNum = $userModel->field('id')->where('app_system', 'Android')
                ->where('created_at', date('Y-m-d H:i:s',time()-300), '>=')
                ->count();
            return $this->writeJson(0, encrypt_data([
                'success' => $result, 'ios' => $iosNum, 'android' => $andNum,
                'warning_order' => $settings['warning_order'],
                'warning_ios' => $settings['warning_ios'],
                'warning_android' => $settings['warning_android']
            ]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }



    public function getPaygod($name,$channel,$payment){
        $settings = settings();
        $model    = Orders::create();
        $payModel = Pays::create();
        $nums    = $settings['warning_order'];
        $payInfo = $payModel->field('title')->where('name', $name)->get();
        $orders  = $model->field('status')->where('platform', $name)
            ->where('channel', $channel)
            ->where('payment',$payment)
            ->limit($nums)
            ->order('created_at', 'desc')
            ->all();
        $yesPayOrders   = [];
        $yesPayOrders[] = array_filter($orders, function ($item) {
            return $item['status'] == 2;
        });
        $yesPay = count($yesPayOrders[0]);
        if($channel == 1){
            $a = '普';
        }else{
            $a = '话';
        }
        if($payment == 1){
            $b = 'W';
        }elseif($payment == 2){
            $b = 'A';
        }else{
            $b = 'C';
        }
        $result = [];
        $result['platform'] = '['.$a.']'.$payInfo['title'].'('.$b.')';
        $result['yespay']   = $yesPay;
        $result['totalpay'] = count($orders);
        $result['success']  = count($orders) ? (number_format($yesPay / (count($orders)), 2) * 100) . '%' : '0%';
        if($yesPay < 3 && count($orders) == $nums){
            $result['hong']   = 1;
        }else{
            $result['hong']   = 0;
        }
        return $result;
    }



    public function online(){
        try {
            $cache = RedisPool::defer('redis');
            $setting = $cache->get('setting');
            if (!$setting) {
                $setting = Configs::create()->getList();
                $cache->set('setting', $setting);
            }
            $time = time() + 100;
            $min = $time - (int)($setting['online_time']*60);
            $nums = $cache->zCount('online',$min,(int)$time);
            return $this->writeJson(0, encrypt_data($nums));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    /**
     * 首页
     */
    public function count(){
        try {
            //视频数量
            $videoTotal = 10256;
            //用户数量
            $userTotal = 589648;
            //一周活跃用户数量
            $userActiveTotal = 356452;
            //充值数量
            $recordModel = Records::create();
//            $profitTotal = $recordModel->sum('profit');
//            $payableTotal = $recordModel->sum('payable');
//            $rechageTotal = $profitTotal + $payableTotal;

            $date = Carbon::now()->toDateString();
            $yestoday = Carbon::now()->subDays()->toDateString();

            //今日/昨日收益
//            $nowProfit = $recordModel->where('date',$date)->sum('profit');
//            $yesProfit = $recordModel->where('date',$yestoday)->sum('profit');

            //今日/昨日订单数
            $nowSettlement= $recordModel->where('date',$date)->sum('settlement');
            $yesSettlement = $recordModel->where('date',$yestoday)->sum('settlement');
            $nowDeduct= $recordModel->where('date',$date)->sum('deduct');
            $yesDeduct = $recordModel->where('date',$yestoday)->sum('deduct');
            $nowOrderNum = $nowSettlement + $nowDeduct;
            $yesOrderNum = $yesSettlement + $yesDeduct;

            //今日/昨日新增用户数
            $nowSInstall= $recordModel->where('date',$date)->sum('s_install');
            $yesSInstall = $recordModel->where('date',$yestoday)->sum('s_install');
            $nowDInstall= $recordModel->where('date',$date)->sum('d_install');
            $yesDInstall = $recordModel->where('date',$yestoday)->sum('d_install');
            $nowSInstall = $nowSInstall + $nowDInstall;
            $yesSInstall = $yesSInstall + $yesDInstall;

            //今日/昨日活跃用户数
            $userModel = Users::create();
            $nowActiveUser = $userModel->where('login_at', $date.' 00:00:00', '>=')
                ->where('login_at', $date.' 23:59:59', '<=')->count();
            $yesActiveUser = $userModel->where('login_at', $yestoday.' 00:00:00', '>=')
                ->where('login_at', $yestoday.' 23:59:59', '<=')->count();

            $order = Orders::create();
            //新用户支付订单数
            $newUserOrderPayNum = $order->where('created_at', $date.' 00:00:00', '>=')
                ->where('created_at', $date.' 23:59:59', '<=')->where('status',Orders::STATUS_2)
                ->where('is_new',1)->count();
            //老用户支付订单数
            $oldUserOrderPayNum = $order->where('created_at', $date.' 00:00:00', '>=')
                ->where('created_at', $date.' 23:59:59', '<=')->where('status',Orders::STATUS_2)
                ->where('is_new',2)->count();
            //新用户订单金额
            $newUserOrderMoney = $order->where('created_at', $date.' 00:00:00', '>=')
                ->where('created_at', $date.' 23:59:59', '<=')->where('status',Orders::STATUS_2)
                ->where('is_new',1)->sum('money');
            //老用户订单金额
            $oldUserOrderMoney = $order->where('created_at', $date.' 00:00:00', '>=')
                ->where('created_at', $date.' 23:59:59', '<=')->where('status',Orders::STATUS_2)
                ->where('is_new',2)->sum('money');
            //新用户订单数
            $newUserOrderNum = $order->where('created_at', $date.' 00:00:00', '>=')
                ->where('created_at', $date.' 23:59:59', '<=')
                ->where('is_new',1)->count();
            //老用户订单数
            $oldUserOrderNum = $order->where('created_at', $date.' 00:00:00', '>=')
                ->where('created_at', $date.' 23:59:59', '<=')
                ->where('is_new',2)->count();
            return $this->writeJson(0, encrypt_data([
                'videoTotal' => $videoTotal,
                'userTotal' => $userTotal,
                'userActiveTotal' => $userActiveTotal,
                'rechageTotal' => 0,
                'nowProfit' => 0,
                'yesProfit' => 0,
                'nowOrderNum' => $nowOrderNum,
                'yesOrderNum' => $yesOrderNum,
                'yesSInstall' => $yesSInstall,
                'yesDInstall' => $yesDInstall,
                'nowSInstall' => $nowSInstall,
                'nowDInstall' => $nowDInstall,
                'nowActiveUser' => $nowActiveUser,
                'yesActiveUser' => $yesActiveUser,
                'newUserOrderPayNum' => $newUserOrderPayNum,
                'oldUserOrderPayNum' => $oldUserOrderPayNum,
                'newUserOrderMoney' => $newUserOrderMoney,
                'oldUserOrderMoney' => $oldUserOrderMoney,
                'newUserOrderNum' => $newUserOrderNum,
                'oldUserOrderNum' => $oldUserOrderNum
            ]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }



}
