<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\BaseException;
use Dframe\Config;
use Dframe\Core;
use Dframe\Router\Response;

/**
 * Short Description
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Loader extends Core
{

    public $baseClass;
    public $router;
    private $_fileExtension = '.php';
    private $_namespaceSeparator = '\\';


    /**
     * Metoda do includowania pliku modelu i wywołanie objektu przez namespace
     *
     * @param string $name
     *
     * @return object
     */
    public function loadModel($name, $namespace = null)
    {
        return $this->_loadObject($name, 'Model', $namespace);
    }

    /**
     * Metoda do includowania pliku widoku i wywołanie objektu przez namespace
     *
     * @param string $name
     *
     * @return object
     */
    public function loadView($name, $namespace = null)
    {
        return $this->_loadObject($name, 'View', $namespace);
    }

    /**
     * Metoda do includowania pliku widoku i wywołanie objektu przez namespace
     *
     * @param string $name
     * @param string $type
     *
     * @return object
     */
    private function _loadObject($name, $type, $namespace = null)
    {

        if (!empty($namespace)) {

            $name = '\\' . $namespace . '\\Model\\' . $name;
            $ob = new $name($this->baseClass);
            if (method_exists($ob, 'init')) {
                $ob->init();
            }

            return $ob;
        }
        //var_dump($this->namespace);

        if (!in_array($type, (array('Model', 'View')))) {
            return false;
        }

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        $n = str_replace($type, '', $name);
        $path = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, APP_DIR . $type . '/' . $folder . $n . '.php');

        try {

            if (!$this->isCamelCaps($name, true)) {
                if (!defined('CODING_STYLE') or (defined('CODING_STYLE') and CODING_STYLE == true)) {
                    throw new BaseException('Camel Sensitive is on. Can not use ' . $type . ' ' . $name . ' try to use StudlyCaps or CamelCase');
                }
            }

            $name = !empty($folder) ? $this->_namespaceSeparator . $type . $this->_namespaceSeparator . str_replace(array($this->_namespaceSeparator, '/'), $this->_namespaceSeparator, $folder) . $name . $type : $this->_namespaceSeparator . $type . $this->_namespaceSeparator . $name . $type;;
            if (!is_file($path)) {
                throw new BaseException('Can not open ' . $type . ' ' . $name . ' in: ' . $path);
            }

            include_once $path;
            $ob = new $name($this->baseClass);
            if (method_exists($ob, 'init')) {
                $ob->init();
            }

        } catch (BaseException $e) {
            $msg = null;
            if (ini_get('display_errors') == "on") {
                $msg .= '<pre>';
                $msg .= 'Message: <b>' . $e->getMessage() . '</b><br><br>';

                $msg .= 'Accept: ' . $_SERVER['HTTP_ACCEPT'] . '<br>';
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $msg .= 'Referer: ' . $_SERVER['HTTP_REFERER'] . '<br><br>';
                }

                $msg .= 'Request Method: ' . $_SERVER['REQUEST_METHOD'] . '<br><br>';
                $msg .= 'Current file Path: <b>' . $this->router->currentPath() . '</b><br>';
                $msg .= 'File Exception: ' . $e->getFile() . ':' . $e->getLine() . '<br><br>';
                $msg .= 'Trace: <br>' . $e->getTraceAsString() . '<br>';
                $msg .= '</pre>';

                exit($msg);
            }


            $routerConfig = Config::load('router');
            $routes = $routerConfig->get('routes');

            if (!empty($routes['error/:code'])) {
                return Response::redirect('error/:code?code=400', 400)->display();
            }

            return '_loadObject Error';
        }

        return $ob;
    }


    /**
     * Establish the requested controller as an object
     *
     * @param string $controller
     */

    public function loadController($controller, $namespace = null)
    {

        if (!empty($namespace)) {
            $class = '\\' . $namespace . '\\Controller\\' . $controller;
            return new $class($this->baseClass);
        }

        $subControler = null;
        if (strstr($controller, ",") !== false) {
            $url = explode(',', $controller);
            $urlCount = count($url) - 1;
            $subControler = '';

            for ($i = 0; $i < $urlCount; $i++) {
                if (!defined('CODING_STYLE') or (defined('CODING_STYLE') and CODING_STYLE == true)) {
                    $subControler .= ucfirst($url[$i]) . DIRECTORY_SEPARATOR;
                } else {
                    $subControler .= $url[$i] . DIRECTORY_SEPARATOR;
                }
            }

            $controller = $url[$urlCount];
        }

        if (!defined('CODING_STYLE') or (defined('CODING_STYLE') and CODING_STYLE == true)) {
            $controller = ucfirst($controller);
        }

        $controller = str_replace(DIRECTORY_SEPARATOR, $this->_namespaceSeparator, $controller);
        $path = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, APP_DIR . 'Controller' . DIRECTORY_SEPARATOR . $subControler . $controller . '.php');


        try {
            if (!is_file($path)) {
                throw new BaseException('Can not open Controller ' . $controller . ' in: ' . $path);
            }

            include_once $path;

            $xsubControler = str_replace(DIRECTORY_SEPARATOR, $this->_namespaceSeparator, $subControler);
            if (!class_exists($this->_namespaceSeparator . 'Controller' . $this->_namespaceSeparator . $xsubControler . '' . $controller . 'Controller')) {
                throw new BaseException('Bad controller error');
            }

            $controller = $this->_namespaceSeparator . 'Controller' . $this->_namespaceSeparator . $xsubControler . '' . $controller . 'Controller';
            $returnController = new $controller($this->baseClass);
        } catch (BaseException $e) {
            $msg = null;
            if (ini_get('display_errors') == 'on') {
                $msg .= '<pre>';
                $msg .= 'Message: <b>' . $e->getMessage() . '</b><br><br>';

                $msg .= 'Accept: ' . $_SERVER['HTTP_ACCEPT'] . '<br>';
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $msg .= 'Referer: ' . $_SERVER['HTTP_REFERER'] . '<br><br>';
                }

                $msg .= 'Request Method: ' . $_SERVER['REQUEST_METHOD'] . '<br><br>';
                $msg .= 'Current file Path: <b>' . $this->router->currentPath() . '</b><br>';
                $msg .= 'File Exception: ' . $e->getFile() . ':' . $e->getLine() . '<br><br>';
                $msg .= 'Trace: <br>' . $e->getTraceAsString() . '<br>';
                $msg .= '</pre>';

                exit($msg);
            }

            $routerConfig = Config::load('router');
            $routes = $routerConfig->get('routes');

            if (!empty($routes['error/:code'])) {
                return Response::redirect('error/:code?code=400', 400)->display();
            }

            return 'loadController Error';
        }

        return $returnController;
    }
    /**
     *
     * @param string  $string
     * @param boolean $classFormat
     * @param boolean $public
     * @param boolean $strict
     */
    public static function isCamelCaps($string, $classFormat = false, $public = true, $strict = true)
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
            $length = strlen($string);
            $lastCharWasCaps = $classFormat;

            for ($i = 1; $i < $length; $i++) {
                $ascii = ord($string {
                    $i});
                if ($ascii >= 48 && $ascii <= 57) {
                    // The character is a number, so it cant be a capital.
                    $isCaps = false;
                } else {
                    if (strtoupper($string {
                        $i}) === $string {
                        $i}) {
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
     */
    public function init()
    {
    }

    /**
     * Metoda
     * dzialajaca jak __destruct wywoływana na koncu kodu
     */
    public function end()
    {
    }

}
