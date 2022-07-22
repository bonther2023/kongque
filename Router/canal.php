<?php

use \App\HttpController\Router;

Router::group(['namespace' => 'Canal', 'prefix' => 'canal'], function () {
    //登录
    Router::post('login', 'LoginController/login');//登录
    Router::post('authorize', 'LoginController/authorize');//授权登录
    Router::get('captcha', 'LoginController/captcha');//验证码
    Router::get('xvhr', 'LoginController/xvhr');//验证码


    Router::get('main', 'IndexController/index');//首页
    Router::get('detail', 'IndexController/detail');//基本信息
    Router::get('config', 'IndexController/config');//配置
    Router::post('update', 'IndexController/update');//修改基本信息
    Router::post('password', 'IndexController/password');//修改密码
    Router::get('sort', 'IndexController/sort');//生成推广链接
    Router::get('flow', 'IndexController/flow');//效果报表
    Router::get('trade', 'IndexController/trade');//效果报表
    Router::get('report', 'IndexController/report');//实时报表
    Router::get('link', 'IndexController/link');//生成推广链接
});

