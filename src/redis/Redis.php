<?php

declare(strict_types=1);

namespace Hyperf\Tasks\redis;

class Redis extends \Hyperf\Redis\Redis
{
    protected $poolName = 'task';
}
