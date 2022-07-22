<?php

namespace App\HttpController\App;

use App\Model\Novels;

class NovelController extends AuthController
{

    public function new(){
        try {
            $model = Novels::create();
            $newNovel = $model->getAppNewNovel();
            return $this->writeJson(0,encrypt_data($newNovel));
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
            $params['kwd'] = '';
            $lists = Novels::create()->getList($params, 'id,title,cate_id,created_at', 20);
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
            $model = Novels::create();
            $info = $model->getAppNovelInfo($id);
            if (empty($info)) return $this->writeJson(1, null, '抱歉，数据不存在');
            $info['buy'] = $this->userVipStatus();
            return $this->writeJson(0, encrypt_data($info));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
