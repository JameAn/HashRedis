<?php
/**
 * Created by James
 * Date: 2017/8/24
 * example RedisManager::getInstance()->get(key)
 */
namespace knowbox\libs;

class ConsistentHash extends RedisManager
{
    protected $redisServer = [];
    //设置redis节点最大值
    protected $hashNode = 255;

    public function __construct()
    {
        parent::__construct();
        $this->regAllServer();
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *  return int array index
     */
    protected function hashF($key, array $conf=[])
    {
        $hash = $this->time33($key);

        $len = sizeof($this->redisServer);
        if (empty($len)) {
            throw new \Exception('error!');
        }

        $keys = array_keys($this->redisServer);
        $redisConfs = array_values($this->redisServer);

        //如果不在区间内，则返回最后一个server
        if ($hash<= $keys[0] || $hash >= $keys[$len - 1]) {
            return $keys[$len - 1];
        }

        foreach ($keys as $key=>$pos) {
            $next_pos = null;
            if (isset($keys[$key + 1])) {
                $next_pos = $keys[$key + 1];
            }

            if (is_null($next_pos)) {
                return $pos;
            }
            //区间判断
            if ($hash >= $pos && $hash <= $next_pos) {
                return $pos;
            }
        }
        //return crc32($key)%count($confs);
    }

    public function getRedis($redisKey)
    {
        $mapIndex = $this->hashF($redisKey);
        //var_dump($mapIndex);exit;
        return $this->connect($this->redisServer[$mapIndex]);
    }

    public function regAllServer()
    {
        foreach ($this->_redisConfs as $serverConf) {
            $this->addServer($serverConf);
        }
        if ($this->redisServer) {
            ksort($this->redisServer);
        }
    }

    public function addServer(array $serverConf)
    {
        $_serverConf = json_encode($serverConf);
        $hash = $this->time33($_serverConf);
        if (!isset($this->redisServer[$hash])) {
            $this->redisServer[$hash] = $serverConf;
        }
    }

    public function time33($str)
    {
        $hash = 5381;
        $s = md5($str);
        $seed = 5;
        $len = 32;
        for ($i = 0; $i < $len; $i++) {
            $hash = ($hash << $seed) + $hash + ord($s{$i});
        }

        return $hash & 0x7fffffff;
    }

    public function printRedisServer()
    {
        foreach ($this->redisServer as $key => $value) {
            echo $key . '-' . json_encode($value) . "\n";
        }
    }
}
