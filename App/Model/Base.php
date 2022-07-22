<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class Base extends AbstractModel
{

    public function paginate($data, $page = 1, $perPage = 10)
    {
        $total = $this->lastQueryResult()->getTotalCount();
        return [
            'data' => $data,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            'current_page' => $page,
        ];
    }

}
