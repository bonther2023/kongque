<?php

namespace App\HttpController\Admin;

use App\Model\Admins;
use Carbon\Carbon;
use EasySwoole\Utility\Hash;

class ManagerController extends AuthController
{



    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = $params['page'] ?? 1;
            $params['kwd'] = $params['kwd'] ?? '';
            $params['status'] = $params['status'] ?? 0;
            $model = Admins::create();
            $fields = 'id, username, nickname, role_id, status, created_at, login_at';
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
            $model = Admins::create();
            $data['updated_at'] = Carbon::now();
            //'管理员名称已存在，请重新填写'
            $info = $model->where('username', $data['username'])->where('id', $data['id'], '<>')->get();
            if($info){
                return $this->writeJson(1, null, '管理员名称已存在，请重新填写');
            }
            if ($data['id']) {
                //编辑
                if ($data['password']) {
                    $data['password'] = Hash::makePasswordHash($data['password']);
                } else {
                    unset($data['password']);
                }
                $model->update($data,['id' => $data['id']]);
                return $this->writeJson(0, null, '编辑管理员信息成功');
            } else {
                //新增
                $data['created_at'] = Carbon::now();
                $data['login_at'] = Carbon::now();
                $data['password'] = Hash::makePasswordHash($data['password'] ?: '123456');
                $model->data($data,false)->save();
                return $this->writeJson(0, null, '新增管理员信息成功');
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
            $model = Admins::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0, null, '抱歉，你要操作的信息不存在');
            $info->update(['status' => Admins::STATUS_2]);
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
            $model = Admins::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0, null, '抱歉，你要操作的信息不存在');
            $info->update(['status' => Admins::STATUS_1]);
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
            $model = Admins::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0, null, '抱歉，你要操作的信息不存在');
            $info->destroy();
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
