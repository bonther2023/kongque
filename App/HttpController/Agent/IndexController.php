<?php

namespace App\HttpController\Agent;

use App\Model\Agents;
use App\Model\Canals;
use App\Model\Records;
use App\Model\Trades;
use Carbon\Carbon;
use EasySwoole\Utility\Hash;

class IndexController extends AuthController {

    public function config()
    {
        try {
            $setting = settings();
            $canalLink = trim($setting['canal_admin'],'/');
            return $this->writeJson(0, encrypt_data($canalLink));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function flow(){
        try {
            $data = $this->getParams();
            $params = [
                'page' => (int)$data['page'] ?? 1,
                'start' => (string)trim($data['start']) ?? '',
                'end' => (string)trim($data['end']) ?? '',
                'cid' => (int)trim($data['cid']) ?? 0,
                'aid' => $this->userid,
            ];
            $model = Records::create();
            $fields = 'r.date,r.s_install,r.money,c.username as canal_username,r.rebate';
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
                'type' => 'agent',
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
            $nowRebate = $model->where('agent_id',$this->userid)->where('date',$date)->sum('money');
            $yesRebate = $model->where('agent_id',$this->userid)->where('date',$yesterday)->sum('money');

            //订单效果
            $nowInstall = $model->where('agent_id',$this->userid)->where('date',$date)->sum('s_install');
            $yesInstall = $model->where('agent_id',$this->userid)->where('date',$yesterday)->sum('s_install');

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
            $model = Agents::create();
            $info = $model->field('id,username,nickname,status,name,qq,bank,card,balance,rebate')
                ->get($this->userid);
            $info['password'] = '';
            return $this->writeJson(0, encrypt_data($info));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }



    public function update()
    {
        try {
            $data = $this->getParams();
            $model = Agents::create();
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
            $model = Agents::create();
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



}
