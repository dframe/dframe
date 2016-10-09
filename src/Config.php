<?php
namespace Dframe;
use Dframe\BaseException;

/**
 * Copyright (C) 2016  
 * @author Sławomir Kaleta
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class Config
{
 
    protected static $cfg = array();
    private $file;
    public $path;
    
    public function __construct($file){
        $this->path = appDir.'../app/Config/'; // appDir zdefiniowany powinien byc w Config.php

        $this->file = $file;
        if (file_exists($this->path.$this->file.'.php') != true)
            throw new BaseException('Not Found Config '. $this->path.$this->file.'.php');
    
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