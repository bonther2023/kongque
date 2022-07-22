<?php

use \App\HttpController\Router;

Router::group(['namespace' => 'App', 'prefix' => 'app'], function () {
    Router::post('login', 'LoginController/login');//登录
    Router::get('ad', 'AdController/ad');//广告
    Router::get('config', 'ConfigController/config');//配置信息
    Router::get('vip', 'ConfigController/vip');//vip配置信息

    //用户
    Router::group(['prefix' => 'user'], function () {
        Router::get('', 'UserController/user');//用户信息
        Router::get('check', 'UserController/check');//检查更新
        Router::post('mobile', 'UserController/mobile');//绑定手机
        Router::post('account', 'UserController/account');//找回账户
        Router::get('custom', 'UserController/custom');//客服信息
    });

    Router::get('category', 'CategoryController/category'); //分类
    Router::get('tag', 'CategoryController/tag'); //标签

    //视频
    Router::group(['prefix' => 'video'], function () {
        Router::get('good', 'VideoController/good');//推荐
        Router::get('sort', 'VideoController/sort');//排序
        Router::get('new', 'VideoController/new');//最新
        Router::get('search', 'VideoController/search');//搜索
        Router::get('info', 'VideoController/info');//详情
        Router::get('find', 'VideoController/find');//发现
    });

    //小姐
    Router::group(['prefix' => 'harlot'], function () {
        Router::get('new', 'HarlotController/new');//最新
        Router::get('list', 'HarlotController/list');//列表
        Router::get('info', 'HarlotController/info');//详情
    });

    //小说
    Router::group(['prefix' => 'novel'], function () {
        Router::get('new', 'NovelController/new');//最新
        Router::get('list', 'NovelController/list');//列表
        Router::get('info', 'NovelController/info');//详情
    });

    //图片
    Router::group(['prefix' => 'photo'], function () {
        Router::get('new', 'PhotoController/new');//最新
        Router::get('list', 'PhotoController/list');//列表
        Router::get('info', 'PhotoController/info');//详情
    });

    //订单
    Router::group(['prefix' => 'order'], function () {
        Router::post('create', 'OrderController/create');//最新
        Router::get('check', 'OrderController/check');//列表
    });

    //直播
    Router::group(['prefix' => 'live'], function () {
        Router::get('platform', 'LiveController/platform');//平台
        Router::get('anchor', 'LiveController/anchor');//列表
        Router::get('test', 'LiveController/test');//列表
    });

    //客服
    Router::post('custom', 'CustomController/index');

    //支付
    Router::get('defray', 'PayController/pay');
    Router::get('test/pay', 'PayController/testPay');

    Router::get('page', 'NotifyController/page');//支付表单界面

    //回调
    Router::group(['prefix' => 'notify'], function () {
        Router::any('lizhi', 'NotifyController/lizhi');//荔枝微信
        Router::any('ergou', 'NotifyController/ergou');//金金微信
        Router::any('panshi', 'NotifyController/panshi');//磐石支付
        Router::any('xunbo', 'NotifyController/xunbo');//宽窄原生
        Router::any('xunbop', 'NotifyController/xunbop');
        Router::any('kuanzhai', 'NotifyController/kuanzhai');//宽窄话费
        Router::any('fuxi', 'NotifyController/fuxi');//伏羲支付
        Router::any('qinaguia', 'NotifyController/qinaguia');//钱柜支付
        Router::any('qinaguicode', 'NotifyController/qinaguicode');//钱柜扫码
        Router::any('taimei', 'NotifyController/taimei');//台妹支付
        Router::any('weipay', 'NotifyController/weipay');//小微话费
        Router::any('rongyi', 'NotifyController/rongyi');//融易支付
        Router::any('rongyicode', 'NotifyController/rongyicode');//融易扫码
        Router::any('baoying', 'NotifyController/baoying');//保盈支付
        Router::any('hengyuan', 'NotifyController/hengyuan');//恒远支付
        Router::any('xizi', 'NotifyController/xizi');//戏子支付
        Router::any('zhongbao', 'NotifyController/zhongbao');//中宝支付
        Router::any('xiaoding', 'NotifyController/xiaoding');//小鼎支付
        Router::any('yangyu', 'NotifyController/yangyu');//洋芋支付
        Router::any('xiaoyi', 'NotifyController/xiaoyi');//小翼支付
        Router::any('fuxiy', 'NotifyController/fuxiy');//伏羲原生
        Router::any('yinyue', 'NotifyController/yinyue');//音乐支付
        Router::any('cang', 'NotifyController/cang');//小苍支付
        Router::any('fuxib', 'NotifyController/fuxib');//伏羲备用
        Router::any('bossyun', 'NotifyController/bossyun');
        Router::any('xiyuan', 'NotifyController/xiyuan');
    });

});
