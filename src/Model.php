<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\Loader\Loader;

/**
 * Model Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class Model extends Loader
{
    /**
     * Standard method for returning  result from the method.
     *
     * @param array|null $errors
     *
     * @return array
     */
    public function methodFail($errors = null): array
    {
        if ($errors === null) {
            return $this->methodResult(false);
        }

        if (!is_array($errors)) {
            $errors = [$errors];
        }

        return $this->methodResult(false, ['errors' => $errors]);
    }

    /**
     * Standard method for returning the result from the method.
     *
     * @param bool       $type
     * @param array|null $array
     *
     * @return array
     */
    public function methodResult($type, $array = null): array
    {
        if (!is_null($array)) {
            return array_merge(['return' => $type], $array);
        }

        return ['return' => $type];
    }

    /**
     * Init method.
     */
    public function init()
    {
    }
}
