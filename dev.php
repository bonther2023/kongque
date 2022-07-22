<?php
return [
    'SERVER_NAME' => "KONGQUE",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9510,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 4,
            'reload_async' => false,//设置为true时，将启用异步安全重启特性，Worker进程会等待异步事件完成后再退出，若开启了 reload_async 配置时，请将定时器移动到自定义进程内，否则会导致worker进程无法reload
            'task_enable_coroutine' => true,
            'max_wait_time' => 3,// reload_async 开启的主要目的是为了保证 服务重载时，协程 或 异步 任务能正常结束。 所以一定要搭配 max_wait_time (默认3s) 来进行使用，否则超时后 worker 会被强制退出。
        ],
        'TASK' => [
            'workerNum' => 4,
            'maxRunningNum' => 50,
            'timeout' => 15
        ]
    ],
    'TEMP_DIR' => EASYSWOOLE_ROOT . '/Temp',
    //'TEMP_DIR' => '/temp',
    'LOG_DIR' => EASYSWOOLE_ROOT . '/Log',
    'PUBLIC_ROOT' => EASYSWOOLE_ROOT . '/Public',

    'CAPTCHA' => [
        'charset' => '123456789',              // 设置字符集合
        'length' => 4,                        // 设置生成验证码位数
        'curve' => false,                    // 开启混淆曲线
        'notice' => true,                     // 开启噪点生成
        'font_color' => '#E85850',                // 字体颜色
        'font_size' => 26,                       // 字体大小
        'back_color' => '#FFF0F5',                // 背景颜色
        'image_height' => 50,                       // 图片高度
        'image_weight' => 160,                      // 图片宽度
        'temp' => EASYSWOOLE_ROOT . '/Temp',// 设置缓存目录
    ],

    'TOKEN' => [
        'key' => 'baiguoapp9999988', //16位
        'iv'  => 'baiguoapp9999988',
    ],

];
