<?php

declare(strict_types=1);

namespace Hyperf\Tasks\exceptions;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 错误码.
 *
 * @Constants
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("FAIL")
     */
    public const FAIL = -1;
}
