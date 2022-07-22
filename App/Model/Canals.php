<?php

namespace App\Model;

use EasySwoole\Mysqli\QueryBuilder;

class Canals extends Base
{
    protected $tableName = 'canals';

    const STATUS_1 = 1;
    const STATUS_2 = 2;

    const STATUS_TEXT = [
        self::STATUS_1 => '<span class="el-tag">正常</span>',
        self::STATUS_2 => '<span class="el-tag el-tag--danger">锁定</span>',
    ];

    public function getList($params = [], $fields = '*', $limit = 10)
    {
        $data = $this->alias('c')->field($fields)
            ->order('c.created_at','desc')
            ->join('agents as a','c.agent_id = a.id','LEFT')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if (isset($params['id']) && $params['id']) {
                    $query->where('c.id', $params['id']);
                }
                if (isset($params['username']) && $params['username']) {
                    $query->where('c.username', $params['username']);
                }
                if(isset($params['status']) && $params['status']) {
                    $query->where('c.status', $params['status']);
                }
                if(isset($params['agent_id']) && $params['agent_id']) {
                    $query->where('c.agent_id', $params['agent_id']);
                }
            });
        $lists = $this->paginate($data, $params['page'], $limit);
        state_to_text($lists['data'], [
            'status' => self::STATUS_TEXT,
        ]);
        return $lists;
    }


    public function getCascaderList()
    {
        $lists = $this->field('id,username')->order('id')->all(['status' => self::STATUS_1]);
        return $lists;
    }

}
