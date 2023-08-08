<?php

declare(strict_types=1);

namespace Hyperf\Tasks\redis;

use Hyperf\Utils\ApplicationContext;

class RedisLock implements RedisLockInterface
{
    /**
     * @var \Hyperf\Redis\Redis|mixed
     */
    private $_redis;

    /**
     * 前缀.
     * @var string
     */
    private $_prefix = 'redis-lock';

    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->_redis = $container->get(Redis::class);
    }

    /**
     * 获取锁.
     */
    public function getLock(string $key, string $value, int $seconds = 0): bool
    {
        if ($seconds > 0) {
            return (bool) $this->_redis->set($this->_buildKey($key), $value, ['nx', 'ex' => $seconds]);
        }
        return (bool) $this->_redis->setnx($this->_buildKey($key), $value);
    }

    /**
     * 自旋锁.
     * @param mixed $spinSeconds
     */
    public function spin(string $key, string $value, int $seconds, int $spinTimes = 2, $spinSeconds = 0.5): bool
    {
        if (is_float($spinSeconds)) {
            $microSeconds = intval(fmod($spinSeconds, 1) * 1000000);
            $spinSeconds = intval($spinSeconds);
        }

        while ($spinTimes > 0) {
            $lock = $this->getLock($key, $value, $seconds);
            if ($lock || --$spinTimes == 0) {
                return $lock;
            }
            if ($spinSeconds) {
                sleep($spinSeconds);
            }
            if (isset($microSeconds)) {
                usleep($microSeconds);
            }
        }

        return false;
    }

    /**
     * 释放锁.
     */
    public function release(string $key, string $value): bool
    {
        $luaScript = <<<'LUA_SCRIPT'
if redis.call('get',KEYS[1]) == ARGV[1] then
    return redis.call('del',KEYS[1])
else 
    return 0
end        
LUA_SCRIPT;

        return (bool) $this->_redis->eval($luaScript, $this->_buildKey($key), $value);
    }

    /**
     * 强制释放锁.
     */
    public function forceRelease(string $key): bool
    {
        return (bool) $this->_redis->del($this->_buildKey($key));
    }

    private function _buildKey($key)
    {
        return sprintf('%s:%s', $this->_prefix, $key);
    }
}
