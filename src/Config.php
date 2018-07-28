<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

/**
 * Config Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Config
{
    protected static $cfg = [];
    private $file;
    public $path;

    public function __construct($file, $path = '')
    {
        $this->path = (isset($path) and !empty($path)) ? $path : APP_DIR . $path . 'Config' . DIRECTORY_SEPARATOR;

        $this->file = $file;
        if (file_exists($this->path . $this->file . '.php') != true) {
            self::$cfg[$file] = [];
        } else {
            self::$cfg[$file] = isset(self::$cfg[$file]) ? self::$cfg[$file] : include $this->path . $this->file . '.php';
        }
    }

    public static function load($file, $path = null)
    {
        return new Config($file, $path);
    }

    public function get($param = null, $or = null)
    {
        if ($param === null) {
            return (isset(self::$cfg[$this->file])) ? self::$cfg[$this->file] : null;
        }

        return (isset(self::$cfg[$this->file][$param]) and !empty(self::$cfg[$this->file][$param])) ? self::$cfg[$this->file][$param] : $or;
    }
}
