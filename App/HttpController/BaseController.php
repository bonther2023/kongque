<?php

namespace App\HttpController;

use App\Model\VideoBilis;
use App\Model\VideoDefaults;
use App\Model\VideoLebos;
use App\Model\VideoSebos;
use App\Model\VideoShayus;
use App\Model\VideoTtangs;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Http\AbstractInterface\Controller;

abstract class BaseController extends Controller
{

    /**
     * 不要打开不网站
     */
    public function index()
    {
        $this->actionNotFound('index');
    }

    /**
     * 重置writeJson 方法
     * @param int $statusCode 0成功 1失败
     * @param null $result 结果
     * @param null $msg 消息提示
     * @return bool
     */
    protected function writeJson($statusCode = 0, $result = null, $msg = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = [
                "code" => $statusCode,
                "data" => $result,
                "msg" => $msg ?? 'SUCCESS'
            ];
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus(200);
            return true;
        } else {
            return false;
        }
    }

    protected function writeEcho($msg){
        if(!$this->response()->isEndResponse()){
            $this->response()->write($msg);
            $this->response()->withHeader('Content-type','application/json;charset=utf-8');
            $this->response()->withStatus(200);
            return true;
        }else{
            return false;
        }
    }

    protected function writeHtml($msg)
    {
        if (!$this->response()->isEndResponse()) {
            $this->response()->write($msg);
//            $this->response()->withHeader('Content-type','charset=utf-8');
            $this->response()->withStatus(200);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取IP
     * @return string
     */
    protected function getIp()
    {
        $ip = $this->request()->getHeaders();
        $str = $ip['x-forwarded-for'][0];
        if(strpos($str,',') > 25){
            $ip = trim(trim(substr($str,strpos($str,',')),','));
        }else{
            $ip = substr($str,0,strpos($str,','));
        }
        return  $ip;
    }

    /**
     * 判断请求是否事ajax请求
     * @return bool
     */
    protected function ajax()
    {
        $requested = $this->request()->getHeader('x-requested-with');
        if (head($requested) == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    protected function getParams(){
        $request = $this->request();
        $params = $request->getRequestParam('params');
        $data = decrypt_data($params);
        return $data;
    }

    protected function getVideoModel($source = ''){
        switch ($source){
            case 'bili':
                $model = VideoBilis::create();
                break;
            case 'lebo':
                $model = VideoLebos::create();
                break;
            case 'sebo':
                $model = VideoSebos::create();
                break;
            case 'ttang':
                $model = VideoTtangs::create();
                break;
            case 'shayu':
                $model = VideoShayus::create();
                break;
            default:
                $model = VideoDefaults::create();
                break;
        }
        return $model;
    }

}
