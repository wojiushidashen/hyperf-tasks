<?php

declare(strict_types=1);

namespace Hyperf\Tasks\exceptions;

class TaskException extends \Exception
{
    public function __construct(int $code = ErrorCode::FAIL, $message = null, \Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        } elseif (is_array($message)) {
            $message = ErrorCode::getMessage($code, $message);
        }

        parent::__construct($message, $code, $previous);
    }
}
