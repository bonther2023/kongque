<?php

namespace App\HttpController\Admin;

use App\Model\Trades;

class TradeController extends AuthController
{

    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = $params['page'] ?? 1;
            $params['status'] = $params['status'] ?? 0;
            $params['start'] = $params['start'] ?? '';
            $params['end'] = $params['end'] ?? '';
            $params['username'] = $params['username'] ?? '';
            $params['userid'] = 0;
            $params['type'] = '';
            $model = Trades::create();
            $fields = 'id,date,userid,username,money,status,name,bank,card';
            $lists = $model->getList($params, $fields);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function status()
    {
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            $model = Trades::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => Trades::STATUS_2]);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function update()
    {
        try {
            $data = $this->getParams();
            $model = Trades::create();
            $info = $model->get($data['id']);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['money' => $data['money']]);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
