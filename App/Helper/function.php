<?php

use App\Model\Configs;
use App\Utility\RedisClient;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\RedisPool\RedisPool;

/**
 * 返回 dev.php 文件配置
 * @param string $name 配置名称
 * @param string $default 默认值
 * @return array|mixed|null
 */
function config($name = '', $default = '')
{
    return Config::getInstance()->getConf($name) ?: $default;
}


/**
 * 获取配置
 * @return mixed|null
 * @throws Exception
 */
function settings()
{
    $redis = RedisPool::defer('redis');
    $value = $redis->get('setting');
    if (!$value) {
        $setting = Configs::create()->getList();
        $redis->set('setting', $setting);
    }
    return $value;
}


function settlement($data)
{
    $redis = RedisPool::defer('redis');
    $job = json($data);
    $redis->rPush('queue:user-settlement',$job);
}



/**
 * 日志
 * @param $data
 * @param int $level
 */
function write_log($data,$level = 1)
{
    if (is_object($data) || is_array($data)) {
        $data = json_encode($data);
    }
    switch ($level){
        case 2:
            Logger::getInstance()->notice($data);
            break;
        case 3:
            Logger::getInstance()->waring($data);
            break;
        case 4:
            Logger::getInstance()->error($data);
            break;
        default:
            Logger::getInstance()->info($data);
            break;
    }
}


/**
 * 设置过期时间
 * @param int $time 有效期，单位秒
 * @return int
 */
function expires($time = 0)
{
    return time() + (int)$time;
}

/**
 * json格式化
 * @param $data
 * @return false|string
 */
function json($data)
{
    return json_encode($data);
}

/**
 * json反格式化
 * @param $data
 * @return false|string
 */
function unjson($data)
{
    return json_decode($data,true);
}


/**
 * 返回管理后台url地址
 * @param string $path
 * @return string
 */
function url_admin($path = '')
{
    return trim(trim(config('SERVER_URL'), '/') . '/suibian/' . trim($path, '/'), '/');
}
/**
 * 返回渠道后台url地址
 * @param string $path
 * @return string
 */
function url_canal($path = '')
{
    return trim(trim(config('SERVER_URL'), '/') . '/' . trim($path, '/'), '/');
}

/**
 * 返回代理后台url地址
 * @param string $path
 * @return string
 */
function url_agent($path = '')
{
    return trim(trim(config('SERVER_URL'), '/') . '/agent/' . trim($path, '/'), '/');
}

/**
 * 返回API url地址
 * @param string $path
 * @return string
 */
function url_api($path = '')
{
    return trim(trim(config('SERVER_URL'), '/') . '/api/' . trim($path, '/'), '/');
}

/**
 * 返回静态地址
 * @param string $name
 * @return string
 */
function asset($name = '')
{
    return trim(config('SERVER_URL'), '/') . '/' . $name;
}


/**
 * 状态转换
 * @param $data
 * @param array $map
 * @return mixed
 */
function state_to_text(&$data, $map = [])
{
    foreach ($data as $key => &$row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $text = $col . '_text';
                $row[$text] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pk 主键字段
 * @param string $pid parent标记字段
 * @param string $child child名字
 * @param int $root 最顶级id数字
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0)
{
    // 创建Tree
    $tree = [];
    // 创建基于主键的数组引用
    $refer = [];
    foreach ($list as $key => $data) {
        $refer[$data[$pk]] =& $list[$key];
    }
    foreach ($list as $key => $data) {
        // 判断是否存在parent
        $parentId = $data[$pid];
        if ($root == $parentId) {
            $tree[] =& $list[$key];
        } else {
            if (isset($refer[$parentId])) {
                $parent =& $refer[$parentId];
                $parent[$child][] =& $list[$key];
            }
        }
    }
    return $tree;
}

/**
 * 获取扣量信息
 * @param $deduction 扣量比
 * @return mixed
 */
function deduction($deduction)
{
    $deduction = $deduction/10;
    //概率数组
    $arr = [
        ['field' => 'deduct', 'v' => $deduction],//扣量
        ['field' => 'settlement', 'v' => 10 - $deduction]//结算
    ];
    foreach ($arr as $k => $v) {
        $temp[$k] = $v['v'];
    }
    //总概率
    $totalProbability = array_sum($temp);
    foreach ($temp as $key => $val) {
        $randNum = mt_rand(1, $totalProbability);
        if ($randNum <= $val) {
            $result = $arr[$key];
            break;
        } else {
            $totalProbability -= $val;
        }
    }
    return $result['field'];
}

/**
 * php加密用于js解密
 * @param $data
 * @return string
 */
function encrypt_data($data)
{
    $config = config('TOKEN');
    $key = $config['key'];
    $iv  = $config['iv'];
    return base64_encode(openssl_encrypt(json_encode($data),"aes-128-cbc",$key,OPENSSL_RAW_DATA,$iv));
}

/**
 * php解密js加密字符串
 * @param $data
 * @return string
 */
function decrypt_data($data)
{
    $config = config('TOKEN');
    $key = $config['key'];
    $iv  = $config['iv'];
    return json_decode(trim(openssl_decrypt($data,"AES-128-CBC",$key,OPENSSL_ZERO_PADDING,$iv)),true);
}

