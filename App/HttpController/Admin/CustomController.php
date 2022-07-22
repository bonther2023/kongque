<?php

namespace App\HttpController\Admin;

use App\Model\Admins;
use App\Model\Customs;
use App\Model\Messages;

class CustomController extends AuthController
{
    public function logout(){
        try {
            $model = Admins::create();
            $user = $model->get(1);
            $user->update([
                'socket_id' => 0,
                'online' => 1,
            ]);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function custom()
    {
        try {
            $model = Admins::create();
            $fields = 'id,socket_id,online';
            $info = $model->field($fields)->get(1);
            return $this->writeJson(0, encrypt_data($info));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function user()
    {
        try {
            $model = Customs::create();
            //读取所有custom_id为当前客服ID的用户
            $userList = $model->where('platform', 'micao')
                ->order('created_at', 'DESC')
                ->all();
            $msgModel = Messages::create();
            foreach ($userList as $index=>&$item){
                //查看是否有消息记录
                $num = $msgModel->field('id')->where('platform', 'micao')
                    ->where('user_id',$item['user_id'])//用户ID
                    ->count();
                if($num){
                    $unread = $msgModel->field('id')->where('platform', 'micao')
                        ->where('user_id',$item['user_id'])//用户ID
                        ->where('is_read',Messages::IS_READ_1)
                        ->where('type',Messages::TYPE_1)
                        ->count();
                    $item['unread'] = $unread;
                }else{
                    unset($userList[$index]);
                }
            }
            $userList = array_values($userList);
            $settings = settings();
            $kefu = trim($settings['kefu_link'],'/');
            $ws = trim($settings['socket_link'],'/');
            return $this->writeJson(0,  encrypt_data(['userList' => $userList, 'ws' => $ws ,'kefu' => $kefu]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


}
