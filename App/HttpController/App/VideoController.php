<?php

namespace App\HttpController\App;

use App\Model\Users;

class VideoController extends AuthController
{

    public function good(){
        try {
            $setting = settings();
            $model = $this->getVideoModel($setting['video_resource']);
            $videoGood = $model->getAppGoodVideo();
            return $this->writeJson(0,encrypt_data($videoGood));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function new(){
        try {
            $setting = settings();
            $model = $this->getVideoModel($setting['video_resource']);
            $data = $this->getParams();
            $params['page'] = $data['page'] ?? 1;
            $sort = 'created_at';
            $lists = $model->getAppVideo($params,$sort);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function sort(){
        try {
            $setting = settings();
            $model = $this->getVideoModel($setting['video_resource']);
            $data = $this->getParams();
            $params['page'] = $data['page'] ?? 1;
            $sort = 'view';
            $lists = $model->getAppVideo($params,$sort);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function find(){
        try {
            $setting = settings();
            $model = $this->getVideoModel($setting['video_resource']);
            $lists = $model->getAppFindVideo();
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function search(){
        try {
            $setting = settings();
            $model = $this->getVideoModel($setting['video_resource']);
            $data = $this->getParams();
            $params['page'] = $data['page'] ?? 1;
            $params['kwd'] = $data['kwd'] ?? '';
            $params['tid'] = $data['tid'] ?? 0;
            $params['cid'] = $data['cid'] ?? 0;
            $sort = 'created_at';
            $lists = $model->getAppVideo($params,$sort);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function info(){
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            if (empty($id)) return $this->writeJson(1, null, '参数非法');
            $setting = settings();
            $model = $this->getVideoModel($setting['video_resource']);
            $info = $model->getAppVideoInfo($id);
            $goods = $model->getAppVideoInfoGood();
            $buy = 0;
            if($this->userid){
                $userModel = Users::create();
                //查看用户VIP信息
                $user = $userModel->field('vip,vip_at')->get($this->userid);
                if($user['vip'] && $user['vip_at']){
                    $now = time();
                    if ($now > $user['vip_at']) {
                        $userModel->update(['vip' => '', 'vip_at' => 0], ['id' => $this->userid]);
                        $user['vip'] = '';
                    }
                    if($user['vip']){
                        $buy = 1;
                    }
                }
            }
            $info['buy'] = $buy;
            return $this->writeJson(0, encrypt_data(['info' => $info, 'goods' => $goods]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
