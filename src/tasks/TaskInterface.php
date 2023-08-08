<?php

declare(strict_types=1);

namespace Hyperf\Tasks\tasks;

interface TaskInterface
{
    /**
     * 订阅事件.
     * @param string $taskName 任务名称
     * @param array $params 参数
     * @param bool $isSingle 是否是单例
     * @return mixed
     */
    public function publish(string $taskName, array $params, bool $isSingle = true): bool;

    /**
     * 取消订阅.
     * @param string $taskName 任务名称
     */
    public function unpublish(string $taskName): bool;

    /**
     * 执行任务.
     * @param string $taskName 任务类型
     * @param null|callable $callback 闭包，参数为订阅时传入的参数，例如：callback($params){}
     * @param int $bathSize 批量执行个数
     */
    public function run(string $taskName, int $bathSize, callable $callback = null): bool;

    /**
     * 开启任务.
     * @param string $taskName 任务名称
     */
    public function open(string $taskName): bool;

    /**
     * 关闭任务.
     * @param string $taskName 任务名称
     */
    public function close(string $taskName): bool;
}
