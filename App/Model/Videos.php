<?php

namespace App\Model;

use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;

class Videos extends Base
{
    protected $tableName = 'videos';


    public function getList($page = 1, $fields = '*', $limit = 500)
    {
        $data = $this->field($fields)->order('id','desc')
            ->limit(($page - 1) * $limit, $limit)
            ->withTotalCount()
            ->all();
        $lists = $this->paginate($data, $page, $limit);
        return $lists;
    }



}
