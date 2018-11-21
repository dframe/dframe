<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Router\Exceptions;

/**
 * RuntimeException Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class RuntimeException extends \RuntimeException
{
    /**
     * RuntimeException constructor.
     *
     * @param null                   $messages
     * @param int                    $code
     * @param \RuntimeException|null $previous
     */
    public function __construct($messages = null, $code = 0, \RuntimeException $previous = null)
    {
        parent::__construct($messages, $code, $previous);
    }
}
