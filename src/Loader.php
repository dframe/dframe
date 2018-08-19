<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\Loader\Exceptions\LoaderException;

/**
 * Loader Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Loader extends Core
{
    public $baseClass;
    public $router;
    private $fileExtension = '.php';
    private $namespaceSeparator = '\\';

    public function __construct($bootstrap = null)
    {
        if (!defined('APP_DIR')) {
            throw new LoaderException('Please Define appDir in Main config.php', 500);
        }

        if (!defined('SALT')) {
            throw new LoaderException('Please Define SALT in Main config.php', 500);
        }

        $this->baseClass = empty($bootstrap) ? new \Bootstrap() : $bootstrap;

        if (isset($this->baseClass->router)) {
            $this->router = $this->baseClass->router;
        }

        return $this;
    }

    /**
     * Metoda do includowania pliku modelu i wywołanie objektu przez namespace.
     *
     * @param string $name
     *
     * @return object
     */
    public function loadModel($name)
    {
        return $this->loadObject($name, 'Model');
    }

    /**
     * Metoda do includowania pliku widoku i wywołanie objektu przez namespace.
     *
     * @param string $name
     *
     * @return object
     */
    public function loadView($name)
    {
        return $this->loadObject($name, 'View');
    }

    /**
     * Metoda do includowania pliku widoku i wywołanie objektu przez namespace.
     *
     * @param string $name
     * @param string $type
     *
     * @return object
     */
    private function loadObject($name, $type)
    {
        if (!in_array($type, (['Model', 'View']))) {
            return false;
        }

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        $n = str_replace($type, '', $name);
        $path = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, APP_DIR . $type . '/' . $folder . $n . '.php');

        try {
            if (!$this->isCamelCaps($name, true)) {
                if (!defined('CODING_STYLE') or (defined('CODING_STYLE') and CODING_STYLE == true)) {
                    throw new LoaderException('Camel Sensitive is on. Can not use ' . $type . ' ' . $name . ' try to use StudlyCaps or CamelCase');
                }
            }

            $name = !empty($folder) ? $this->namespaceSeparator . $type . $this->namespaceSeparator . str_replace([$this->namespaceSeparator, '/'], $this->namespaceSeparator, $folder) . $name . $type : $this->namespaceSeparator . $type . $this->namespaceSeparator . $name . $type;
            if (!is_file($path)) {
                throw new LoaderException('Can not open ' . $type . ' ' . $name . ' in: ' . $path);
            }

            include_once $path;
            $ob = new $name($this->baseClass);
            if (method_exists($ob, 'init')) {
                $ob->init();
            }
        } catch (LoaderException $e) {
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

            if (isset($routerConfig->get('error/400')[0])) {
                return $this->router->redirect($routerConfig->get('error/400')[0], 400);
            } elseif (isset($routerConfig->get('error/404')[0])) {
                return $this->router->redirect($routerConfig->get('error/404')[0], 404);
            }

            return 'loadObject Error';
        }

        return $ob;
    }

    /**
     * Establish the requested controller as an object.
     *
     * @param string $controller
     */
    public function loadController($controller)
    {
        $subControler = null;
        if (strstr($controller, ',') !== false) {
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

        $controller = str_replace(DIRECTORY_SEPARATOR, $this->namespaceSeparator, $controller);
        $path = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, APP_DIR . 'Controller' . DIRECTORY_SEPARATOR . $subControler . $controller . '.php');

        try {
            if (!is_file($path)) {
                throw new LoaderException('Can not open Controller ' . $controller . ' in: ' . $path);
            }

            if (isset($this->baseClass->router->debug)) {
                $this->baseClass->router->debug->addHeader(['X-DF-Debug-File' => $path]);
                $this->baseClass->router->debug->addHeader(['X-DF-Debug-Controller' => $controller]);
            }

            include_once $path;

            $xsubControler = str_replace(DIRECTORY_SEPARATOR, $this->namespaceSeparator, $subControler);
            if (!class_exists($this->namespaceSeparator . 'Controller' . $this->namespaceSeparator . $xsubControler . '' . $controller . 'Controller')) {
                throw new LoaderException('Bad controller error');
            }

            $controller = $this->namespaceSeparator . 'Controller' . $this->namespaceSeparator . $xsubControler . '' . $controller . 'Controller';
            $this->returnController = new $controller($this->baseClass);
        } catch (LoaderException $e) {
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

            if (isset($routerConfig->get('error/400')[0])) {
                return $this->router->redirect($routerConfig->get('error/400')[0], 400);
            } elseif (isset($routerConfig->get('error/404')[0])) {
                return $this->router->redirect($routerConfig->get('error/404')[0], 404);
            }

            return 'loadController Error';
        }

        return $this;
    }

    /**
     * @param string $string
     * @param bool   $classFormat
     * @param bool   $public
     * @param bool   $strict
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
                $ascii = ord($string[$i]);
                if ($ascii >= 48 and $ascii <= 57) {
                    // The character is a number, so it cant be a capital.
                    $isCaps = false;
                } else {
                    if (strtoupper(
                        $string[$i]
                    ) === $string[$i]) {
                        $isCaps = true;
                    } else {
                        $isCaps = false;
                    }
                }

                if ($isCaps === true and $lastCharWasCaps === true) {
                    return false;
                }

                $lastCharWasCaps = $isCaps;
            }
        }//end if

        return true;
    }

    //end isCamelCaps()

    /**
     * Metoda
     * init dzialajaca jak __construct wywoływana na poczatku kodu.
     */
    public function init()
    {
    }

    /**
     * Metoda
     * dzialajaca jak __destruct wywoływana na koncu kodu.
     */
    public function end()
    {
    }
}
