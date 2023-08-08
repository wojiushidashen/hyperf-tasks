<?php

declare(strict_types=1);

namespace Hyperf\Tasks\tasks;

use Hyperf\Tasks\exceptions\ErrorCode;
use Hyperf\Tasks\exceptions\TaskException;
use Hyperf\Tasks\models\CrontabTasks;
use Hyperf\Tasks\redis\RedisLockInterface;

class Task implements TaskInterface
{
    private $_lockKey = 'CRONTAB_TASK_KEY';

    private $_lockExpire = 600;

    /**
     * @var RedisLockInterface
     */
    private $_redisLock;

    public function __construct(RedisLockInterface $redisLock)
    {
        $this->_redisLock = $redisLock;
    }

    /**
     * 订阅任务.
     */
    public function publish(string $taskName, array $params, bool $isSingle = true): bool
    {
        if ($this->_checkExists($taskName, $params)) {
            throw new TaskException(ErrorCode::FAIL, '任务已发布，请勿重新发布！');
        }

        $this->_createTask($taskName, $params, $isSingle);

        return true;
    }

    /**
     * 取消订阅.
     */
    public function unpublish(string $taskName): bool
    {
        $this->_getTasks($taskName, 100, function ($task) {
            /* @var CrontabTasks $taskModel */
            $taskModel = CrontabTasks::find($task->id);
            $taskName = $taskModel->name;
            $params = $this->_strToArray($taskModel->params);

            $taskModel->delete();
            $this->_unlock($this->_generateSignName($taskName, $params));
        });
    }

    /**
     * 执行任务.
     */
    public function run(string $taskName, int $bathSize, callable $callback = null): bool
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);

        $this->_getTasks($taskName, $bathSize, function ($task) use ($callback) {
            /* @var CrontabTasks $taskModel */
            $taskModel = CrontabTasks::find($task->id);
            $taskName = $taskModel->name;
            $params = $this->_strToArray($taskModel->params);
            $md5 = $this->_generateSignName($taskName, $params);

            if (($taskModel->is_single == CrontabTasks::IS_SIGNING_YES && ! $this->_lock($md5)) || $taskModel->status != CrontabTasks::STATUS_WAITING) {
                $errorMsg = $this->_setErrorInfo('当前任务正在执行中...');
                $this->_setStatusInfo($taskModel, CrontabTasks::STATUS_STARTING, $errorMsg);

                return;
            }

            $startTime = time();

            $this->_startTask($taskModel);

            try {
                $res = $callback($params);
                if ($res && $res = $this->_strToArray($res)) {
                    $task->result = $res;
                }
                $error = '';
                $status = CrontabTasks::STATUS_ENDED;
            } catch (\Throwable $throwable) {
                $error = $this->_setErrorInfo($throwable->getMessage(), $throwable->getFile(), $throwable->getLine(), $throwable->getTraceAsString());
                $status = CrontabTasks::STATUS_ERROR;
                $res = '';
            }

            $execTime = time() - $startTime;
            $this->_setStatusInfo($taskModel, $status, $error, $res, $execTime);

            if ($taskModel->is_single == CrontabTasks::IS_SIGNING_YES) {
                $this->_unlock($md5);
            }
        });

        return true;
    }

    public function open(string $taskName): bool
    {
        return true;
    }

    public function close(string $taskName): bool
    {
        return true;
    }

    private function _startTask(CrontabTasks $task)
    {
        $task->status = CrontabTasks::STATUS_STARTING;
        $task->save(false);
    }

    private function _setStatusInfo(CrontabTasks $task, $status, $error = '', $res = '', $execTime = 0)
    {
        $task->status = $status;
        if ($error) {
            $task->error = $error;
        }
        if ($res) {
            $task->result = $res;
        }

        $task->exec_time = $execTime;
        $task->save(false);
    }

    private function _setErrorInfo(string $message, string $file = '', string $line = '0', string $trance = '')
    {
        return json_encode([
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'trance' => $trance,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function _checkExists($taskName, $params)
    {
        return CrontabTasks::where('md5', $this->_generateSignName($taskName, $params))
            ->whereIn('status', [CrontabTasks::STATUS_WAITING, CrontabTasks::STATUS_STARTING])
            ->exists();
    }

    private function _createTask(string $taskName, array $params, bool $isSingle = false): string
    {
        $task = CrontabTasks::create([
            'name' => $taskName,
            'params' => $params,
            'is_single' => intval($isSingle),
            'status' => CrontabTasks::STATUS_WAITING,
            'switch' => CrontabTasks::SWITCH_OPEN,
            'md5' => $this->_generateSignName($taskName, $params),
        ]);

        return (string) $task->id;
    }

    private function _getTasks($taskName, $batchSize = 10, callable $callback = null)
    {
        CrontabTasks::query()
            ->where('name', $taskName)
            ->where('status', CrontabTasks::STATUS_WAITING)
            ->where('switch', CrontabTasks::SWITCH_OPEN)
            ->limit($batchSize)
            ->chunkById(100, function ($tasks) use ($callback) {
                if ($callback) {
                    foreach ($tasks as $task) {
                        $callback($task);
                    }
                }
            });
    }

    private function _generateSignName(string $taskName, array $params)
    {
        $queryStr = http_build_query($params);
        return md5(sprintf('%s:%s', $taskName, $queryStr));
    }

    private function _lock(string $md5)
    {
        return $this->_redisLock->spin($this->_lockKey, $md5, $this->_lockExpire);
    }

    private function _unlock(string $md5)
    {
        return $this->_redisLock->release($this->_lockKey, $md5);
    }

    private function _strToArray($str)
    {
        if ($str === '' || $str === null) {
            return [];
        }
        if (is_array($str)) {
            return $str;
        }
        $arr = json_decode((string) $str, true);
        if (! is_array($arr)) {
            return [];
        }
        return $arr;
    }
}
