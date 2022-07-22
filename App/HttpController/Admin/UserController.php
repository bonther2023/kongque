<?php

namespace App\HttpController\Admin;

use App\Model\Users;
use Carbon\Carbon;

class UserController extends AuthController
{


    public function list()
    {
        try {
            $params = $this->getParams();
            $params['page'] = $params['page'] ?? 1;
            $params['cid'] = $params['cid'] ?? 0;
            $params['date'] = $params['date'] ?? '';
            $params['mobile'] = $params['mobile'] ?? '';
            $params['id'] = $params['id'] ?? 0;
            $params['username'] = $params['username'] ?? '';
            $params['system'] = $params['system'] ?? '';
            $model = Users::create();
            $fields = 'u.id,u.username,u.login_at,u.mobile,u.created_at,u.uuid,u.app_network,u.app_release,u.ip_address,u.app_vendor,
                u.app_system,u.app_version,u.app_model,u.ip,u.ip_address,c.username as canal_name,u.vip_at,u.vip,a.username as agent_name';
            $lists = $model->getList($params, $fields);
            return $this->writeJson(0, encrypt_data($lists));
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function update()
    {
        try {
            $data = $this->getParams();
            $model = Users::create();
            $user = $model->get($data['id']);
            if (empty($user)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $user->update(['vip' => $data['vip'], 'vip_at' => strtotime($data['vip_at'])]);
            return $this->writeJson(0,null,'更新用户信息成功');
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    protected function getVipAt($vip){
        $oldVip = time();
        switch ($vip) {
            case 'day_vip':
                $vipAt = (int)$oldVip + 86400;
                break;
            case 'month_vip':
                $vipAt = (int)$oldVip + 30 * 86400;
                break;
            case 'quarter_vip':
                $vipAt = (int)$oldVip + 180 * 86400;
                break;
            case 'year_vip':
                $vipAt = (int)$oldVip + 365 * 86400;
                break;
            case 'forever_vip':
                $vipAt = (int)$oldVip + 3 * 365 * 86400;
                break;
            default:
                $vipAt = 0;
                break;
        }
        return $vipAt;
    }

    public function destroy()
    {
        try {
            $data = $this->getParams();
            $id = $data['id'] ?? 0;
            $model = Users::create();
            $info = $model->get($id);
            if (empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->destroy();
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            write_log($e->getMessage());
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
