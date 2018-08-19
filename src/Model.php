<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

/**
 * Model Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class Model extends Loader
{
    /**
     * Standard method for returning the result from the method.
     *
     * @param bool  $type
     * @param array $array
     *
     * @return array
     */
    public function methodResult($type, $array = null)
    {
        if (!is_null($array)) {
            return array_merge(['return' => $type], $array);
        }

        return ['return' => $type];
    }

    /**
     * Standard method for returning  result from the method.
     *
     * @param array $errors
     *
     * @return array
     */
    public function methodFail($errors = null)
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
     * Init method.
     */
    public function init()
    {
    }
}
