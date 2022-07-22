<?php

namespace App\HttpController\App;

use App\HttpController\BaseController;
use App\Model\Ads;

class AdController extends BaseController
{


    public function ad()
    {
        try {
            $params = $this->getParams();
            $name = $params['name'] ?? '';
            $type = $params['type'] ?? 0;
            $ad = Ads::create()->getAppAd($name,$type);
            return $this->writeJson(0, encrypt_data($ad));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }



}
