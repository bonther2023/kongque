<?php

namespace App\Model;

use Carbon\Carbon;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class Harlots extends Base
{
    protected $tableName = 'harlots';

    const STATUS_1 = 1;
    const STATUS_2 = 2;

    const STATUS_TEXT = [
        self::STATUS_1 => '<span class="el-tag">正常</span>',
        self::STATUS_2 => '<span class="el-tag el-tag--danger">锁定</span>',
    ];

    protected function getCreatedAtAttr($value, $data)
    {
        return Carbon::parse($value)->format('Y-m-d H:i');
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
                if (isset($params['cid']) && $params['cid']) {
                    $query->where('cate_id', $params['cid']);
                }
                if (isset($params['pname']) && $params['pname']) {
                    $query->where('province LIKE "%'.$params['pname'].'%"');
                }
                if (isset($params['cname']) && $params['cname']) {
                    $query->where('city LIKE "%'.$params['cname'].'%"');
                }
                if (isset($params['rname']) && $params['rname']) {
                    $query->where('region LIKE "%'.$params['rname'].'%"');
                }
                if (isset($params['status']) && $params['status']) {
                    $query->where('status', $params['status']);
                }
            });
        $lists = $this->paginate($data, $params['page'], $limit);
        state_to_text($lists['data'], [
            'status' => self::STATUS_TEXT,
        ]);
        return $lists;
    }


    public function getAppNewHarlot()
    {
        $cache = RedisPool::defer('redis');
        $newKey = 'harlot:new';
        $newHarlot = $cache->get($newKey);
        if(!$newHarlot){
            $newHarlot = $this->alias('h')->field('h.id,h.title,c.title as ctitle,province,city,region')
                ->join('categorys as c','c.id = h.cate_id','LEFT')
                ->order('h.created_at')->limit(30)->all();
            $cache->set($newKey,$newHarlot,120);
        }
        return $newHarlot;
    }

    public function getAppHarlotInfo($id = 0){
        $cache = RedisPool::defer('redis');
        $key = 'harlot:info:id_' . $id;
        $info = $cache->get($key);
        if(!$info){
            $info = $this->get($id);
            $cache->set($key,$info,120);
        }
        return $info;
    }


}
