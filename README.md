# Hyperf Tasks
> hyperf任务，定时任务使用；任务状态记录在mysql中。

## 1、使用教程
### a、下载composer包
```shell
> composer require ezijing/hyperf-tasks
```

### b、发布数据迁移脚本
```shell
> php bin/hyperf.php vendor:publish ezijing/hyperf-tasks
```

### c、创建数据库表
```shell
> php bin/hyperf.php migrate
```

### d、配置redis连接池
> 在 `config/autoload/redis.php`下新增配置
```php 
<?php

declare(strict_types=1);

return [
    ... 
    'task' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

### c、异常配置
> 在文件`app/Exception/Handler/AppExceptionHandler.php`中做如下配置（根据自己的情况更改）：
```php 
<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Throwable;
use Psr\Http\Message\ResponseInterface;

class AppExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $sendData = [
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
        ];
        $this->stopPropagation();
        
        switch (true) {
            // 配置任务异常
             case $throwable instanceof TaskException:
                $data = Json::encode($sendData);
                break;
             default:
                $data = Json::encode([
                    'code' => $throwable->getCode() ?? ErrorCode::FAIL,
                    'message' => $throwable->getMessage(),
                ]);
                $this->logger->error(sprintf('%s %s[%s] code:%s in %s', date('Y-m-d H:i:s'), $throwable->getMessage(), $throwable->getLine(), $throwable->getCode(), $throwable->getFile()));
                $this->logger->error($throwable->getTraceAsString());
        }
        
        return $response
            ->withHeader('Content-type', 'application/json;  charset=utf-8')
            ->withStatus(200)
            ->withBody(new SwooleStream($data));
    }
}
```

## 2、使用

### a、发布任务
```php 
<?php

declare(strict_types=1);

use Hyperf\Di\Annotation\Inject;
use Hyperf\Tasks\tasks\TaskInterface;

class IndexController extends AbstractController
{
    public function index(TaskInterface $task) 
    {
        $userId = 1;
        $task->setOperator($userId);
        
        // 标识
        $key = 'test1';
        // 参数
        $params = ['id' => 1];
        $task->publish($key, []);
    }
}
```

### b、取消任务
```php 
<?php

declare(strict_types=1);

use Hyperf\Di\Annotation\Inject;
use Hyperf\Tasks\tasks\TaskInterface;

class IndexController extends AbstractController
{
    public function index(TaskInterface $task) 
    {
        // 标识
        $key = 'test1';
        $task->unpublish($key);
    }
}
```

### c、执行任务
```php 
<?php

declare(strict_types=1);

use Hyperf\Di\Annotation\Inject;
use Hyperf\Tasks\tasks\TaskInterface;

class IndexController extends AbstractController
{
    public function index(TaskInterface $task) 
    {
        // 标识
        $key = 'test1';
        // 批量执行
        $batchSize = 5;
        
        $task->run($key', $batchSize, function($params) {
            sleep(5);
            
            // $params 处理传过来的参数
            
            // todo 执行业务内容
            
            // 返回参数，必须是数组或是不返回
            return $params; 
        });
    }
}
```
