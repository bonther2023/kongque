<?php

namespace App\Model;


use EasySwoole\Mysqli\QueryBuilder;

class RecordLogs extends Base
{
    protected $tableName = 'record_logs';

    public function getList($params = [], $fields = '*')
    {
        $lists = $this->field($fields)
            ->group('hour')
            ->order('hour')
            ->all(function (QueryBuilder $query) use ($params) {
                if (isset($params['date']) && $params['date']) {
                    $query->where('date', $params['date']);
                }
                if (isset($params['cid']) && $params['cid']) {
                    $query->where('canal_id', $params['cid']);
                }
            });
        return $lists;
    }


}
