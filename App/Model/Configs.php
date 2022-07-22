<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class Configs extends AbstractModel
{
    protected $tableName = 'configs';


    public function getList()
    {
        $lists = $this->all();
        $data = [];
        foreach ($lists as $item){
            $data[$item['config_key']] = $item['config_value'];
        }
        return $data;
    }


}
