<?php
namespace EasySwoole\EasySwoole;

use App\Crontab\AgentSettlement;
use App\Crontab\CanalSettlement;
use App\Crontab\ClearOrder;
use App\Crontab\ClearUser;
use App\Process\UpdateMoney;
use App\Process\UpdateOrder;
use App\Process\UpdateRebate;
use App\Process\UpdateUser;
use App\Process\UpdateVideo;
use App\Process\UserLogin;
use App\Process\UserOnline;
use App\Process\UserRegister;
use App\Process\UserSettlement;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\HotReload\HotReload;
use EasySwoole\HotReload\HotReloadOptions;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config as MysqlConfig;


class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
        define('PROCESS_NAME_PREFIX', config('SERVER_NAME'));
    }

    public static function mainServerCreate(EventRegister $register)
    {
        /****************** 热重启 **********************/
        $hotReloadOptions = new HotReloadOptions();
		$hotReloadOptions->disableInotify(false);
        $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);//添加要检视的目录
        $hotReload = new HotReload($hotReloadOptions);
        $server = ServerManager::getInstance()->getSwooleServer();
        $hotReload->attachToServer($server);


        /****************** mysql **********************/
        $sqlConfig = new MysqlConfig();
        $sqlConfig->setHost('rm-j6cs395v65gf02zfi.mysql.rds.aliyuncs.com');
        $sqlConfig->setUser('root');
        $sqlConfig->setPassword('Ndvgg6ddm1ho33n13');
        $sqlConfig->setDatabase('kongque');

        //连接池配置
        $sqlConfig->setGetObjectTimeout(3.0); //设置获取连接池对象超时时间
        $sqlConfig->setIntervalCheckTime(30*1000); //设置检测连接存活执行回收和创建的周期
        $sqlConfig->setMaxIdleTime(15); //连接池对象最大闲置时间(秒)
        $sqlConfig->setMaxObjectNum(100); //设置最大连接池存在连接对象数量
        $sqlConfig->setMinObjectNum(5); //设置最小连接池存在连接对象数量
        DbManager::getInstance()->addConnection(new Connection($sqlConfig));//第二个参数为连接名称，默认defult,可以设置第二张表的连接配置

        /****************** redis **********************/

        $redisConfig = new RedisConfig();
        $redisConfig->setHost('r-j6cdq568z47m50d1e3.redis.rds.aliyuncs.com');
        $redisConfig->setAuth('Ndvgg6ddm1ho33n13');
        $redisConfig->setDb(10);
        $redisConfig->setSerialize(1);//0不序列化 1php 2json
        $poolConfig = RedisPool::getInstance()->register($redisConfig,'redis');
        //配置连接池连接数
        $poolConfig->setMinObjectNum(5);
        $poolConfig->setMaxObjectNum(100);
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        //跨域
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(Status::CODE_OK);
            $response->end();
        }
        $response->withHeader('Access-Control-Allow-Origin', '*');
    }
}