<?php

namespace App\Model;


use EasySwoole\Mysqli\QueryBuilder;

class AdminRoles extends Base
{
    protected $tableName = 'admin_roles';

    protected function getRulesAttr($value, $data)
    {
        return json_decode($value);
    }

    public function getList($params = [], $fields = ['*'], $limit = 10)
    {
        $data = $this->field($fields)
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('name LIKE "%'.$params['kwd'].'%" OR description LIKE "%'.$params['kwd'].'%"');
                }
            });
        $lists = $this->paginate($data, $params['page'], $limit);
        return $lists;
    }

    public function getAdminRoles(){
        return $lists = $this->field(['id', 'name'])->all();
    }


    public function adminRole($roleId = 0)
    {
        $info = $this->get($roleId);
        return $info['rules'];
    }


}
