<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Message\Status;
use EasySwoole\Utility\File;
use FastRoute\RouteCollector;
use EasySwoole\Http\Response;
use EasySwoole\Http\Request;

class Router extends AbstractRouter{

    protected $space;//空间
    protected $prx;//前缀
    protected $routes;

    public function initialize(RouteCollector $routeCollector){
//        $this->setGlobalMode(true);
        //开启全局模式拦截,局模式拦截下,路由将只匹配Router.php中的控制器方法响应,将不会执行框架的默认解析
//        $this->setMethodNotAllowCallBack(function (Request $request,Response $response){
//            $response->withStatus(Status::CODE_METHOD_NOT_ALLOWED);
//            return $response->write('Sorry,the method not allowed.');
//        });
        $this->setRouterNotFoundCallBack(function (Request $request,Response $response){
//            throw new \Exception('Sorry, the page you are looking for could not be found.');
            $response->withStatus(Status::CODE_NOT_FOUND);
            $response->write('    ');
            return false;
//            return 'index';//重定向到index路由
        });
        $this->routes = $routeCollector;
        $this->prx = '';
        $this->space = '';
        $this->loadFile();
    }

    /**
     * get
     * @param $uri
     * @param null $action
     * @return mixed
     */
    public function get($uri, $action = null){
        return $this->routes->addRoute('GET', $this->prefix($uri), $this->namespace($action));
    }

    /**
     * post
     * @param $uri
     * @param null $action
     * @return mixed
     */
    public function post($uri, $action = null){
        return $this->routes->addRoute('POST', $this->prefix($uri), $this->namespace($action));
    }

    /**
     * delete
     * @param $uri
     * @param null $action
     * @return mixed
     */
    public function delete($uri, $action = null)
    {
        return $this->routes->addRoute('DELETE', $this->prefix($uri),$this->namespace($action));
    }

    /**
     * any
     * @param $uri
     * @param null $action
     * @return mixed
     */
    public function any($uri, $action = null){
        return $this->routes->addRoute(['GET', 'POST'], $this->prefix($uri),$this->namespace($action));
    }

    /**
     * * 设置路由分组，一个路由文件只能使用一次
     * @param array $attributes 只包含prefix和namespace两个值
     * @param callable $callback 闭包回调
     */
    public function group(array $attributes, callable $callback){
        $previousPrefix = $this->prx;
        $previousSpace = $this->space;
        if(isset($attributes['prefix'])){
            $this->prx = trim($previousPrefix, '/').'/' . trim($attributes['prefix'], '/');
        }
        if(isset($attributes['namespace'])){
            $this->space = trim($previousSpace, '/').'/' . trim($attributes['namespace'], '/');
        }
        $callback($this);
        $this->prx = $previousPrefix;
        $this->space = $previousSpace;
    }

    /**
     * 获取路由前缀
     * @param $uri
     * @return mixed
     */
    protected function prefix($uri){
        return substr_replace(trim(trim($this->prx, '/').'/'.trim($uri, '/'),'/'),'/',0,0);
    }

    /**
     * 获取路由对应控制器空间
     * @param $action
     * @return mixed
     */
    protected function namespace($action){
        return substr_replace(trim(trim($this->space, '/').'/'.trim($action, '/'),'/'),'/',0,0);
    }

    /**
     * 自动加载路由文件
     */
    protected function loadFile(){
        $files = File::scanDirectory(EASYSWOOLE_ROOT . '/Router');
        if (is_array($files)) {
            foreach ($files['files'] as $file) {
                require $file;
            }
        }
    }
}
