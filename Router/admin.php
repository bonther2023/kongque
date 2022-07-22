<?php

use \App\HttpController\Router;

Router::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {
    Router::post('login', 'LoginController/login');//登录
    Router::get('captcha', 'LoginController/captcha');//验证码

    Router::get('socket', 'IndexController/socket');//sokect
    Router::get('online', 'IndexController/online');//online
    Router::get('monitor', 'IndexController/monitor');//monitor
    Router::get('count', 'IndexController/count');//online

    Router::group(['prefix' => 'handle'], function () {
        Router::post('target', 'HandleController/target');//更新视频连接
        Router::post('thumb', 'HandleController/thumb');//更新视频封面
        Router::post('image', 'HandleController/image');//更新图片地址
    });

    //客服
    Router::group(['prefix' => 'custom'], function () {
        Router::get('', 'CustomController/custom');//客服
        Router::get('user', 'CustomController/user');//客服用户
        Router::get('logout', 'CustomController/logout');//客服退出
    });

    //管理员
    Router::group(['prefix' => 'manager'], function () {
        Router::get('list', 'ManagerController/list');//列表
        Router::post('update', 'ManagerController/update');//更新
        Router::post('lock', 'ManagerController/lock');//锁定
        Router::post('active', 'ManagerController/active');//激活
        Router::post('destroy', 'ManagerController/destroy');//删除
    });

    //代理
    Router::group(['prefix' => 'agent'], function () {
        Router::get('list', 'AgentController/list');//列表
        Router::get('domain', 'AgentController/domain');//代理域名地址
        Router::get('select', 'AgentController/select');//代理
        Router::post('update', 'AgentController/update');//更新
        Router::post('lock', 'AgentController/lock');//锁定
        Router::post('active', 'AgentController/active');//激活
        Router::post('destroy', 'AgentController/destroy');//删除
        Router::get('login', 'AgentController/login');//登录
        Router::post('trade', 'AgentController/trade');//结算
    });

    //渠道
    Router::group(['prefix' => 'canal'], function () {
        Router::get('list', 'CanalController/list');//列表
        Router::get('domain', 'CanalController/domain');//渠道域名地址
        Router::get('select', 'CanalController/select');//渠道
        Router::get('agent', 'CanalController/agent');//代理
        Router::post('update', 'CanalController/update');//更新
        Router::post('lock', 'CanalController/lock');//锁定
        Router::post('active', 'CanalController/active');//激活
        Router::post('destroy', 'CanalController/destroy');//删除
        Router::get('login', 'CanalController/login');//登录
        Router::post('trade', 'CanalController/trade');//结算
    });

    //支付
    Router::group(['prefix' => 'pay'], function () {
        Router::get('list', 'PayController/list');//列表
        Router::get('select', 'PayController/select');//支付
        Router::post('update', 'PayController/update');//更新
        Router::post('lock', 'PayController/lock');//锁定
        Router::post('active', 'PayController/active');//激活
        Router::post('destroy', 'PayController/destroy');//删除
        Router::get('success', 'PayController/paySuccess');//成功率
    });

    //设置
    Router::group(['prefix' => 'setting'], function () {
        Router::get('', 'ConfigController/setting');//配置
        Router::post('update', 'ConfigController/update');//更新配置
        Router::get('platform', 'ConfigController/platform');//视频平台
    });

    //广告
    Router::group(['prefix' => 'ad'], function () {
        Router::get('list', 'AdController/list');//列表
        Router::post('update', 'AdController/update');//更新
        Router::post('destroy', 'AdController/destroy');//删除
    });

    //图片
    Router::group(['prefix' => 'photo'], function () {
        Router::get('category', 'PhotoController/category');//分类
        Router::get('list', 'PhotoController/list');//列表
        Router::post('update', 'PhotoController/update');//更新
        Router::post('destroy', 'PhotoController/destroy');//删除
    });

    //小说
    Router::group(['prefix' => 'novel'], function () {
        Router::get('category', 'NovelController/category');//分类
        Router::get('list', 'NovelController/list');//列表
        Router::post('update', 'NovelController/update');//更新
        Router::post('destroy', 'NovelController/destroy');//删除
    });

    //小姐
    Router::group(['prefix' => 'harlot'], function () {
        Router::get('category', 'HarlotController/category');//分类
        Router::get('list', 'HarlotController/list');//列表
        Router::post('update', 'HarlotController/update');//更新
        Router::post('destroy', 'HarlotController/destroy');//删除
    });

    //类目
    Router::group(['prefix' => 'category'], function () {
        Router::get('list', 'CategoryController/list');//列表
        Router::post('update', 'CategoryController/update');//更新
        Router::post('destroy', 'CategoryController/destroy');//删除
    });

    //标签
    Router::group(['prefix' => 'tag'], function () {
        Router::get('list', 'TagController/list');//列表
        Router::post('update', 'TagController/update');//更新
        Router::post('destroy', 'TagController/destroy');//删除
    });

    //视频
    Router::group(['prefix' => 'video'], function () {
        Router::get('list', 'VideoController/list');//列表
        Router::get('info', 'VideoController/info');//详情
        Router::get('category', 'VideoController/category');//类目和专题
        Router::post('update', 'VideoController/update');//更新
        Router::post('lock', 'VideoController/lock');//锁定
        Router::post('active', 'VideoController/active');//激活
        Router::post('destroy', 'VideoController/destroy');//删除
    });

    //订单
    Router::group(['prefix' => 'order'], function () {
        Router::get('list', 'OrderController/list');//列表
        Router::post('update', 'OrderController/update');//补单
    });

    //流量趋势
    Router::group(['prefix' => 'flow'], function () {
        Router::get('list', 'FlowController/list');//列表
    });

    //报表统计
    Router::group(['prefix' => 'report'], function () {
        Router::get('list', 'ReportController/list');//列表
        Router::get('compare', 'ReportController/compare');//对比（最近一周）
    });

    //结算
    Router::group(['prefix' => 'trade'], function () {
        Router::get('list', 'TradeController/list');//列表
        Router::post('update', 'TradeController/update');//更新金额
        Router::post('status', 'TradeController/status');//更新结算状态
    });

    //用户
    Router::group(['prefix' => 'user'], function () {
        Router::get('list', 'UserController/list');//列表
        Router::post('update', 'UserController/update');//更新
        Router::post('destroy', 'UserController/destroy');//删除
    });


});
