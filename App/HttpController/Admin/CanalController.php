<?php

namespace App\HttpController\Admin;

use App\Model\Canals;
use App\Model\Trades;
use App\Utility\JwtToken;
use Carbon\Carbon;
use EasySwoole\Utility\Hash;

class CanalController extends AuthController
{

    public function domain()
    {
        try {
            $setting = settings();
            $canalLink = trim($setting['canal_admin'],'/');
            return $this->writeJson(0, encrypt_data($canalLink));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function select()
    {
        try {
            $model = Canals::create();
            //获取代理
            $agents = $model->getCascaderList();
            return $this->writeJson(0, encrypt_data($agents));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }



    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = (int)$params['page'] ?? 1;
            $params['agent_id'] = (int)$params['agent_id'] ?? 0;
            $params['id'] = $params['id'] ?? 0;
            $params['username'] = $params['username'] ?? '';
            $params['status'] = (int)$params['status'] ?? 0;
            $model = Canals::create();
            $fields = 'c.id,c.agent_id,c.username,c.name,c.canal_rebate,c.status,c.created_at,c.balance,c.apk_name,c.login_at,c.nickname,
        c.qq,c.bank,c.card,c.order_num,c.amount_new_user,c.day_vip_rebate,c.month_vip_rebate,c.quarter_vip_rebate,c.year_vip_rebate,
        c.forever_vip_rebate,c.register_rebate,a.username as agent_name';
            $lists = $model->getList($params, $fields);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function update()
    {
        try {
            $data = $this->getParams();
            $model = Canals::create();
            $data['updated_at'] = Carbon::now();
            if ($data['id']) {
                //编辑
                if ($data['password']) {
                    $data['password'] = Hash::makePasswordHash($data['password']);
                } else {
                    unset($data['password']);
                }
                $model->update($data,['id' => $data['id']]);
                return $this->writeJson(0, null, '编辑渠道信息成功');
            } else {
                //新增
                $data['created_at'] = Carbon::now();
                $data['login_at'] = Carbon::now();
                $data['password'] = Hash::makePasswordHash($data['password'] ?: '123456');
                $model->data($data,false)->save();
                return $this->writeJson(0, null, '新增渠道信息成功');
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
            $id = $data['id'] ?: 0;
            $model = Canals::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => Canals::STATUS_2]);
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
            $model = Canals::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => Canals::STATUS_1]);
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
            $model = Canals::create();
            $info = $model->find($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $model->destroy();
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function login(){
        try {
            $data = $this->getParams();
            $id = $data['id'] ?: 0;
            $model = Canals::create();
            $info = $model->get($id);
            $jwtToken = new JwtToken();
            $token = $jwtToken->token($id);
            $this->response()->setCookie('canal_login', $token, expires(86400 * 7));
            $this->response()->setCookie('canal_user', $info['nickname'], expires(86400 * 7));
            $this->response()->redirect(url_canal('main'));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->response()->write($e->getMessage());
        }
    }

    public function trade(){
        try {
            $request = $this->request();
            $id = (int)trim($request->getRequestParam('id')) ?: 0;
            $model = Canals::create();
            $info = $model->get($id);
            if($info['status'] == Canals::STATUS_2){
                return $this->writeJson(1,null,'账号被锁定');
            }
            if(empty($info['name']) || empty($info['bank']) || empty($info['card'])){
                return $this->writeJson(1,null,'渠道银行资料信息不完整');
            }
            $setting = settings();
            if($info['balance'] < $setting['trade_money']){
                return $this->writeJson(1,null,'结算金额低于最低结算金额'.$setting['trade_money'].'元，不能结算');
            }
            $insert = [
                'date' => date('Y-m-d',time()),
                'type' => 'canal',
                'userid' => $info['id'],
                'username' => $info['username'],
                'money' => $info['balance'],
                'name' => $info['name'],
                'bank' => $info['bank'],
                'card' =>$info['card']
            ];
            Trades::create()->data($insert,false)->save();
            $model->update(['id' => $info['id'],'balance' => ["[I]" => "-" . $info['balance']]]);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
