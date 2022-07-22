<?php

namespace App\HttpController\Admin;

use App\Model\Categorys;
use App\Model\Images;
use Carbon\Carbon;
use EasySwoole\Mysqli\QueryBuilder;

class HandleController extends AuthController
{

    public function target()
    {
        try {

            $data = $this->getParams();
            $model = $this->getVideoModel($data['source']);
            $res = $model->func(function ($builder) use($data){
                return $builder->raw("UPDATE `video_".$data['source']."s` SET `target` = replace(`target`, '".$data['url_old']."', '".$data['url_new']."')");
            });
            if($res){
                return $this->writeJson(0, $res, '批量操作成功');
            }else{
                return $this->writeJson(1, $res, '批量操作失败');
            }
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function thumb(){
        try {

            $data = $this->getParams();
            $model = $this->getVideoModel($data['source']);
            $res = $model->func(function ($builder) use($data){
                return $builder->raw("UPDATE `video_".$data['source']."s` SET `thumb` = replace(`thumb`, '".$data['url_old']."', '".$data['url_new']."')");
            });
            if($res){
                return $this->writeJson(0, $res, '批量操作成功');
            }else{
                return $this->writeJson(1, $res, '批量操作失败');
            }
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function image(){
        try {
            $data = $this->getParams();
            $model = Images::create();
            $res = $model->func(function ($builder) use($data){
                $builder->raw("UPDATE `images` SET `content` = replace(`content`, '".$data['url_old']."', '".$data['url_new']."')");
            });
            if($res){
                return $this->writeJson(0, $res, '批量操作成功');
            }else{
                return $this->writeJson(1, $res, '批量操作失败');
            }
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
