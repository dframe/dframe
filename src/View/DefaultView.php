<?php 
/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\View;

use Dframe\Config;
use Dframe\Router;

/**
 * Short Description
 *
 * @author Sławek Kaleta <slaszka@gmail.com>
 */
class DefaultView implements \Dframe\View\ViewInterface
{
    
    public function __construct()
    {
        $this->templateConfig = Config::load('view/defaultConfig');
    }

    /**
     * Set the var to the template
     *
     * @param string $name 
     * @param string $value
     *
     * @return void
     */
    public function assign($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Return code
     *
     * @param string $name Filename
     * @param string $path Alternative Path
     *
     * @return void
     */
    public function fetch($name, $path = null)
    {
        throw new \Exception('This module dont have fetch');
    }

    /**
     * Przekazuje kod do szablonu Smarty
     *
     * @param string $name
     * @param string $path
     *
     * @return void
     */
    public function renderInclude($name)
    {

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        if ($path == null) {
            $path = $this->templateConfig->get('setTemplateDir').'/'.$folder.$name.$this->templateConfig->get('fileExtension', '.html.php');
        }
        
        try{
            if (!is_file($path)) {
                throw new \Exception('Can not open template '.$name.' in: '.$path);
            }
            
            $renderInclude = include $path;           

        }catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }
        
        return $renderInclude;
    }

    /**
     * Display JSON.
     *
     * @param array $data
     * @param int   $status
     *
     * @return Json
     */
    public function renderJSON($data, $status = false) 
    {
        $router = new Router();
        $router->response()->status($status)->header(array('Content-Type' => 'application/json'));
        return json_encode($data);
    }
 
    /**
     * Display JSONP.
     *
     * @param array $data
     *
     * @return Json with Calback
     */
    public function renderJSONP($data) 
    {
        header('Content-Type: application/json');
        $callback = null;
        if (isset($_GET['callback'])) { 
            $callback = $_GET['callback'];
        }
        
        return $callback . '(' . json_encode($data) . ')';
    }

}