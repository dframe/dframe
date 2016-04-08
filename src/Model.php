<?php
namespace Dframe;

/**
 * This class includes methods for models.
 *
 * @abstract
 */

abstract class Model extends Core
{
    
    public function methodResult($type, $array = null){
        if(!is_null($array))
            return array_merge(array('return' => $type), $array);
 
        return array('return' => $type);
    }

    public function methodFail($errors = null){
        if($errors === null){
            return $this->methodResult(false);
        }
        if(!is_array($errors)){
            $errors = array($errors);
        }

    	return $this->methodResult(false, array('errors' => $errors));
    }
      
    public function init() {}
}