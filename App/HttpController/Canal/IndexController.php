<?php

namespace App\HttpController\Canal;

use App\Model\Canals;
use App\Model\Records;
use App\Model\Trades;
use Carbon\Carbon;
use EasySwoole\Utility\Hash;

class IndexController extends AuthController {

    public function flow(){
        try {
            $data = $this->getParams();
            $params = [
                'page' => $data['page'] ?? 1,
                'start' => $data['start'] ?? '',
                'end' => $data['end'] ?? '',
                'cid' => $this->userid,
            ];
            $model = Records::create();
            $fields = 'r.date,r.s_install,r.rebate';
            $lists = $model->getList($params, $fields, 10);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function trade(){
        try {
            $data = $this->getParams();
            $params = [
                'page' => $data['page'] ?? 1,
                'start' => $data['start'] ?? '',
                'end' => $data['end'] ?? '',
                'userid' => $this->userid,
                'type' => 'canal',
            ];
            $model = Trades::create();
            $fields = 'date,money,status';
            $lists = $model->getList($params, $fields, 10);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function report()
    {
        try {
            $date = Carbon::now()->toDateString();
            $yesterday = Carbon::now()->subDays()->toDateString();
            $model = Records::create();
            //订单佣金
            $nowRebate = $model->where('canal_id',$this->userid)->where('date',$date)->sum('rebate');
            $yesRebate = $model->where('canal_id',$this->userid)->where('date',$yesterday)->sum('rebate');

            //订单效果
            $nowInstall = $model->where('canal_id',$this->userid)->where('date',$date)->sum('s_install');
            $yesInstall = $model->where('canal_id',$this->userid)->where('date',$yesterday)->sum('s_install');

            return $this->writeJson(0, encrypt_data([
                'nowRebate' => $nowRebate,
                'yesRebate' => $yesRebate,
                'nowInstall' => $nowInstall,
                'yesInstall' =>$yesInstall ,
            ]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }

    }



    public function detail()
    {
        try {
            $model = Canals::create();
            $info = $model->field('id,username,nickname,status,name,qq,bank,card,balance,apk_name,canal_rebate')
                ->get($this->userid);
            $info['password'] = '';
            return $this->writeJson(0, encrypt_data($info));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function config()
    {
        try {
            $setting = settings();
            $webLink = trim($setting['web_link'],'/');
            $apkLink = trim($setting['apk_link'],'/').'/';
            return $this->writeJson(0, encrypt_data(['web' => $webLink, 'apk' => $apkLink]));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function update()
    {
        try {
            $data = $this->getParams();
            $model = Canals::create();
            $info = $model->get($this->userid);
            $info->update($data);
            return $this->writeJson(0, null, '更新成功');
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function password()
    {
        try {
            $data = $this->getParams();
            $model = Canals::create();
            $info = $model->get($this->userid);
            if(!$data['new_password']){
                return $this->writeJson(1, null, '请输入新密码');
            }
            if($data['old_password'] == $data['new_password']){
                return $this->writeJson(1, null, '新旧密码输入一致');
            }
            if(Hash::validatePasswordHash($data['old_password'], $info['password']) == false){
                return $this->writeJson(1, null, '旧密码输入错误');
            }
            $info->update(['password' => Hash::makePasswordHash($data['new_password'])]);
            return $this->writeJson(0, null, '更新成功');
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function sort(){
        try {
            $params =  base64_encode('/kong/thbg/mang/index.html?qdid='.$this->userid);
            $setting = settings();
            $target = trim($setting['web_link'],'/');
            $domain = $target."/t.php?auth=".$params;
            return $this->writeJson(0, encrypt_data($domain));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function link(){
        try {
            $setting = settings();
            $target = trim($setting['web_link'],'/');
            $domain = [
                $target."/t.php?auth=".base64_encode('/kong/thbg/mang/index.html?qdid='.$this->userid),
                $target."/t.php?auth=".base64_encode('/kong/thbg/huds/index.html?qdid='.$this->userid),
                $target."/t.php?auth=".base64_encode('/kong/thbg/leng/index.html?qdid='.$this->userid),
            ];
            return $this->writeJson(0, encrypt_data($domain));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }





}
