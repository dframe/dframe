<?php
namespace Dframe;
use Dframe\BaseException;
use Dframe\Config;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class Loader extends Core
{

    private $controller;
    private $action;
    private $urlvalues;
    public $bootstrap;

    // Establish the requested controller as an object
    public function CreateController($controller = null, $action = null){
        if(is_null($controller) AND is_null($action)){
            $this->router->parseGets();
            
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
            }

            $routerConfig = Config::load('router');
            header("HTTP/1.0 404 Not Found");
            $this->router->redirect($routerConfig->get('404'));
            exit();
        }
        
        
        $this->controller = '\Controller\\'.$xsubControler.''.$this->controller.'Controller';
        return new $this->controller($this->baseClass);
    }

}