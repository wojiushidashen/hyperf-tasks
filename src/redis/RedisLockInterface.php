<?php

declare(strict_types=1);

namespace Hyperf\Tasks\redis;

interface RedisLockInterface
{
    /**
     * 获取锁.
     *
     * @param string $key 唯一标识
     * @param string $value 值
     * @param int $seconds 过期时间，单位：s
     */
    public function getLock(string $key, string $value, int $seconds = 0): bool;

    /**
     * 自旋锁.
     *
     * @param string $key 唯一标识
     * @param string $value 值
     * @param int $seconds 过期时间，单位：s
     * @param int $spinTimes 自旋次数
     * @param float $spinSeconds 间隔秒数
     */
    public function spin(string $key, string $value, int $seconds, int $spinTimes = 2, $spinSeconds = 0.5): bool;

    /**
     * 释放锁.
     *
     * @param string $key 唯一标识
     * @param string $value 值
     */
    public function release(string $key, string $value): bool;

    /**
     * 强制释放锁.
     *
     * @param string $key 唯一标识
     */
    public function forceRelease(string $key): bool;
}
