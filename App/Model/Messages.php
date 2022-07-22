<?php

namespace App\Model;


class Messages extends Base
{
    protected $tableName = 'messages';
    CONST TYPE_1 = 1;
    CONST TYPE_2 = 2;
    CONST TYPE_TEXT = [
        self::TYPE_1 => '用户发送客服',
        self::TYPE_2 => '客服发送用户',
    ];

    //该字段只针对客服有效
    CONST IS_READ_1 = 1;
    CONST IS_READ_2 = 2;
    CONST IS_READ_TEXT = [
        self::IS_READ_1 => '未读',
        self::IS_READ_2 => '已读',
    ];

}
