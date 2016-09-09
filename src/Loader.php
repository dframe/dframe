<?php
namespace Dframe;
use Dframe\Config;

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

class Loader extends Core
{

    private $controller;
    private $action;
    private $urlvalues;
    public $bootstrap;

    //public function __construct($bootstrap){
    //    $this->baseClass = $bootstrap;
    //}
    
    // Establish the requested controller as an object
    public function CreateController($controller = null, $action = null){
        if(is_null($controller) AND is_null($action)){

            $routerConfig = Config::load('router');
            if(empty($_GET['task']))
            	$_GET['task'] = $routerConfig->get('NAME_CONTROLLER');
    
            if(empty($_GET['action']))
            	$_GET['action'] = $routerConfig->get('NAME_MODEL');
            
            $this->controller = $_GET['task'];
            $this->action = $_GET['action'];

        }else{
            $this->controller = $controller;
            $this->action = $action;
        }


        if(strstr($this->controller, ",") !== False){

            $url = explode(',', $this->controller);
            $urlCount = count($url)-1;
            $subControler = '';
            for ($i=0; $i < $urlCount; $i++) { 
                $subControler .= $url[$i].'/';
            }
            $this->controller = $url[$urlCount];

        }else 
            $subControler = null;


       // Does the class exist?
        $patchController = appDir.'../app/Controller/'.$subControler.''.$this->controller.'.php';
        //var_dump($patchController);
        if(file_exists($patchController)){
            include_once $patchController;
            $path = null;
        }

        $xsubControler = str_replace("/", "\\", $subControler);
        try {

            if(!class_exists('\Controller\\'.$xsubControler.''.$this->controller.'Controller'))
        	    throw new BaseException('Bad controller error');

        }catch(BaseException $e) {

            if(ini_get('display_errors') == "on"){
                echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br /> 
                Trace: '.$e->getTraceAsString();
                exit();
            }else{
            	$routerConfig = Config::load('router');
                header("HTTP/1.0 404 Not Found");
                $this->router->redirect($routerConfig->get('404'));
                return 1;
            }

        }
        
        
        $this->controller = '\Controller\\'.$xsubControler.''.$this->controller.'Controller';
        return new $this->controller($this->baseClass);
    }

}