<?php
namespace Dframe;
/**
* System Ładowania plików Kontrollera
*/
include "Functions.php";

class Loader extends Core
{

    private $controller;
    private $action;
    private $urlvalues;
    public $bootstrap;
    
    // Establish the requested controller as an object
    public function CreateController(){
        
        $this->router->parseGets(); // Prasowanie $_GET

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
        if(file_exists($patchController)){
            include_once $patchController;
            $path = null;
        }

        if(!class_exists('\Controller\\'.$this->controller.'Controller')){
        	if(ini_get('display_errors') == "on"){
        		echo 'Patch: '.$patchController;
        		echo 'bad controller error';
        		return 1;
        	}
        	header("HTTP/1.0 404 Not Found");
            return 1;
        }
            
        $parents = class_parents('\Controller\\'.$this->controller.'Controller');
        // Does the class extend the controller class?
        if(!method_exists('\Controller\\'.$this->controller.'Controller',$this->action)) {
            if(!method_exists('\Controller\\'.$this->controller.'Controller', 'page')){

            	if(ini_get('display_errors') == "on"){
            		echo 'Brak metody';
            		return 1;
            	}
          
            	header("HTTP/1.0 404 Not Found");
            	header('Location: ./?notFound=1');
    
                return 1;


            }

        }

        $this->controller = '\Controller\\'.$this->controller.'Controller';
        return new $this->controller($this->baseClass);
    }

}
?>