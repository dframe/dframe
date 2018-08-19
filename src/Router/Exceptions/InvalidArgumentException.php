<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Router\Exceptions;

/**
 * InvalidArgumentException Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    public function __construct($messages = null, $code = 0, \InvalidArgumentException $previous = null)
    {
        parent::__construct($messages, $code, $previous);
    }
}
