<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Task\Exceptions;

use Exception;

/**
 * TaskException Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class TaskException extends Exception
{
    /**
     * LoaderException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
