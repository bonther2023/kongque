<?php

namespace App\HttpController\Admin;

use App\Model\Ads;
use App\Model\VideoBilis;
use Carbon\Carbon;

class AdController extends AuthController
{


    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = $params['page'] ?? 1;
            $model = Ads::create();
            $fields = 'id,thumb,position,width,height';
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
            $model = Ads::create();
            $data['updated_at'] = Carbon::now();
            $data['width'] = (int)$data['width'];
            $data['height'] = (int)$data['height'];
            $data['thumb'] =  json($data['thumb']);
            if ($data['id']) {
                $model->update($data,['id' => $data['id']]);
                return $this->writeJson(0, null, '编辑广告信息成功');
            } else {
                //新增
                $data['created_at'] = Carbon::now();
                $model->data($data,false)->save();
                return $this->writeJson(0, null, '新增广告信息成功');
            }
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
            $model = Ads::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->destroy();
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
