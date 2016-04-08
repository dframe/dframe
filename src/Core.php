<?php
namespace Dframe;

abstract class Core
{
    public $baseClass = null;
    
    public function __construct($bootstrap){
        $this->baseClass = $bootstrap;
        $this->router = new \Dframe\Router($this->baseClass);
        
        // Tworzenie obiektu dostępnego w całym Dframe
        if(is_file('config/customLoad.php'))
            $this->customLoad = $this->loadConfig('customLoad')->get();

        return $this;
    }

    /*
     * Metoda do includowania pliku konficuracyjnego
     * 
    */
    public function loadConfig($file){
        return \Dframe\Config::load($file);
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
        $path = $type.'/'.$folder.$n.'.php';
        
        if(!empty($folder))
            $name = '\\'.$type.'\\'.str_replace(array('\\', '/'), '', $folder).'\\'.$name.$type;   
        else
        	$name = '\\'.$type.'\\'.$name.$type;


        try {
            if(is_file($path)) {
                include_once $path;
                $ob = new $name($this->baseClass);
                $ob->init();
            }else{
                throw new \Exception('Can not open '.$type.' '.$name.' in: '.$path);
            }
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
     * Metoda 
     * init dzialajaca jak __construct wywoływana na poczatku kodu
     * end identycznie tyle ze na końcu
     */

    public function init() {}
    public function end() {}
    
}
?>