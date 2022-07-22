<?php

namespace App\HttpController;


use ank\IpLookup;
use App\Model\VideoDefaults;
use Carbon\Carbon;
use EasySwoole\RedisPool\RedisPool;

class IndexController extends BaseController {

    public function index(){
//        $model = VideoDefaults::create();
//        $lists = $model->all();
//        foreach ($lists as $item){
//            $date = Carbon::now()->subDays(mt_rand(0, 30))->toDateString();
//            $time = Carbon::parse($date)->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 60));
//            $model->update(['date' => $date,'created_at' => $time,'view' => rand(500,60000)],['id' => $item['id']]);
//        }
    }

}
