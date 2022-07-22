<?php

namespace App\Model;


use EasySwoole\Mysqli\QueryBuilder;

class Records extends Base
{
    protected $tableName = 'records';


    public function getList($params = [], $fields = '*', $limit = 9)
    {
        $lists = $this->field($fields)->alias('r')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->join('canals as c','r.canal_id = c.id','LEFT')
            ->join('agents as a','r.agent_id = a.id','LEFT')
            ->order('r.date')
            ->order('r.s_install')
            ->all(function (QueryBuilder $query) use ($params) {
                if (isset($params['start']) && $params['start']) {
                    $query->where('r.date', $params['start'], '>=');
                }
                if (isset($params['end']) && $params['end']) {
                    $query->where('r.date', $params['end'], '<=');
                }
                if (isset($params['cid']) && $params['cid']) {
                    $query->where('r.canal_id', $params['cid']);
                }
                if (isset($params['aid']) && $params['aid']) {
                    $query->where('r.agent_id', $params['aid']);
                }
            });
           $lists = $this->paginate($lists, $params['page'], $limit);
        foreach ($lists['data'] as &$item){
            if(isset($item['profit']) && isset($item['payable'])){
                $item['rechage'] = number_format($item['profit'] + $item['payable'],2);
            }
        }
        return $lists;
    }

    public function sumRecords($params){
        $lists = $this->field(['SUM(r.`s_install`) as s_installs',
            'SUM(r.`d_install`) as d_installs',
            'SUM(r.`settlement`) as settlements',
            'SUM(r.`deduct`) as deducts',
            'SUM(r.`money`) as moneys',
            'SUM(r.`rebate`) as rebates',
            'SUM(r.`payable`) as payables',
            'SUM(r.`profit`) as profits'])->alias('r')
            ->join('canals as c','r.canal_id = c.id','LEFT')
            ->join('agents as a','r.agent_id = a.id','LEFT')
            ->all(function (QueryBuilder $query) use ($params) {
                if ($params['start']) {
                    $query->where('r.date', $params['start'], '>=');
                }
                if ($params['end']) {
                    $query->where('r.date', $params['end'], '<=');
                }
                if ($params['cid']) {
                    $query->where('r.canal_id', $params['cid']);
                }
                if ($params['aid']) {
                    $query->where('r.agent_id', $params['aid']);
                }
            });
        foreach ($lists as &$item){
            $item['rechages'] = number_format($item['payables'] + $item['profits'],2);
        }
        return $lists[0];
    }


}
