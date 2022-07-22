<?php

namespace App\HttpController\Admin;

use App\Model\Agents;
use App\Model\Trades;
use App\Utility\JwtToken;
use Carbon\Carbon;
use EasySwoole\Utility\Hash;

class AgentController extends AuthController
{
    public function select()
    {
        try {
            $model = Agents::create();
            //获取代理
            $agents = $model->getCascaderList();
            return $this->writeJson(0, encrypt_data($agents));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function domain()
    {
        try {
            $setting = settings();
            $canalLink = trim($setting['agent_admin'],'/');
            return $this->writeJson(0, encrypt_data($canalLink));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = $params['page'] ?? 1;
            $params['id'] = $params['id'] ?? 0;
            $params['username'] = $params['username'] ?? '';
            $params['status'] = $params['status'] ?? 0;
            $model = Agents::create();
            $fields = 'id,username,nickname,status,created_at,balance,login_at,rebate,name,qq,bank,card';
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
            $model = Agents::create();
            $data['updated_at'] = Carbon::now();
            if ($data['id']) {
                //编辑
                if ($data['password']) {
                    $data['password'] = Hash::makePasswordHash($data['password']);
                } else {
                    unset($data['password']);
                }
                $model->update($data,['id' => $data['id']]);
                return $this->writeJson(0, null, '编辑代理信息成功');
            } else {
                //新增
                $data['created_at'] = Carbon::now();
                $data['login_at'] = Carbon::now();
                $data['password'] = Hash::makePasswordHash($data['password'] ?: '123456');
                $model->data($data,false)->save();
                return $this->writeJson(0, null, '新增代理信息成功');
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
            $model = Agents::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => Agents::STATUS_2]);
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
            $id = $data['id'] ?: 0;
            $model = Agents::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => Agents::STATUS_1]);
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
            $model = Agents::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->destroy();
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function login(){
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            $model = Agents::create();
            $info = $model->get($id);
            $jwtToken = new JwtToken();
            $token = $jwtToken->token($id);
            $this->response()->setCookie('agent_login', $token, expires(86400 * 7));
            $this->response()->setCookie('agent_user', $info['nickname'], expires(86400 * 7));
            $this->response()->redirect(url_agent('main'));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->response()->write($e->getMessage());
        }
    }


    public function trade(){
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            $model = Agents::create();
            $info = $model->get($id);
            if($info['status'] == Agents::STATUS_2){
                return $this->writeJson(1,null,'账号被锁定');
            }
            if(empty($info['name']) || empty($info['bank']) || empty($info['card'])){
                return $this->writeJson(1,null,'代理银行资料信息不完整');
            }
            $setting = settings();
            if($info['balance'] < $setting['trade_money']){
                return $this->writeJson(1,null,'结算金额低于最低结算金额'.$setting['trade_money'].'元，不能结算');
            }
            $insert = [
                'date' => date('Y-m-d',time()),
                'type' => 'agent',
                'userid' => $info['id'],
                'username' => $info['username'],
                'money' => $info['balance'],
                'name' => $info['name'],
                'bank' => $info['bank'],
                'card' =>$info['card']
            ];
            Trades::create()->data($insert,false)->save();
            $model->update(['balance' => ["[I]" => "-" . $info['balance']],'id' => $info['id']]);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
