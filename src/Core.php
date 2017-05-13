<?php
namespace Dframe;
use Dframe\BaseException;
use Dframe\Router;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class Core
{
    public $baseClass = null;
    
    public function __construct($bootstrap =null){
        if(!defined('appDir'))
           throw new BaseException('Please Define appDir in Main config.php', 500);

        if(!defined('SALT'))
           throw new BaseException('Please Define SALT in Main config.php', 500);

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
                throw new BaseException('Can not open '.$type.' '.$name.' in: '.$path);
           
        }catch(BaseException $e) {
            if(ini_get('display_errors') == "on"){
                echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br /> 
                Trace: '.$e->getTraceAsString();
                exit();
            }

            $routerConfig = Config::load('router');
            header("HTTP/1.0 400 Bad Request");
            echo $e->getMessage();
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