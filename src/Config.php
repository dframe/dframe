<?php
namespace Dframe;
use Dframe\BaseException;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class Config
{
 
    protected static $cfg = array();
    private $file;
    public $path;
    
    public function __construct($file, $path = ''){
        $this->path = APP_DIR.'Config/'; // appDir zdefiniowany powinien byc w Config.php
        
        if(isset($path) AND !empty($path))
            $this->path = APP_DIR.$path.'/';


        $this->file = $file;
        if (file_exists($this->path.$this->file.'.php') != true)
            throw new BaseException('Not Found Config '. $this->path.$this->file.'.php');
    
        if(!isset(self::$cfg[$file]))
            self::$cfg[$file] = include($this->path.$this->file.'.php');

    }

    public static function load($file, $path = null){
        return new Config($file, $path);

    }    

    public function get($param = null, $or = null){

        if($param == null)
            return (isset(self::$cfg[$this->file]))? self::$cfg[$this->file] : null;

        return (isset(self::$cfg[$this->file][$param]) AND !empty(self::$cfg[$this->file][$param]))? self::$cfg[$this->file][$param] : $or;
    }

}