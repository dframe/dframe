<?php
/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */
 
namespace Dframe;

/**
 * Short Description
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class Model extends Loader
{
    
    public function methodResult($type, $array = null)
    {
        if (!is_null($array)) {
            return array_merge(array('return' => $type), $array);
        }
 
        return array('return' => $type);
    }

    public function methodFail($errors = null)
    {
        if ($errors === null) {
            return $this->methodResult(false);
        }

        if (!is_array($errors)) {
            $errors = array($errors);
        }
        
        return $this->methodResult(false, array('errors' => $errors));
    }
      
    public function init()
    {
    }
}
