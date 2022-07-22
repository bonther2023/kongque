<?php

namespace App\HttpController\Admin;

use App\Model\Orders;
use EasySwoole\RedisPool\RedisPool;

class OrderController extends AuthController
{

    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = $params['page'] ?? 1;
            $params['status'] = $params['status'] ?? 0;
            $params['kwd'] = $params['kwd'] ?? '';
            $params['start'] = $params['start'] ?? '';
            $params['end'] = $params['end'] ?? '';
            $params['payment'] = $params['payment'] ?? 0;
            $params['cid'] = $params['cid'] ?? 0;
            $params['system'] = $params['system'] ?? '';
            $params['platform'] = $params['platform'] ?? '';
            $params['name'] = $params['name'] ?? '';
            $model = Orders::create();
            $data = $model->getList($params);
            return $this->writeJson(0, encrypt_data($data));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }




    public function update()
    {
        try {
            $params = $this->getParams();
            $oid = $params['id'] ?? 0;
            $model = Orders::create();
            $info  = $model->get($oid);
            if ($info) {
                $redis = RedisPool::defer('redis');
                $job = json([
                    'oid' => $info['id']
                ]);
                $redis->rPush('queue:update-user',$job);
                return $this->writeJson(0, null, '操作成功');
            } else {
                return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            }
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }
}
