<?php
namespace Dframe;
use Dframe\Router;

class Core
{
    public $baseClass = null;
    
    public function __construct($bootstrap =null){
        if(!defined('appDir'))
           throw new \Exception('Please Define appDir in config.php');

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

    public function setView($engine){
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