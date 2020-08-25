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
   * @param string          $message
   * @param int            $code
   * @param \RuntimeException|null $previous
   */
   public function __construct($message = "", $code = 0, \RuntimeException $previous = null)
   {
     parent::__construct($message, $code, $previous);
   }
}
