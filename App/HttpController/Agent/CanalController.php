<?php

namespace App\HttpController\Agent;

use App\Model\Agents;
use App\Model\Canals;
use EasySwoole\Utility\Hash;

class CanalController extends AuthController
{

    public function index()
    {
        try {
            $lists = Canals::create()->field('id,username')
                ->order('id')
                ->all(['status' => Canals::STATUS_1,'agent_id' => $this->userid]);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }

    public function list()
    {
        try {
            $data = $this->getParams();
            $params = [
                'page' => $data['page'] ?? 1,
                'kwd' => $data['kwd'] ?? '',
                'agent_id' => $this->userid,
            ];
            $model = Canals::create();
            $fields = 'c.id,c.username,c.canal_rebate,c.balance,c.login_at,c.nickname,c.status,c.agent_id';
            $lists = $model->getList($params, $fields, 9);
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
            $id = $data['id'] ?? 0;
            $model = Canals::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(1,null,'抱歉，你要操作的信息不存在');
            if(!$data['new_password']){
                return $this->writeJson(1, null, '请输入新密码');
            }
            $info->update(['password' => Hash::makePasswordHash($data['new_password'])]);
            return $this->writeJson(0, null, '更新成功');
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


}
