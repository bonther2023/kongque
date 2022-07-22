<?php

namespace App\Model;



use EasySwoole\RedisPool\RedisPool;

class Tags extends Base
{
    protected $tableName = 'tags';


    public function getList($fields = '*')
    {
        $lists = $this->field($fields)->all();
       return $lists;
    }

    public function getAppTag(){
        $cache = RedisPool::defer('redis');
        $classKey = 'vtag';
        $tag = $cache->get($classKey);
        if(!$tag){
            $tag = $this->field('id,name')->all();
            $cache->set($classKey,$tag,120);
        }
        return $tag;
    }


}
