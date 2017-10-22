<?php
namespace Dframe;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE (MIT)
 */

abstract class Model extends Loader
{
    
    public function methodResult($type, $array = null){
        if(!is_null($array))
            return array_merge(array('return' => $type), $array);
 
        return array('return' => $type);
    }

    public function methodFail($errors = null){
        if($errors === null)
            return $this->methodResult(false);

        if(!is_array($errors))
            $errors = array($errors);
        
        return $this->methodResult(false, array('errors' => $errors));
    }
      
    public function init() {}
    
}