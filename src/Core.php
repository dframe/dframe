<?php
namespace Dframe;
use Dframe\Router;

/**
 * Copyright (C) 2016  Sławomir Kaleta
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

class Core
{
    public $baseClass = null;
    
    public function __construct($bootstrap =null){
        if(!defined('appDir'))
           throw new \Exception('Please Define appDir in Main config.php');

        if(!defined('SALT'))
           throw new \Exception('Please Define SALT in Main config.php');

        if($bootstrap != null){
            $this->baseClass = $bootstrap;
            $this->router = new Router();
        }

        return $this;
    }

    /*
     *   Metoda do includowania pliku modelu i wywołanie objektu przez namespace
    */
    public function loadModel($name){
        return $this->loadObject($name, 'Model');
    }

    /*
     *   Metoda do includowania pliku widoku i wywołanie objektu przez namespace
    */
    public function loadView($name){
        return $this->loadObject($name, 'View');

    }

    private function loadObject($name, $type){

        if(!in_array($type, (array('Model', 'View'))))
            return false;

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
        
        $n = str_replace($type, '', $name);
        $path = appDir.'../app/'.$type.'/'.$folder.$n.'.php';

        if(!empty($folder))
            $name = '\\'.$type.'\\'.str_replace(array('\\', '/'), '\\', $folder).$name.$type;   
        else
            $name = '\\'.$type.'\\'.$name.$type;

        try {
            if(is_file($path)) {
                include_once $path;
                $ob = new $name($this->baseClass);
                $ob->init();
            }else
                throw new \Exception('Can not open '.$type.' '.$name.' in: '.$path);
           
        }
        catch(\Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }

        return $ob; 
    }

    /**
     * Ładowanie rodzaju silnika widoku
     * php/html, smarty, twig
     */
    public function setView($engine = 'defaultView'){
        $this->view = $engine;
    }

    /** 
     * Metoda 
     * init dzialajaca jak __construct wywoływana na poczatku kodu
     * end identycznie tyle ze na końcu
     */

    public function init() {}
    public function end() {}
    
}