<?php

declare(strict_types=1);

namespace Hyperf\Tasks;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                '\Hyperf\Tasks\redis\RedisLockInterface' => '\Hyperf\Tasks\redis\RedisLock',
                '\Hyperf\Tasks\tasks\TaskInterface' => '\Hyperf\Tasks\tasks\Task',
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
                    'id' => 'crontab_task',
                    'description' => 'The config of redis client.',
                    'source' => __DIR__ . '/../publish/crontab_task.php',
                    'destination' => BASE_PATH . '/config/autoload/crontab_task.php',
                ],
            ],
        ];
    }
}
