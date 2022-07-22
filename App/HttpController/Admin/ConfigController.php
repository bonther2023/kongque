<?php

namespace App\HttpController\Admin;

use App\Model\Configs;
use App\Model\Pays;
use EasySwoole\RedisPool\RedisPool;

class ConfigController extends AuthController
{

    public function setting()
    {
        try {
            $lists = Configs::create()->getList();
            $pays  = Pays::create()->where('status',Pays::STATUS_1)->field('title,name,type,channel,is_code')->all();
            return $this->writeJson(0, encrypt_data(['configs' => $lists, 'pays' => $pays]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function update()
    {
        try {
            $data = $this->getParams();
            $model   = Configs::create();
            foreach ($data as $key => $value) {
                $info = $model->get(['config_key' => $key]);
                $info->update(['config_value' => $value]);
            }
            //永久缓存配置文件
            $redis = RedisPool::defer('redis');
            $redis->set('setting', $data);
            return $this->writeJson(0, null, '更新配置信息成功');
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function platform(){
        try {
            $platform = [
                ['name' => 'default','title' => '默认'],
                ['name' => 'shayu','title' => '鲨鱼'], //http://shayuzy2.com/
                ['name' => 'sebo','title' => '色播'],  //   https://www.ssdy99.com/
                ['name' => 'bili','title' => '哔哩'],  //   http://www.msnyxs1.com/
                ['name' => 'lebo','title' => '乐播'],  //   https://lebozy8.com/
                ['name' => 'ttang','title' => '博天堂'],    //   https://www.sanji10.com/
            ];
            return $this->writeJson(0, encrypt_data($platform));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
