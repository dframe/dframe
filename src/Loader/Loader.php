<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Loader;

use Bootstrap;
use Dframe\Config\Config;
use Dframe\Loader\Exceptions\LoaderException;
use Dframe\Router\Response;
use Dframe\Router\Router;
use Exception;

/**
 * Loader Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Loader
{
    /**
     * Path logs
     */
    protected const LOG_DIR = APP_DIR . 'View/cache/logs/';

    /**
     * @var Router
     */
    public Router $router;

    /**
     * @var Bootstrap|null
     */
    public $baseClass;

    /**
     * @var string
     */
    protected string $fileExtension = '.php';

    /**
     * @var string
     */
    protected string $namespaceSeparator = '\\';

    /**
     * Loader constructor.
     *
     * @param null|object|Bootstrap $bootstrap
     *
     * @throws LoaderException
     */
    public function __construct($bootstrap = null)
    {
        spl_autoload_register([$this, 'autoload']);

        if (!defined('APP_DIR')) {
            throw new LoaderException('Please Define APP_DIR in Main config.php', 500);
        }
        if (!defined('SALT')) {
            throw new LoaderException('Please Define SALT in Main config.php', 500);
        }

        $this->baseClass = empty($bootstrap) ? new Bootstrap() : $bootstrap;

        $baseClass = $this->baseClass;
        foreach ($baseClass->providers['core'] ?? [] as $key => $value) {
            $this->$key = new $value($this);
            if (method_exists($this->$key, 'boot') or is_callable([$this->$key, 'boot'])) {
                $this->$key->boot($this);
            }
        }

        if (is_null($bootstrap)) {
            foreach ($baseClass->providers['baseClass'] ?? [] as $key => $value) {
                $this->baseClass->$key = new $value($this->baseClass);
            }

            $this->baseClass->modules = (object)[];
            foreach ($baseClass->providers['modules'] ?? [] as $key => $value) {
                $this->baseClass->modules->$key = new $value($this);
                $this->baseClass->modules->$key->register();
                $this->baseClass->modules->$key->boot($this->baseClass->modules->$key->app);
            }
        } else {
            foreach ($this->baseClass->providers['modules'] ?? [] as $key => $value) {
                foreach ($baseClass->providers['core'] ?? [] as $key2 => $value2) {
                    if (method_exists($this->$key2, 'boot') or is_callable([$this->$key2, 'boot'])) {
                        $this->$key2->boot($this->baseClass->modules->$key->app);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param $class
     *
     * @return bool|mixed
     * @throws LoaderException
     */
    public static function autoload($class)
    {
        $args = $class;

        if (substr($class, -4) == "View") {
            $class = substr($class, 0, -4);
        } elseif (substr($class, -5) == "Model") {
            $class = substr($class, 0, -5);
        } elseif (substr($class, -10) == "Controller") {
            $class = substr($class, 0, -10);
        } else {
            return false;
        }

        $directory = explode('/', str_replace('\\', '/', ltrim($class, '\\')));
        $class = array_pop($directory);
        $directory = array_merge($directory, explode('/', str_replace('_', '/', $class)));
        $class = array_pop($directory);
        $directory = rtrim(APP_DIR . join('/', $directory), '/');

        if (!empty($class)) {
            if (is_file($path = $directory . '/' . $class . '.php')) {
                return require_once $path;
            }

            if (realpath(__DIR__ . '/' . $directory)) {
                throw new LoaderException('Couldn\'t locate ' . $args);
            }
        }
    }

    /**
     * Model Loader
     *
     * @param string      $name
     * @param null|string $namespace
     *
     * @return mixed
     */
    public function loadModel($name, $namespace = null)
    {
        return $this->loadObject($name, 'Model', $namespace);
    }

    /**
     * Loading files
     *
     * @param string      $name
     * @param string      $type
     * @param null|string $namespace
     *
     * @return mixed
     */
    protected function loadObject($name, $type, $namespace = null)
    {
        try {
            if (!$this->isCamelCaps($name)) {
                if (!defined('CODING_STYLE') or (defined('CODING_STYLE') and CODING_STYLE === true)) {
                    throw new LoaderException(
                        'Camel Sensitive is on. Can not use ' . $type . ' ' . $name . ' try to use StudlyCaps or CamelCase'
                    );
                }
            }

            if (!in_array($type, (['Model', 'View']))) {
                return false;
            }

            if (!empty($namespace)) {
                $name = '\\' . $namespace . '\\' . $type . '\\' . $name;
            } else {
                $name = $namespace . '\\' . $type . '\\' . $name . $type;
            }

            $name = str_replace(DIRECTORY_SEPARATOR, $this->namespaceSeparator, $name);
            $name = str_replace('/', $this->namespaceSeparator, $name);

            $ob = new $name($this->baseClass);
            if (method_exists($ob, 'start')) {
                $ob->start();
            }
            if (method_exists($ob, 'init')) {
                $ob->init();
            }
        } catch (LoaderException $e) {
            if (ini_get('display_errors') === "1") {
                $msg = null;
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

                return Response::create($msg)->display();
            }

            $routes = Config::load('router')->get('routes');
            if (!empty($routes['error/:code'])) {
                return Response::redirect('error/:code?code=400', 400)->display();
            }

            return Response::create()->status(500)->display();
        }

        return $ob;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public static function isCamelCaps($string): bool
    {
        preg_match_all('/^(?\'isCamelCaps\'(([A-Z]|([\\\\|\/\/][A-Z]))[a-zA-Z]+)+)$/', $string, $matches);

        return !empty($matches['isCamelCaps']);
    }

    /**
     * Method init that works like __construct called at the beginning of the code.
     */
    public function init()
    {
    }

    /**
     * View Loader
     *
     * @param string      $name
     * @param null|string $namespace
     *
     * @return mixed
     */
    public function loadView($name, $namespace = null)
    {
        return $this->loadObject($name, 'View', $namespace);
    }

    /**
     * Establish the requested controller as an object.
     *
     * @param string      $controller
     * @param null|string $namespace
     *
     * @return mixed
     */
    public function loadController($controller, $namespace = null)
    {
        try {
            $subController = null;

            if (strstr($controller, ',') !== false) {
                $url = explode(',', $controller);
                $urlCount = count($url) - 1;
                $subController = '';

                for ($i = 0; $i < $urlCount; $i++) {
                    $subController .= (!defined('CODING_STYLE') or
                        (defined('CODING_STYLE') and CODING_STYLE === true)) ?
                        ($url[$i]) . DIRECTORY_SEPARATOR : $url[$i] . DIRECTORY_SEPARATOR;
                }

                $controller = $url[$urlCount];
            }

            if (!defined('CODING_STYLE') or (defined('CODING_STYLE') and CODING_STYLE === true)) {
                $controller = ucfirst($controller);
            }

            $controller = str_replace(DIRECTORY_SEPARATOR, $this->namespaceSeparator, $controller);

            if (!empty($namespace) && $namespace == '\\') {
                $load = $controller;
            } elseif (!empty($namespace)) {
                $class = '\\' . $namespace . '\\Controller\\' . $subController . $controller;
                $load = str_replace('/', $this->namespaceSeparator, $class);
            } else {
                $load = $this->namespaceSeparator . 'Controller' . $this->namespaceSeparator . $subController . $controller . 'Controller';
                $load = str_replace(DIRECTORY_SEPARATOR, $this->namespaceSeparator, $load);
            }

            if (isset($this->debug)) {
                $this->debug->addHeader(['X-DF-Debug-Controller' => $load]);
            }

            $controller = new $load($this->baseClass);
        } catch (Exception $e) {
            return $this->processLoadControllerException($e);
        }

        return $controller;
    }

    /**
     * @param $e Exception
     *
     * @return string|null
     * @throws Exception
     */
    protected function processLoadControllerException($e): ?string
    {
        if (ini_get('display_errors') === "1") {
            if (PHP_SAPI === 'cli') {
                throw new Exception($e->getMessage());
            } else {
                $msg = '<pre>';
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

                return Response::create($msg)->display();
            }
        }

        $routes = Config::load('router')->get('routes');

        if (!empty($routes['error/:code'])) {
            return Response::redirect('error/:code?code=400', 400)->display();
        }

        return Response::create()->status(500)->display();
    }

    /**
     * A method that works like __destruct called at the end of the code.
     */
    public function end()
    {
    }
}
