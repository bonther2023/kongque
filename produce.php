<?php
return [
    'SERVER_NAME' => "Customer",
    'SERVER_URL' => "http://www.custom.bh/",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SOCKET_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => false,//设置为true时，将启用异步安全重启特性，Worker进程会等待异步事件完成后再退出
            'task_enable_coroutine' => true,
            'max_wait_time' => 3,// reload_async 开启的主要目的是为了保证 服务重载时，协程 或 异步 任务能正常结束。 所以一定要搭配 max_wait_time (默认3s) 来进行使用，否则超时后 worker 会被强制退出。
//            'enable_static_handler' => true,//加入以下两条配置以返回静态文件
//            'document_root' => EASYSWOOLE_ROOT . "/Public",
//            'heartbeat_idle_time' => 60, // 300没有心跳时则断开(设置无效),转到nginx中设置
//            'heartbeat_check_interval' => 10,(设置无效)
        ],
        'TASK' => [
            'workerNum' => 8,
            'maxRunningNum' => 50,
            'timeout' => 15
        ]
    ],
    'TEMP_DIR' => '/temp',
    'LOG_DIR' => EASYSWOOLE_ROOT . '/Log',
    'PUBLIC_ROOT' => EASYSWOOLE_ROOT . '/Public',
];
