<?php

namespace App\HttpController\Admin;

use App\Model\Records;

class FlowController extends AuthController
{


    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = (int)$params['page'] ?? 1;
            $params['start'] = $params['start'] ?? '';
            $params['end'] = $params['end'] ?? '';
            $params['aid'] = (int)$params['aid'] ?? 0;
            $params['cid'] = (int)$params['cid'] ?? 0;
            $model = Records::create();
            $fields = 'r.id,r.date,r.canal_id,r.s_install,r.d_install,r.settlement,r.money,r.deduct,r.profit,
            r.payable,r.rebate,c.username as canal_username,a.username as agent_username';
            $lists = $model->getList($params, $fields, 20);
            $totals = $model->sumRecords($params);
            return $this->writeJson(0, encrypt_data(['lists' => $lists, 'totals' => $totals]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }




}
