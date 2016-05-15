<?php
namespace Dframe;
use Dframe\Config;
/**
* System Ładowania plików Kontrollera
*/
include_once "Functions.php";

class Loader extends Core
{

    private $controller;
    private $action;
    private $urlvalues;
    public $bootstrap;

    public function __construct($bootstrap){
        $this->baseClass = $bootstrap;
    }
    
    // Establish the requested controller as an object
    public function CreateController(){
      
        $routerConfig = Config::load('router');
        if(empty($_GET['task']))
        	$_GET['task'] = $routerConfig->get('NAME_CONTROLLER');

        if(empty($_GET['action']))
        	$_GET['action'] = $routerConfig->get('NAME_MODEL');

        $this->controller = $_GET['task'];
        $this->action = $_GET['action'];


        if(strstr($this->controller, ",") !== False){

            $url = explode(',', $this->controller);
            $urlCount = count($url)-1;
            $subControler = '';
            for ($i=0; $i < $urlCount; $i++) { 
                $subControler .= $url[$i].'/';
            }
            $this->controller = $url[$urlCount];

        }else $subControler = null;


       // Does the class exist?
        $patchController = 'Controller/'.$subControler.''.$this->controller.'.php';
        //var_dump($patchController);
        if(file_exists($patchController)){
            //echo 'OK 1';
            include_once $patchController;
            //echo 'OK 2';
            $path = null;
        }

        $xsubControler = str_replace("/", "\\", $subControler);
        
        if(!class_exists('\Controller\\'.$xsubControler.''.$this->controller.'Controller')){
        	if(ini_get('display_errors') == "on"){
        		echo 'Patch: '.'Controller/'.$xsubControler.''.$this->controller.'Controller';
        		echo '<br>bad controller error<br>';
        		return 1;
        	}
        	header("HTTP/1.0 404 Not Found");
            return 1;
        }
        //    
        //$parents = class_parents('Scscript\Controller\\'.$this->controller.'Controller');
        //// Does the class extend the controller class?
        //if(!method_exists('Scscript\Controller\\'.$this->controller.'Controller',$this->action)) {
        //    if(!method_exists('Scscript\Controller\\'.$this->controller.'Controller', 'page')){
//
        //    	if(ini_get('display_errors') == "on"){
        //    		echo 'Brak metody';
        //    		return 1;
        //    	}
        //  
        //    	header("HTTP/1.0 404 Not Found");
        //    	header('Location: ./?notFound=1');
    //
        //        return 1;
//
//
        //    }
//
        //}
        
        
        $this->controller = '\Controller\\'.$xsubControler.''.$this->controller.'Controller';
        return new $this->controller($this->baseClass);
    }

}
?>