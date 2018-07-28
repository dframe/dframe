<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Loader\Exceptions;

/**
 * LoaderException Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class LoaderException extends \ErrorException
{
    public function __construct($messages = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($messages, $code, $previous);
    }
}
