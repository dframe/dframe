<?php
namespace Dframe;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class BaseException extends \Exception
{

    public function __construct($messages = null, $code = 0, Exception $previous = null){
        parent::__construct($messages, $code, $previous);
    }
}