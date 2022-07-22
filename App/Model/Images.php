<?php

namespace App\Model;

use Carbon\Carbon;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class Images extends Base
{
    protected $tableName = 'images';

    protected function getCreatedAtAttr($value, $data)
    {
        return Carbon::parse($value)->format('Y-m-d H:i');
    }

    protected function getContentAttr($value, $data)
    {
        return unjson($value);
    }

    public function getList($params = [], $fields = '*', $limit = 10)
    {
        $data = $this->field($fields)->order('created_at','desc')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('title LIKE "%'.$params['kwd'].'%"');
                }
                if(isset($params['cid']) && $params['cid']) {
                    $query->where('cate_id', $params['cid']);
                }
            });
        $lists = $this->paginate($data, $params['page'], $limit);
        return $lists;
    }


    public function getAppNewPhoto()
    {
        $cache = RedisPool::defer('redis');
        $newKey = 'photo:new';
        $newNovel = $cache->get($newKey);
        if(!$newNovel){
            $newNovel = $this->alias('n')->field('n.id,n.title,c.title as ctitle,n.created_at')
                ->join('categorys as c','c.id = n.cate_id','LEFT')
                ->order('n.created_at')->limit(30)->all();
            $cache->set($newKey,$newNovel,120);
        }
        return $newNovel;
    }


    public function getAppPhotoInfo($id = 0){
        $cache = RedisPool::defer('redis');
        $key = 'photo:info:id_' . $id;
        $info = $cache->get($key);
        if(!$info){
            $info = $this->get($id);
            $cache->set($key,$info,120);
        }
        return $info;
    }


}
