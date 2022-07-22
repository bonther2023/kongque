<?php

namespace App\HttpController\Admin;

use App\Model\Orders;
use App\Model\Pays;
use App\Model\Users;
use Carbon\Carbon;
use EasySwoole\RedisPool\RedisPool;

class PayController extends AuthController
{
    public function select(){
        try {
            $pays  = Pays::create()
                ->where('status',Pays::STATUS_1)
                ->field('title,name')
                ->order('id','desc')
                ->all();
            return $this->writeJson(0, encrypt_data($pays));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    function list() {
        try {
            $params = $this->getParams();
            $params['page'] = (int)$params['page'] ?? 1;
            $params['kwd'] = $params['kwd'] ?? '';
            $params['status'] = (int)$params['status'] ?? 0;
            $model  = Pays::create();
            $lists  = $model->getList($params);
            $redis = RedisPool::defer('redis');
            $setting = $redis->get('setting');
            return $this->writeJson(0, encrypt_data(['lists' => $lists,'notify' => $appUrl = trim($setting['notify_url'],'/').'/']));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function update()
    {
        try {
            $data = $this->getParams();
            $model  = Pays::create();
            $data['updated_at'] = Carbon::now();
            $info = $model->where('name', $data['name'])->where('id', $data['id'], '<>')->get();
            if($info){
                return $this->writeJson(1, null, '支付标识已存在，请重新填写');
            }
            if ($data['id']) {
                $model->update($data,['id' => $data['id']]);
                $cache = RedisPool::defer('redis');
                $payKey = 'pays:'.$data['name'];
                $cache->del($payKey);
                return $this->writeJson(0, null, '编辑支付信息成功');
            } else {
                //新增
                $data['created_at'] = Carbon::now();
                $model->data($data,false)->save();
                return $this->writeJson(0, null, '新增支付信息成功');
            }
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function lock()
    {
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            $model = Pays::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => Pays::STATUS_2]);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function active()
    {
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            $model = Pays::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => Pays::STATUS_1]);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function destroy()
    {
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            $model  = Pays::create();
            $info    = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->destroy();
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function paySuccess()
    {
        try {
            $settings = settings();
            $aPays    = $settings['payment_type_alipay'];
            $wPays    = $settings['payment_type_wechat'];
            $hfaPays  = $settings['hf_payment_type_alipay'];
            $hfwPays  = $settings['hf_payment_type_wechat'];
            $cPays  = $settings['hf_payment_type_alipay'];
            $hfcPays  = $settings['payment_type_code'];
            $aPays    = $aPays ? explode('-', $aPays) : [];
            $wPays    = $wPays ? explode('-', $wPays) : [];
            $hfaPays  = $hfaPays ? explode('-', $hfaPays) : [];
            $hfwPays  = $hfwPays ? explode('-', $hfwPays) : [];
            $pays     = array_merge($aPays, $wPays, $hfaPays, $hfwPays);
            $model    = new Orders();
            $payModel = new Pays();
            $resault  = [];
            foreach ($pays as $key => $pay) {
                $payInfo = $payModel->where('name', $pay)->first();
                $orders  = $model->where('platform', $pay)
                    ->take(40)
                    ->orderBy('created_at', 'DESC')
                    ->get();
                $yesPayOrders   = [];
                $yesPayOrders[] = array_filter($orders, function ($item) {
                    return $item['status'] == 2;
                });
                $yesPay                    = count($yesPayOrders[0]);
                $resault[$key]['platform'] = $payInfo['title'];
                $resault[$key]['yespay']   = $yesPay;
                $resault[$key]['totalpay'] = count($orders);
                $resault[$key]['success']  = count($orders) ? (number_format($yesPay / (count($orders)), 2) * 100) . '%' : '0%';
                if($yesPay < 3 && count($orders) == 40){
                    $resault[$key]['hong']   = 1;
                }else{
                    $resault[$key]['hong']   = 0;
                }
            }
            $userModel = new Users();
            $iosNum    = $userModel->where('name', 'iOS')
                ->where('created_at', Carbon::now()->subMinutes(5), '>=')
                ->count();
            return $this->writeJson(0, ['success' => $resault, 'ios' => $iosNum]);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
