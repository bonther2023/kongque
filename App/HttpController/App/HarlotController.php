<?php

namespace App\HttpController\App;

use App\Model\Harlots;

class HarlotController extends AuthController
{

    public function new(){
        try {
            $model = Harlots::create();
            $newHarlot = $model->getAppNewHarlot();
            return $this->writeJson(0,encrypt_data($newHarlot));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }


    }

    public function list(){
        try {
            $data = $this->getParams();
            $params['page'] = (int)$data['page'] ?? 1;
            $params['cid'] = (int)$data['cid'] ?? 0;
            $params['pname'] = (string)$data['pname'] ?? '';
            $params['cname'] = (string)$data['cname'] ?? '';
            $params['rname'] = (string)$data['rname'] ?? '';
            $params['status'] = 1;
            $lists = Harlots::create()->getList($params, 'id,title,cate_id,province,city,region', 20);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }


    }


    public function info(){
        try {
            $data = $this->getParams();
            $id = (int)$data['id'] ?? 0;
            if (empty($id)) return $this->writeJson(1, null, '参数非法');
            $model = Harlots::create();
            $info = $model->getAppHarlotInfo($id);
            if (empty($info)) return $this->writeJson(1, null, '抱歉，数据不存在');
            $info['buy'] = $this->userVipStatus();
            return $this->writeJson(0, encrypt_data($info));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }


    }





}
