<?php
namespace Dframe;
use Dframe\BaseException;
use Dframe\Config;
use Dframe\Core;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE (MIT)
 */

class Loader extends Core
{

    private $controller;
    private $action;
    private $urlvalues;
    public $bootstrap;

    public function __construct($bootstrap = null)
    {

        if (!defined('appDir') AND !defined('APP_DIR')) {
            throw new BaseException('Please Define appDir in Main config.php', 500);
        }
        
        /* Backward compatibility */
        if (defined('appDir') AND !defined('APP_DIR')) {
            define('APP_DIR', appDir);
        }
        
        if (!defined('SALT')) {
            throw new BaseException('Please Define SALT in Main config.php', 500);
        }

        $this->baseClass = empty($bootstrap) ? new \Bootstrap() : $bootstrap;

        if (isset($this->baseClass->router)) { 
            $this->router = $this->baseClass->router;
        }
        
        return $this;
    }


    /**
     *   Metoda do includowania pliku modelu i wywołanie objektu przez namespace
     */

    public function loadModel($name)
    {
        return $this->loadObject($name, 'Model');
    }

    /**
     *   Metoda do includowania pliku widoku i wywołanie objektu przez namespace
     */

    public function loadView($name)
    {
        return $this->loadObject($name, 'View');

    }

    private function loadObject($name, $type)
    {

        if (!in_array($type, (array('Model', 'View')))) {
            return false;
        }

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
        
        $n = str_replace($type, '', $name);
        $path = APP_DIR.$type.'/'.$folder.$n.'.php';
        try {

            if (!$this->isCamelCaps($name)) {
                 throw new BaseException('Camel Sensitive is on. Can not use '.$type.' '.$name.' try to use camelCaseName');
            }
            
            $name = !empty($folder) ? '\\'.$type.'\\'.str_replace(array('\\', '/'), '\\', $folder).$name.$type : '\\'.$type.'\\'.$name.$type;;   
    
            if (!is_file($path)) {
                throw new BaseException('Can not open '.$type.' '.$name.' in: '.$path);
            }

            include_once $path;
            $ob = new $name($this->baseClass);
            if (method_exists($ob, 'init')) {
                $ob->init(); 
            }
           
        }catch(BaseException $e) {
            
            if (ini_get('display_errors') == "on") {
                echo '<pre>';
                echo 'Accept: '.$_SERVER['HTTP_ACCEPT'].'<br>';
                echo 'Referer: '.$_SERVER['HTTP_REFERER'].'<br><br>';
                echo 'Request Method: '.$_SERVER['REQUEST_METHOD'].'<br><br>';

                echo 'Current file Path: <b>'.$this->router->currentPath().'</b><br>';

                echo 'Message: <b>'.$e->getMessage().'</b><br><br>';
                echo 'File Exception: '.$e->getFile().':'.$e->getLine().'<br><br>';
                echo 'Trace: <br>'.$e->getTraceAsString().'<br>';
                echo '</pre>';
                exit();
            }

            $routerConfig = Config::load('router');
            $router->response()->status('400');

            if (isset($routerConfig->get('error/400')[0])) {
                $this->router->redirect($routerConfig->get('error/400')[0]);

            } elseif (isset($routerConfig->get('error/404')[0])) {
                $this->router->redirect($routerConfig->get('error/404')[0]);

            }

            exit();
        }

        return $ob;
    }


    // Establish the requested controller as an object
    public function loadController($controller)
    {

        $subControler = null;
        if (strstr($controller, ",") !== false) {

            $url = explode(',', $controller);
            $urlCount = count($url)-1;
            $subControler = '';
            
            for ($i=0; $i < $urlCount; $i++) { 
                $subControler .= $url[$i].'/';
            }

            $controller = $url[$urlCount];
        }
        // Does the class exist?
        $patchController = APP_DIR.'Controller/'.$subControler.$controller.'.php'; 
        //var_dump($patchController);
        if (file_exists($patchController)) {
            include_once $patchController;
            $path = null;
        }

        $xsubControler = str_replace("/", "\\", $subControler);
        try {

            if (!class_exists('\Controller\\'.$xsubControler.''.$controller.'Controller')) {
                throw new BaseException('Bad controller error');
            }

            $controller = '\Controller\\'.$xsubControler.''.$controller.'Controller';
            $returnController = new $controller($this->baseClass);

        }catch(BaseException $e) {
            
            if (ini_get('display_errors') == 'on') {
                echo $e->getMessage().'<br><br>
                File: '.$e->getFile().'<br>
                Code line: '.$e->getLine().'<br> 
                Trace: '.$e->getTraceAsString();
                exit();
            }

            $routerConfig = Config::load('router');
            header("HTTP/1.0 404 Not Found");

            if (isset($routerConfig->get('error/404')[0])) {
                $this->router->redirect($routerConfig->get('error/404')[0]);
            }

            exit();
        }
        
        return $returnController;
    }

    public static function isCamelCaps($string, $classFormat=false, $public=true, $strict=true)
    {

        // Check the first character first.
        if ($classFormat === false) {
            $legalFirstChar = '';
            if ($public === false) {
                $legalFirstChar = '[_]';
            }

            if ($strict === false) {
                // Can either start with a lowercase letter, 
                // or multiple uppercase
                // in a row, representing an acronym.
                $legalFirstChar .= '([A-Z]{2,}|[a-z])';
            } else {
                $legalFirstChar .= '[a-z]';
            }
        } else {
            $legalFirstChar = '[A-Z]';
        }

        if (preg_match("/^$legalFirstChar/", $string) === 0) {
            return false;
        }

        // Check that the name only contains legal characters.
        $legalChars = 'a-zA-Z0-9';
        if (preg_match("|[^$legalChars]|", substr($string, 1)) > 0) {
            return false;
        }

        if ($strict === true) {
            // Check that there are not two capital letters 
            // next to each other.
            $length          = strlen($string);
            $lastCharWasCaps = $classFormat;

            for ($i = 1; $i < $length; $i++) {
                $ascii = ord($string{$i});
                if ($ascii >= 48 && $ascii <= 57) {
                    // The character is a number, so it cant be a capital.
                    $isCaps = false;
                } else {
                    if (strtoupper($string{$i}) === $string{$i}) {
                        $isCaps = true;
                    } else {
                        $isCaps = false;
                    }
                }

                if ($isCaps === true && $lastCharWasCaps === true) {
                    return false;
                }

                $lastCharWasCaps = $isCaps;
            }
        }//end if

        return true;

    }//end isCamelCaps()


    /** 
     * Metoda 
     * init dzialajaca jak __construct wywoływana na poczatku kodu
     * end identycznie tyle ze na końcu
     */

    public function init()
    {

    }

    public function end()
    {

    }

}