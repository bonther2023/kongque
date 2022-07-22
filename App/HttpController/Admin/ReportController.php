<?php

namespace App\HttpController\Admin;

use App\Model\RecordLogs;
use App\Utility\RedisClient;
use Carbon\Carbon;

class ReportController extends AuthController
{

    public function list()
    {
        try {
            $params = $this->getParams();
            $params['date'] = $params['date'] ?? '';
            $params['cid'] = (int)$params['cid'] ?? 0;
            $model = RecordLogs::create();
            $fields = 'SUM(`install`) as installs,SUM(`orders`) as orders,SUM(`order_pays`) as pays,date,hour';
            $lists = $model->getList($params, $fields);
            $statis = [];
            $installs = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $orders = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $pays = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            foreach ($lists as $list) {
                for ($i = 0; $i < 24; $i++) {
                    if ($i == $list['hour']) {
                        $installs[$i] = $list['installs'];
                        $orders[$i] = $list['orders'];
                        $pays[$i] = $list['pays'];
                    }
                }
            }
            $statis['install'] = $installs;
            $statis['order'] = $orders;
            $statis['pay'] = $pays;
            return $this->writeJson(0, encrypt_data(['lists' => $lists, 'statis' => $statis]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function compare(){
        try {
            $params = $this->getParams();
            $params['cid'] = (int)$params['cid'] ?? 0;
            $params['date'] = Carbon::now()->format('Y-m-d');
            $model = RecordLogs::create();
            $fields = 'SUM(`install`) as installs,SUM(`order_pays`) as pays,date,hour';
            $data = [];
            $date = [];
            for($i = 0;$i < 7; $i++){
                $d = Carbon::now()->subDays($i)->format('Y-m-d');
                $params['date'] = $d;
                $date[] = $d;
                $data[] = $model->getList($params, $fields);
            }
            $_data = [];
            foreach ($data as $key => $item) {
                foreach ($item as $k => $val){
                    for ($i = 0; $i < 24; $i++) {
                        if ($i == $val['hour']) {
                            $_data[23-$i]['hour'] = $val['hour'];
                            $_data[23-$i][$val['date']] = $val['installs'].'【'.$val['pays'].'】';
                            break;
                        }
                    }
                }
            }
            ksort($_data);
            return $this->writeJson(0, encrypt_data(['lists' => array_column($_data,null), 'dates' => $date]));//
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
