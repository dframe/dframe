<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\BaseException;

/**
 * Config Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Config
{

    protected static $cfg = array();
    private $_file;
    public $path;

    public function __construct($file, $path = '')
    {

        $this->path = (isset($path) and !empty($path)) ? $path : APP_DIR . $path . 'Config'. DIRECTORY_SEPARATOR;

        $this->_file = $file;
        if (file_exists($this->path . $this->_file . '.php') != true) {
            self::$cfg[$file] = array();
        } else {
            self::$cfg[$file] = isset(self::$cfg[$file]) ? self::$cfg[$file] : include $this->path . $this->_file . '.php';
        }
    }

    public static function load($file, $path = null)
    {
        return new Config($file, $path);
    }

    public function get($param = null, $or = null)
    {
        if ($param == null) {
            return (isset(self::$cfg[$this->_file])) ? self::$cfg[$this->_file] : null;
        }

        return (isset(self::$cfg[$this->_file][$param]) and !empty(self::$cfg[$this->_file][$param])) ? self::$cfg[$this->_file][$param] : $or;
    }
}
