<?php

namespace App\Model;

use Carbon\Carbon;
use EasySwoole\Mysqli\QueryBuilder;

class Orders extends Base
{
    protected $tableName = 'orders';

    const STATUS_0 = 0;//无效订单，用户点了生成订单但是没有确认订单
    const STATUS_1 = 1;//未支付
    const STATUS_2 = 2;//已支付

    const STATUS_TEXT = [
        self::STATUS_2 => '<span class="el-tag el-tag--danger">已付</span>',
        self::STATUS_1 => '<span class="el-tag">未付</span>',
    ];

    const PAYMENT_1 = 1;//微信
    const PAYMENT_2 = 2;//支付宝

    const PAYMENT_TEXT = [
        self::PAYMENT_1 => '<span class="el-tag">微</span>',
        self::PAYMENT_2 => '<span class="el-tag">支</span>',
    ];

    const DEDUCT_1 = 1;//结算
    const DEDUCT_2 = 2;//扣量

    const DEDUCT_TEXT = [
        self::DEDUCT_1 => '<span class="el-tag">结算</span>',
        self::DEDUCT_2 => '<span class="el-tag  el-tag--danger">扣量</span>',
    ];

    const IS_NEW_1 = 1;//新
    const IS_NEW_2 = 2;//老

    const CHANNEL_1 = 1;//普通
    const CHANNEL_2 = 2;//话费

    const CHANNEL_TEXT = [
        self::CHANNEL_1 => '<span class="el-tag">普通</span>',
        self::CHANNEL_2 => '<span class="el-tag  el-tag--danger">话费</span>',
    ];

    public function getList($params = [], $fields = '*', $limit = 9)
    {
        $moneys = $this->field('sum(money) as total')
            ->all(function (QueryBuilder $query) use ($params){
                if (isset($params['payment']) && $params['payment']) {
                    $query->where('payment', $params['payment']);
                }
                if (isset($params['platform']) && $params['platform']) {
                    $query->where('platform', $params['platform']);
                }
                if (isset($params['system']) && $params['system']) {
                    $query->where('system', $params['system']);
                }
                if (isset($params['status']) && $params['status']) {
                    $query->where('status', $params['status']);
                }
                if (isset($params['cid']) && $params['cid']) {
                    $query->where('canal_id', $params['cid']);
                }
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('(`order_id` = '.$params['kwd'].' OR `user_id` = '.$params['kwd'].')');
                }
                if(isset($params['name']) && $params['name']) {
                    $query->where('username',  '%'.$params['name'].'%','LIKE');
                }
                if (isset($params['start']) && $params['start']) {
                    $query->where('created_at', $params['start'].' 00:00:00', '>=');
                }
                if (isset($params['end']) && $params['end']) {
                    $query->where('created_at', $params['end'].' 23:59:59', '<=');
                }
        });

        $data = $this->field($fields)
            ->order('created_at')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()->all(function (QueryBuilder $query) use ($params){
                if (isset($params['payment']) && $params['payment']) {
                    $query->where('payment', $params['payment']);
                }
                if (isset($params['platform']) && $params['platform']) {
                    $query->where('platform', $params['platform']);
                }
                if (isset($params['system']) && $params['system']) {
                    $query->where('system', $params['system']);
                }
                if (isset($params['status']) && $params['status']) {
                    $query->where('status', $params['status']);
                }
                if (isset($params['cid']) && $params['cid']) {
                    $query->where('canal_id', $params['cid']);
                }
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('(`order_id` = '.$params['kwd'].' OR `user_id` = '.$params['kwd'].')');
                }
                if(isset($params['name']) && $params['name']) {
                    $query->where('username',  '%'.$params['name'].'%','LIKE');
                }
                if (isset($params['start']) && $params['start']) {
                    $query->where('created_at', $params['start'].' 00:00:00', '>=');
                }
                if (isset($params['end']) && $params['end']) {
                    $query->where('created_at', $params['end'].' 23:59:59', '<=');
                }
            });

        $lists = $this->paginate($data, $params['page'], $limit);
        state_to_text($lists['data'], [
            'status' => self::STATUS_TEXT,
            'deduct' => self::DEDUCT_TEXT,
        ]);
        return ['lists' => $lists,'total' => $moneys[0]['total']];
    }

}
