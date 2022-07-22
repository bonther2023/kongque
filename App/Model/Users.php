<?php

namespace App\Model;

use EasySwoole\Mysqli\QueryBuilder;

class Users extends Base
{
    protected $tableName = 'users';


    public function getList($params = [], $fields = '*', $limit = 8)
    {
        $data = $this->alias('u')->field($fields)
            ->join('agents as a','u.agent_id = a.id','LEFT')
            ->join('canals as c','u.canal_id = c.id','LEFT')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->order('u.created_at')
            ->withTotalCount()
            ->all(function (QueryBuilder $query) use ($params) {
                if(isset($params['username']) && $params['username']) {
                    $query->where('u.username',$params['username']);
                }
                if(isset($params['id']) && $params['id']) {
                    $query->where('u.id',$params['id']);
                }
                if(isset($params['mobile']) && $params['mobile']) {
                    $query->where('u.mobile',$params['mobile']);
                }
                if(isset($params['system']) && $params['system']) {
                    $query->where('u.app_system', $params['system']);
                }
                if(isset($params['cid']) && $params['cid']) {
                    $query->where('u.canal_id', $params['cid']);
                }
                if(isset($params['date']) && $params['date']) {
                    $query->where('u.created_at', $params['date'].' 00:00:00', '>=')
                        ->where('u.created_at', $params['date'].' 23:59:59', '<=');
                }
            });
        foreach ($data as &$item){
            if($item['vip_at']){
                $item['vip_at'] = date('Y-m-d H:i:s',$item['vip_at']);
            }else{
                $item['vip_at'] = '';
            }
        }
        $lists = $this->paginate($data, $params['page'], $limit);
        return $lists;
    }

}
