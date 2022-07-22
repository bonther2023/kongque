<?php

namespace App\HttpController\App;

use App\HttpController\BaseController;
use App\Model\Categorys;
use App\Model\Tags;

class CategoryController extends BaseController
{

    public function category()
    {
        try {
            $category = Categorys::create()->getAppCategory();
            return $this->writeJson(0, encrypt_data($category));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function tag()
    {
        try {
            $tag = Tags::create()->getAppTag();
            return $this->writeJson(0, encrypt_data($tag));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
