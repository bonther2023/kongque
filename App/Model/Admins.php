<?php

namespace App\Model;

use EasySwoole\Mysqli\QueryBuilder;

class Admins extends Base
{
    protected $tableName = 'admins';//数据表名称

    const STATUS_1 = 1;
    const STATUS_2 = 2;

    const STATUS_TEXT = [
        self::STATUS_1 => '<span class="el-tag">正常</span>',
        self::STATUS_2 => '<span class="el-tag el-tag--danger">锁定</span>',
    ];

    public function getList($params = [], $fields = '*', $limit = 10)
    {
        $data = $this->alias('a')->field($fields)
//            ->join('admin_roles as r','a.role_id = r.id','LEFT')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('username LIKE "%'.$params['kwd'].'%" OR id = '.$params['kwd']);
                }
                if(isset($params['status']) && $params['status']) {
                    $query->where('status', $params['status']);
                }
            });
        $lists = $this->paginate($data, $params['page'], $limit);
        state_to_text($lists['data'], [
            'status' => self::STATUS_TEXT,
        ]);
        return $lists;
    }

}