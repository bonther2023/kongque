<?php

namespace App\HttpController\Admin;

use App\Model\AdminRoles;
use Carbon\Carbon;
use EasySwoole\Validate\Validate;

class RoleController extends AuthController{

    public function index(){
        return $this->render('admin.manager.role');
    }


    public function list(){
        try {
            $request = $this->request();
            $params = [
                'page' => (int)trim($request->getRequestParam('page')) ?: 1,
                'kwd' => (string)trim($request->getRequestParam('kwd')) ?: '',
            ];
            $model = AdminRoles::create();
            $fields = 'id,name,description,rules,created_at,updated_at';
            $lists = $model->getList($params,$fields);
            return $this->writeJson(0,$lists);
        } catch (\Throwable $e) {
            return $this->writeJson(1, null, $e->getMessage());
        }
    }


    public function create(){
        return $this->render('admin.manager.role_update');
    }

    public function edit(){
        try {
            $request = $this->request();
            $id = (int)trim($request->getRequestParam('id')) ?: 0;
            $model = AdminRoles::create();
            $info = $model->get($id);
//            return $this->writeJson(1, $info);
            if(empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info = json($info);
            return $this->render('admin.manager.role_update',compact('info','id'));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function update(){
        try {
            $request = $this->request();
            $data = $request->getRequestParam();
            $validate = new Validate();
            $validate->addColumn('name')->required('请填写角色名称');
            if(!$this->validate($validate)){
                return $this->writeJson(1, null, $validate->getError()->__toString());
            }
            $model = AdminRoles::create();
            $data['updated_at'] = Carbon::now();
            $data['rules'] = json_encode($data['rules'] ?? []);
            if($data['id']){
                //编辑
                $model->update($data, ['id' => $data['id']]);
                return $this->writeJson(0,null,'编辑角色信息成功');
            }else{
                //新增
                $data['created_at'] = Carbon::now();
                //data($data, $setter = true)  第二个参数 可以决定是否要调用修改器
                $model->data($data,false)->save();
                return $this->writeJson(0,null,'新增角色信息成功');
            }
        } catch (\Throwable $e) {
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

    public function destroy(){
        try {
            $request = $this->request();
            $id = (int)trim($request->getRequestParam('id')) ?: 0;
            $model = AdminRoles::create();
            $info = $model->get($id);
            if(empty($info)) return $this->writeJson(0,null,'抱歉，你要操作的信息不存在');
            $info->destroy($id);
            return $this->writeJson(0);
        } catch (\Throwable $e) {
            return $this->writeJson(1, null, $e->getMessage());
        }
    }

}
