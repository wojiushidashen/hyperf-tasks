<?php

declare(strict_types=1);

namespace Hyperf\Tasks;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \Hyperf\Tasks\redis\RedisLockInterface::class => \Hyperf\Tasks\redis\RedisLock::class,
                \Hyperf\Tasks\tasks\TaskInterface::class => \Hyperf\Tasks\tasks\Task::class,
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'migrations',
                    'description' => '数据表迁移.',
                    'source' => __DIR__ . '/../src/migrations/2023_08_10_092902_crontab_tasks.php',
                    'destination' => BASE_PATH . '/migrations/2023_08_10_092902_crontab_tasks.php',
                ],
            ],
        ];
    }
}
