<?php

namespace App\Model;


use EasySwoole\RedisPool\RedisPool;

class Categorys extends Base
{
    protected $tableName = 'categorys';

    const KIND_1 = 1;
    const KIND_2 = 2;
    const KIND_3 = 3;
    const KIND_4 = 4;
    const KIND_5 = 5;

    const KIND_TEXT = [
        self::KIND_1 => '<span class="el-tag">视频</span>',
        self::KIND_2 => '<span class="el-tag">专题</span>',
        self::KIND_3 => '<span class="el-tag">楼凤</span>',
        self::KIND_4 => '<span class="el-tag">小说</span>',
        self::KIND_5 => '<span class="el-tag">图片</span>',
    ];

    public function getList($params = [], $fields = '*', $limit = 7)
    {
        $data = $this->field($fields)->order('id')
        ->limit(($params['page'] - 1) * $limit, $limit)
        ->withTotalCount()
        ->all();
        $lists = $this->paginate($data, $params['page'], $limit);
        state_to_text($lists['data'], [
            'kind' => self::KIND_TEXT,
        ]);
        return $lists;
    }

    public function getCate($kind = 1){
        return $this->field('id,title')->where('kind',$kind)->all();
    }

    public function getAppCategory(){
        $cache = RedisPool::defer('redis');
        $classKey = 'category';
        $category = $cache->get($classKey);
        if(!$category){
            $category = $this->field('id,title,icon,kind,note')->order('sort','asc')->all();
            $cache->set($classKey,$category,120);
        }
        return $category;
    }


}
