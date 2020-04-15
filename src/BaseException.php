<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Exception;

/**
 * BaseException Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class BaseException extends Exception
{
    /**
     * BaseException constructor.
     *
     * @param string          $messages
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($messages = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($messages, $code, $previous);
    }
}
