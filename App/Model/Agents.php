<?php

namespace App\Model;

use EasySwoole\Mysqli\QueryBuilder;

class Agents extends Base
{
    protected $tableName = 'agents';

    const STATUS_1 = 1;
    const STATUS_2 = 2;

    const STATUS_TEXT = [
        self::STATUS_1 => '<span class="el-tag">正常</span>',
        self::STATUS_2 => '<span class="el-tag el-tag--danger">锁定</span>',
    ];

    public function getList($params = [], $fields = '*', $limit = 8)
    {
        $data = $this->field($fields)->order('created_at','desc')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if (isset($params['id']) && $params['id']) {
                    $query->where('id', $params['id']);
                }
                if (isset($params['username']) && $params['username']) {
                    $query->where('username', $params['username']);
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


    public function getCascaderList()
{
    $lists = $this->field('id,username')->order('id')->all(['status' => self::STATUS_1]);
    return $lists;
}

}
