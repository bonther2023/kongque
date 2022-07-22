<?php

use \App\HttpController\Router;

Router::group(['namespace' => 'Agent', 'prefix' => 'agent'], function () {
    //登录
    Router::post('login', 'LoginController/login');//登录
    Router::post('authorize', 'LoginController/authorize');//授权登录
    Router::get('captcha', 'LoginController/captcha');//验证码

    Router::get('main', 'IndexController/index');//首页
    Router::get('detail', 'IndexController/detail');//基本信息
    Router::post('update', 'IndexController/update');//修改基本信息
    Router::get('config', 'IndexController/config');//配置
    Router::post('password', 'IndexController/password');//修改密码
    Router::get('flow', 'IndexController/flow');//效果报表
    Router::get('trade', 'IndexController/trade');//效果报表
    Router::get('report', 'IndexController/report');//实时报表

    //渠道
    Router::group(['prefix' => 'canal'], function () {
        Router::get('', 'CanalController/index');//代理下渠道搜索列表
        Router::get('list', 'CanalController/list');//代理下渠道列表
        Router::post('update', 'CanalController/update');//更新
        Router::post('lock', 'CanalController/lock');//锁定
        Router::post('active', 'CanalController/active');//启用
    });
});
