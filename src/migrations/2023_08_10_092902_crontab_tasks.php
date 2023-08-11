<?php

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CrontabTasks extends Migration
{
    public function up(): void
    {
        $tableName = 'crontab_tasks';
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id')->autoIncrement()->comment('ID');
            $table->string('name', 100)->nullable(false)->default('')->comment('任务名称');
            $table->json('params')->nullable()->comment('参数');
            $table->string('md5', 200)->comment('md5验证值');
            $table->tinyInteger('status')->nullable(false)->default(0)->comment('状态：0未开始 1等待执行 2执行中 3已结束 4错误');
            $table->tinyInteger('switch')->nullable(false)->default(0)->comment('开关：0关闭 1开启');
            $table->text('error')->nullable()->comment('错误信息');
            $table->integer('exec_time')->nullable(false)->default(0)->comment('执行时间：单位s');
            $table->tinyInteger('is_single')->nullable(false)->default(0)->comment('是否是单次执行');
            $table->json('result')->nullable()->comment('任务执行完成后的结果');
            $table->bigInteger('operator')->nullable(false)->default(0)->comment('任务执行完成后的结果');
            $table->integer('delay')->nullable(false)->default(0)->comment('延迟执行时间，单位：s');
            $table->timestamp('created_time')->nullable()->comment('创建时间');
            $table->timestamp('updated_time')->nullable()->comment('更新时间');

            $table->index('name', 'idx_name');
            $table->index('md5', 'idx_md5');
        });
    }

    public function down(): void
    {
    }
}
