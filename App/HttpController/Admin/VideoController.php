<?php

namespace App\HttpController\Admin;

use App\Model\Categorys;
use Carbon\Carbon;

class VideoController extends AuthController
{

    public function category()
    {
        try {
            $model = Categorys::create();
            $cates = $model->getCate(1);
            $topic = $model->getCate(2);
            return $this->writeJson(0, encrypt_data(['cates' => $cates, 'topic' => $topic]));
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
            $params['kwd'] = $params['kwd'] ?? '';
            $params['status'] = $params['status'] ?? 0;
            $params['cid'] = $params['cid'] ?? 0;
            $params['tid'] = $params['tid'] ?? 0;
            $params['source'] = $params['source'] ?? 'default';
            $model = $this->getVideoModel($params['source']);
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
            $data['source'] = $data['source'] ?? 'default';
            $model = $this->getVideoModel($data['source']);
            $data['topic_id'] = (int)$data['topic_id'];
            if ($data['id']) {
                //编辑
                $info = $model->get($data['id']);
                if($info['status'] == 3){
                    $data['date'] = Carbon::now()->toDateString();
                    $data['created_at'] = Carbon::now();
                }
                $info->update($data);
                return $this->writeJson(0, null, '编辑视频信息成功');
            } else {
                //新增
                $data['date'] = Carbon::now()->format('Y-m-d');
                $data['created_at'] = Carbon::now();
                $model->data($data,false)->save();
                return $this->writeJson(0, null, '新增视频信息成功');
            }
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function info()
    {
        try {
            $data = $this->getParams();
            $source = $data['source'] ?? 'default';
            $id = $data['id'] ?? 0;
            $model = $this->getVideoModel($source);
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            return $this->writeJson(0, encrypt_data($info));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function lock()
    {
        try {
            $data = $this->getParams();
            $source = $data['source'] ?? 'default';
            $id = $data['id'] ?? 0;
            $model = $this->getVideoModel($source);
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => 3]);
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
            $source = $data['source'] ?? 'default';
            $id = $data['id'] ?? 0;
            $model = $this->getVideoModel($source);
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->update(['status' => 1]);
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
            $source = $data['source'] ?? 'default';
            $id = $data['id'] ?? 0;
            $model = $this->getVideoModel($source);
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
