<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\View\Exceptions;

use Exception;

/**
 * ViewException Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class ViewException extends Exception
{
    /**
     * ViewException constructor.
     *
     * @param null            $messages
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($messages = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($messages, $code, $previous);
    }
}
