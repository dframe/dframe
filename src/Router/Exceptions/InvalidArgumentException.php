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
   /**
   * InvalidArgumentException constructor.
   *
   * @param string               $message
   * @param int                $code
   * @param \InvalidArgumentException|null $previous
   */
   public function __construct($message = "", $code = 0, \InvalidArgumentException $previous = null)
   {
     parent::__construct($message, $code, $previous);
   }
}
