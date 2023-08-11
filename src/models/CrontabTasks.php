<?php

declare(strict_types=1);

namespace Hyperf\Tasks\models;

use Hyperf\Database\Model\Model;

/**
 * This is the model class for table "cron_tasks".
 *
 * @property int $id 主键ID
 * @property string $name 任务名称
 * @property string $params 参数
 * @property int $status 状态：0未开始 1等待执行 2执行中 3已结束 4错误 5任务堆积
 * @property int $switch 开关：0关闭 1开启
 * @property string $md5 MD5验证值
 * @property null|string $error 错误信息
 * @property int $exec_time 执行时间：单位s
 * @property int $is_single 是否是单次执行
 * @property int $delay 延迟执行时间，单位：s
 * @property string $result 任务执行完成后的结果
 * @property null|string $created_time
 * @property null|string $updated_time
 * @property int $operator
 */
class CrontabTasks extends Model
{
    public const CREATED_AT = 'created_time';

    public const UPDATED_AT = 'updated_time';

    public const STATUS_NOT_STARTED = 0;

    public const STATUS_WAITING = 1;

    public const STATUS_STARTING = 2;

    public const STATUS_ENDED = 3;

    public const STATUS_ERROR = 4;

    // 注意：（可能是同一资源竞争导致堆积，这种情况很快会被消费掉；任务未执行完，后面产生新的一样的任务会被堆积，不会被消费）
    public const STATUS_STACKING = 5;

    public const SWITCH_OPEN = 1;

    public const SWITCH_CLOSE = 0;

    public const IS_SIGNING_YES = 1;

    public $incrementing = true;

    protected $table = 'crontab_tasks';

    protected $fillable = ['id', 'name', 'params', 'md5', 'status', 'switch', 'error', 'exec_time', 'is_single', 'result', 'operator', 'delay', 'created_time', 'updated_time'];

    protected $casts = [
        'id' => 'string',
        'created_time' => 'string',
        'updated_time' => 'string',
    ];
}
