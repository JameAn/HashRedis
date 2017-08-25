<?php
/**
 * Created by James
 * Date: 2017/8/24
 * example RedisManager::getInstance()->get(key)
 */
namespace knowbox\libs;

class RedisManager
{
    private $_redis = [];
    #redis配置
    private $_redisConfs = [];
    #已经注册的redis实例数组
    private $_regRedis = [];

    private static $instance;

    public function __construct()
    {
        require_once "../config/Init.php";
        $this->_redisConfs = require_once ROOT_PATH."/config/RedisConfig.php";
        $this->_redisConfs = $this->getRedisInstanceConf($this->_redisConfs);
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *  将redisconf中的默认配置与实例配置整合
     */
    private function getRedisInstanceConf($redisConf)
    {
        if (empty($redisConf['instance'])) {
            return $redisConf['default'];
        }

        foreach ($redisConf["instance"] as &$instance) {
            $instance = array_merge($redisConf['default'], $instance);
        }
        return $redisConf['instance'];
    }

    private function connect($conf)
    {
        $mapKey = md5(implode(":", $conf));
        if (array_key_exists($mapKey, $this->_regRedis)) {
            $connect_status = $this->_regRedis[$mapKey]->ping();
            if ($connect_status === "+PONG") {
                return $this->_regRedis[$mapKey];
            }
        }
        $redis = new \Redis();
        $redis->connect($conf['HOST'], $conf['PORT']);
        $redis->auth($conf['AUTH']);
        $redis->select($conf['DB']);

        $this->_regRedis[$mapKey] = $redis;
        return $redis;
    }

    /**
     *  return int array index
     */
    protected function hashF($key, array $confs)
    {
        return crc32($key)%count($confs);
    }

    public function getRedis($redisKey)
    {
        $mapIndex = $this->hashF($redisKey, $this->_redisConfs);
        return $this->connect($this->_redisConfs[$mapIndex]);
    }

    public function __call($funName, $args)
    {
        $cachekey = array_shift($args);
        switch (count($args)) {
            case 0:
               return $this->getRedis($cachekey)->$funName($cachekey);
               break;
            case 1:
               return $this->getRedis($cachekey)->$funName($cachekey, array_shift($args));
               break;
            case 2:
                return $this->getRedis($cachekey)->$funName($cachekey, array_shift($$args), array_shift($args));
                break;
            case 3:
                return $this->getRedis($cachekey)->$funName($cachekey, array_shift($$args), array_shift($args), array_shift($args));
                break;
            default:
                throw new \Exception("未定义参数");
        }
    }
}
