<?php

namespace App\Model;

use EasySwoole\Mysqli\QueryBuilder;

class Pays extends Base
{
    protected $tableName = 'pays';

    const TYPE_1 = 1;
    const TYPE_2 = 2;
    const TYPE_3 = 3;
    const TYPE_TEXT = [
        self::TYPE_1 => '<span>微信</span>',
        self::TYPE_2 => '<span>支付宝</span>',
        self::TYPE_3 => '<span>双端</span>',
    ];

    const CHANNEL_1 = 1;
    const CHANNEL_2 = 2;
    const CHANNEL_3 = 3;
    const CHANNEL_TEXT = [
        self::CHANNEL_1 => '<span>普通</span>',
        self::CHANNEL_2 => '<span>话费</span>',
        self::CHANNEL_3 => '<span>双通道</span>',
    ];

    const STATUS_1 = 1;
    const STATUS_2 = 2;

    const STATUS_TEXT = [
        self::STATUS_1 => '<span class="el-tag">正常</span>',
        self::STATUS_2 => '<span class="el-tag el-tag--danger">锁定</span>',
    ];

    const PAY_METHOD_1 = 1;
    const PAY_METHOD_2 = 2;
    const PAY_METHOD_3 = 3;
    const PAY_METHOD_TEXT = [
        self::PAY_METHOD_1 => '<span>POST</span>',
        self::PAY_METHOD_2 => '<span>GET</span>',
        self::PAY_METHOD_3 => '<span>PJSON</span>',
    ];


    const PAY_FORMAT_1 = 1;
    const PAY_FORMAT_2 = 2;
    const PAY_FORMAT_3 = 3;
    const PAY_FORMAT_TEXT = [
        self::PAY_FORMAT_1 => '<span>JSON</span>',
        self::PAY_FORMAT_2 => '<span>FORM</span>',
        self::PAY_FORMAT_3 => '<span>CURL</span>',
    ];


    public function getList($params = [], $fields = '*', $limit = 10)
    {
        $data = $this->field($fields)
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->order('status','asc')
            ->order('id')
            ->all(function (QueryBuilder $query) use ($params) {
                if(isset($params['kwd']) && $params['kwd']) {
                    $query->where('title LIKE "%'.$params['kwd'].'%"');
                }
                if(isset($params['status']) && $params['status']) {
                    $query->where('status', $params['status']);
                }
            });
        $lists = $this->paginate($data, $params['page'], $limit);
        state_to_text($lists['data'], [
            'type' => self::TYPE_TEXT,
            'channel' => self::CHANNEL_TEXT,
            'status' => self::STATUS_TEXT,
            'pay_format' => self::PAY_FORMAT_TEXT,
            'pay_method' => self::PAY_METHOD_TEXT,
        ]);
        return $lists;
    }

}
