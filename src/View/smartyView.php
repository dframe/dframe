<?php
namespace Dframe\View;
use Dframe\Config;
use Dframe\Router\Response;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class smartyView implements \Dframe\View\interfaceView
{
    public function __construct(){
        $smartyConfig = Config::load('view/smarty');

        $smarty = new \Smarty;
        $smarty->debugging = $smartyConfig->get('debugging', false);;
        $smarty->setTemplateDir($smartyConfig->get('setTemplateDir'))
                ->setCompileDir($smartyConfig->get('setCompileDir'));
        
        $this->smarty = $smarty;
    }

    public function assign($name, $value) {
        try {
            if($this->smarty->getTemplateVars($name) !== null)
                throw new \Exception('You can\'t assign "'.$name . '" in Smarty');
            
            $assign = $this->smarty->assign($name, $value);

        }catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }

        return $assign;
    }

    public function fetch($name, $path=null) {
        $smartyConfig = Config::load('view/smarty');

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
        
        if($path == null)
            $path = $smartyConfig->get('setTemplateDir').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');

        try {
        	
            if(!is_file($path))
            	throw new \Exception('Can not open template '.$name.' in: '.$path);

            $fetch = $this->smarty->fetch($path); // Ładowanie widoku

        }catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }

        return $fetch;
    } 

    /**
     * Przekazuje kod do szablonu Smarty
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */
    public function renderInclude($name, $path = null) {

        $smartyConfig = Config::load('view/smarty');
        
        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        if($path == null)
           $path= $smartyConfig->get('setTemplateDir').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');
        
        try{
            if(is_file($path))
                $this->smarty->display($path); // Ładowanie widoku
            else
                throw new \Exception('Can not open template '.$name.' in: '.$path);

        }catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }
    }
     
    /**
     * Wyświetla dane JSON.
     * @param array $data Dane do wyświetlenia
     */

    public function renderJSON($data, $status = false) {
        $router = new Router();
        $router->response()->status($status)->header(array('Content-Type' => 'application/json'));
        return json_encode($data);
    }
 
    /**
     * Wyświetla dane JSONP.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSONP($data) {
        header('Content-Type: application/json');
        $callback = null;
        if(isset($_GET['callback'])) 
            $callback = $_GET['callback'];
        
        echo $callback . '(' . json_encode($data) . ')';
        exit();
    }

}