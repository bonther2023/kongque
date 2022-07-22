<?php

namespace App\Model;


use EasySwoole\Mysqli\QueryBuilder;

class Trades extends Base
{
    protected $tableName = 'trades';

    const STATUS_1 = 1;//未结算
    const STATUS_2 = 2;//已结算

    const STATUS_TEXT = [
        self::STATUS_2 => '<span class="el-tag">已结算</span>',
        self::STATUS_1 => '<span class="el-tag el-tag--danger">未结算</span>',
    ];

    public function getList($params = [], $fields = '*', $limit = 10)
    {
        $lists = $this->field($fields)
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->order('date')
            ->order('money')
            ->all(function (QueryBuilder $query) use ($params) {
                if (isset($params['start']) && $params['start']) {
                    $query->where('date', $params['start'], '>=');
                }
                if (isset($params['username']) && $params['username']) {
                    $query->where('username', '%'.$params['username'].'%', 'like');
                }
                if (isset($params['status']) && $params['status']) {
                    $query->where('status', $params['status']);
                }
                if (isset($params['end']) && $params['end']) {
                    $query->where('date', $params['end'], '<=');
                }
                if (isset($params['userid']) && $params['userid']) {
                    $query->where('userid', $params['userid']);
                }
                if (isset($params['type']) && $params['type']) {
                    $query->where('type', $params['type']);
                }
            });
        $lists = $this->paginate($lists, $params['page'], $limit);
        state_to_text($lists['data'], [
            'status' => self::STATUS_TEXT,
        ]);
       return $lists;
    }


}
