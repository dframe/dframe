<?php
namespace Dframe\View;
use Dframe\Config;
use Dframe\Router;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class defaultView implements \Dframe\View\interfaceView
{
    public function __construct(){
        $this->templateConfig = Config::load('view/defaultConfig');
    }

    public function assign($name, $value){
        $this->$name = $value;
    }

    public function fetch($name, $path=null){
        throw new \Exception('This module dont have fetch');
    }

    /**
     * Przekazuje kod do szablonu Smarty
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */
    public function renderInclude($name){

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        if($path == null)
            $path= $this->templateConfig->get('setTemplateDir').'/'.$folder.$name.$this->templateConfig->get('fileExtension', '.html.php');
        
        try{
            if(is_file($path))
                 include($path);                    
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