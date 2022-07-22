<?php

namespace App\Model;

use EasySwoole\RedisPool\RedisPool;

class Ads extends Base
{
    protected $tableName = 'ads';

    protected function getThumbAttr($value, $data)
    {
        return unjson($value);
    }

    public function getList($params = [], $fields = '*', $limit = 10)
    {
        $data = $this->field($fields)->order('created_at','desc')
            ->limit(($params['page'] - 1) * $limit, $limit)
            ->withTotalCount()
            ->all();
        $lists = $this->paginate($data, $params['page'], $limit);
        return $lists;
    }


    public function getAppAd($name = '',$type = 0)
    {
        if(empty($name)){
            return null;
        }
        $cache = RedisPool::defer('redis');
        $adKey = 'ads:'.$name;
        $ads = $cache->get($adKey);
        if(!$ads){
            $ads = $this->field('thumb,width,height')->where('position',$name)->get()->toArray();
            $cache->set($adKey,$ads,120);
        }
        if($type){
            $ads['thumb'] = [$ads['thumb'][array_rand($ads['thumb'])]];
        }
        return $ads;
    }

}
