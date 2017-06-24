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

class Loader
{

    private $controller;
    private $action;
    private $urlvalues;
    public $bootstrap;

    public function __construct($bootstrap){

        if(!defined('appDir'))
           throw new BaseException('Please Define appDir in Main config.php', 500);

        if(!defined('SALT'))
           throw new BaseException('Please Define SALT in Main config.php', 500);

        $this->baseClass = $bootstrap;
        if(isset($this->baseClass->router))
            $this->router = $this->baseClass->router;
        
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

            if(!is_file($path))
                throw new BaseException('Can not open '.$type.' '.$name.' in: '.$path);

            include_once $path;
            $ob = new $name($this->baseClass);
            $ob->init();
           
        }catch(BaseException $e) {
            
            if(ini_get('display_errors') == "on"){
                echo $e->getMessage().'<br><br>
                File: '.$e->getFile().'<br>
                Code line: '.$e->getLine().'<br> 
                Trace: '.$e->getTraceAsString();
                exit();
            }

            $routerConfig = Config::load('router');
            header("HTTP/1.0 400 Bad Request");

            if(isset($routerConfig->get('error/404')[0]))
                $this->router->redirect($routerConfig->get('error/404')[0]);

            exit();
        }

        return $ob; 
    }


    // Establish the requested controller as an object
    public function loadController($controller){

        $subControler = null;
        if(strstr($controller, ",") !== False){

            $url = explode(',', $controller);
            $urlCount = count($url)-1;
            $subControler = '';
            
            for ($i=0; $i < $urlCount; $i++) { 
                $subControler .= $url[$i].'/';
            }

            $controller = $url[$urlCount];

        }

        // Does the class exist?
        $patchController = appDir.'../app/Controller/'.$subControler.''.$controller.'.php';
        //var_dump($patchController);
        if(file_exists($patchController)){
            include_once $patchController;
            $path = null;
        }

        $xsubControler = str_replace("/", "\\", $subControler);
        try {

            if(!class_exists('\Controller\\'.$xsubControler.''.$controller.'Controller'))
                throw new BaseException('Bad controller error');

            $controller = '\Controller\\'.$xsubControler.''.$controller.'Controller';
            $returnController = new $controller($this->baseClass);

        }catch(BaseException $e) {
            
            if(ini_get('display_errors') == 'on'){
                echo $e->getMessage().'<br><br>
                File: '.$e->getFile().'<br>
                Code line: '.$e->getLine().'<br> 
                Trace: '.$e->getTraceAsString();
                exit();
            }

            $routerConfig = Config::load('router');
            header("HTTP/1.0 404 Not Found");

            if(isset($routerConfig->get('error/404')[0]))
                $this->router->redirect($routerConfig->get('error/404')[0]);

            exit();
        }
        
        return $returnController;
    }



    /** 
     * Metoda 
     * init dzialajaca jak __construct wywoływana na poczatku kodu
     * end identycznie tyle ze na końcu
     */

    public function init() {}
    public function end() {}

}