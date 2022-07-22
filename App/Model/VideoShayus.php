<?php

namespace App\Model;

use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class VideoShayus extends Base
{
    protected $tableName = 'video_shayus';

    const STATUS_1 = 1;
    const STATUS_2 = 2;
    const STATUS_3 = 3;

    const STATUS_TEXT = [
        self::STATUS_1 => '<span class="el-tag">正常</span>',
        self::STATUS_2 => '<span class="el-tag el-tag--danger">推荐</span>',
        self::STATUS_3 => '<span class="el-tag el-tag--info">锁定</span>',
    ];

    const THUMB_TYPE_1 = 1;//横图
    const THUMB_TYPE_2 = 2;//竖图

    const FREE_1 = 1;//正常
    const FREE_2 = 2;//限免

    public function getAppGoodVideo()
    {
        $cache = RedisPool::defer('redis');
        $goodKey = 'video:good_shayu';
        $videoGood = $cache->get($goodKey);
        if(!$videoGood){
            $video1 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 1)
                ->order('RAND()')->limit(5)->all();
            $video2 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 2)
                ->order('RAND()')->limit(5)->all();
            $video3 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 3)
                ->order('RAND()')->limit(5)->all();
            $video5 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 5)
                ->order('RAND()')->limit(5)->all();
            $video6 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 6)
                ->order('RAND()')->limit(5)->all();
            $video13 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 13)
                ->order('RAND()')->limit(5)->all();
            $video35 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 35)
                ->order('RAND()')->limit(5)->all();
            $video36 = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)
                ->where('category_id', 36)
                ->order('RAND()')->limit(5)->all();
            $videoGood = [$video1,$video36,$video3,$video13,$video2,$video5,$video6,$video35];
            $cache->set($goodKey,$videoGood,120);
        }
        return $videoGood;
    }

    public function getAppFindVideo(){
        $videoFind = $this->field('id,title,thumb,thumb_type,view,date,tag,quality')
            ->where('status', self::STATUS_3, '<')->order('RAND()')->limit(20)->all();
        return $videoFind;
    }

    public function getAppVideo($params, $sort = 'id', $limit = 20)
    {
        $data = $this->field('id,title,thumb,thumb_type,view,date,tag,quality')
            ->order($sort,'desc')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('title LIKE "%'.$params['kwd'].'%"');
                }
                if (isset($params['tid']) && $params['tid']) {
                    $query->where('topic_id', $params['tid']);
                }
                if (isset($params['cid']) && $params['cid']) {
                    $query->where('category_id', $params['cid']);
                }
                $query->where('status', self::STATUS_3, '<');
            });
        $lists = $this->paginate($data, $params['page'] , $limit);
        return $lists;
    }


    public function getAppVideoInfo($id){
        $cache = RedisPool::defer('redis');
        $key = 'video:info_shayu:id_'.$id;
        $info = $cache->get($key);
        if(!$info){
            $info = $this->get($id);
            $cache->set($key,$info,120);
        }
        $job = json([
            'id' => $info['id'],
            'resource' => 'shayu'
        ]);
        $cache->rPush('queue:update-video',$job);
        return $info;
    }



    public function getAppVideoInfoGood(){
        $cache = RedisPool::defer('redis');
        $key = 'video:info_good_shayu';
        $good = $cache->get($key);
        if(!$good){
            $good = $this->field('id,title,thumb,thumb_type,free,tag,quality')
                ->where('status', self::STATUS_2)->order('RAND()')->limit(10)->all();
            $cache->set($key,$good,120);
        }
        return $good;
    }

    public function getList($params = [], $fields = '*', $limit = 8)
    {
        $data = $this->field($fields)->order('id','desc')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('title LIKE "%'.$params['kwd'].'%"');
                }
                if (isset($params['status']) && $params['status']) {
                    $query->where('status', $params['status']);
                }
                if (isset($params['cid']) && $params['cid']) {
                    $query->where('category_id', $params['cid']);
                }
                if (isset($params['tid']) && $params['tid']) {
                    $query->where('topic_id', $params['tid']);
                }
                if (isset($params['free']) && $params['free']) {
                    $query->where('free', $params['free']);
                }
            });
        $lists = $this->paginate($data, $params['page'], $limit);
        state_to_text($lists['data'], [
            'status' => self::STATUS_TEXT,
        ]);
        return $lists;
    }


}
