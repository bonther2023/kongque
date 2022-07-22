<?php

namespace App\HttpController\Admin;

use App\Model\Categorys;
use App\Model\Harlots;
use App\Model\Images;
use Carbon\Carbon;

class HarlotController extends AuthController
{

    public function category()
    {
        try {
            $model = Categorys::create();
            $lists = $model->getCate(3);
            return $this->writeJson(0, encrypt_data($lists));
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
            $params['cid'] = $params['cid'] ?? 0;
            $params['kwd'] = $params['kwd'] ?? 1;
            $params['status'] = $params['status'] ?? 0;
            $model = Harlots::create();
            $lists = $model->getList($params);
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
            $model = Harlots::create();
            $data['updated_at'] = Carbon::now();
            $data['nums'] = (int)$data['nums'];
            $data['age'] = (int)$data['age'];
            if ($data['id']) {
                $model->update($data,['id' => $data['id']]);
                return $this->writeJson(0, null, '编辑楼凤信息成功');
            } else {
                //新增
                $data['created_at'] = Carbon::now();
                $model->data($data,false)->save();
                return $this->writeJson(0, null, '新增楼凤信息成功');
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
            $model = Harlots::create();
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
