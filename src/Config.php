<?php
namespace Dframe;

class Config{

    protected static $cfg = array();
	public $path = 'config/';

    public function __construct($file){
    	$this->file = $file;
    	if (file_exists($this->path.$this->file.'.php') == false) 
    		return false;
        
        if(!isset(self::$cfg[$file]))
            self::$cfg[$file] = include($this->path.$this->file.'.php');

    }

    public static function load($file){
        return new Config($file);
    }

    public function get($param = null, $or = null){


    	if($param == null)
    		return (isset(self::$cfg[$this->file]))? self::$cfg[$this->file] : null;

	    return (isset(self::$cfg[$this->file][$param]) AND !empty(self::$cfg[$this->file][$param]))? self::$cfg[$this->file][$param] : $or;
    }


}