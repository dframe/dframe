<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Config;

use Dframe\Config\Exceptions\ConfigException;

/**
 * Config Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Config
{
    /**
     * @var array
     */
    protected static array $cfg = [];

    /**
     * @var string
     */
    public string $path;

    /**
     * @var string
     */
    protected string $file;

    /**
     * Config constructor.
     *
     * @param        $file
     * @param string $path
     */
    public function __construct($file, $path = '')
    {
        if (!defined('APP_DIR')) {
            throw new ConfigException('Please Define APP_DIR in Main config.php', 500);
        }

        $this->path = (isset($path) and !empty($path)) ? $path : \APP_DIR . $path . 'Config' . DIRECTORY_SEPARATOR;

        $this->file = $file;
        if (file_exists($this->path . $this->file . '.php') !== true) {
            self::$cfg[$file] = [];
        } else {
            self::$cfg[$file] = isset(self::$cfg[$file]) ? self::$cfg[$file] : include $this->path . $this->file . '.php';
        }
    }

    /**
     * @param      $file
     * @param null $path
     *
     * @return self
     */
    public static function load($file, $path = null): self
    {
        return new self($file, $path);
    }

    /**
     * @param null $param
     * @param null $or
     *
     * @return mixed|null
     */
    public function get($param = null, $or = null)
    {
        if ($param === null) {
            return (isset(self::$cfg[$this->file])) ? self::$cfg[$this->file] : null;
        }

        return (isset(self::$cfg[$this->file][$param]) and !empty(self::$cfg[$this->file][$param])) ? self::$cfg[$this->file][$param] : $or;
    }
}
