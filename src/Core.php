<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\Router;
use Dframe\Router\Response;

/**
 * Core Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Core
{

    public function __construct()
    {

        if (!defined('APP_DIR')) {
            throw new BaseException('Please Define appDir in Main config.php', 500);
        }

        if (!defined('SALT')) {
            throw new BaseException('Please Define SALT in Main config.php', 500);
        }

        if (ini_get('display_errors') == "on") {
            $this->debug = new Debug();
        }

        $baseClass = empty($bootstrap) ? new \Bootstrap() : $bootstrap;
        $this->baseClass = (object)[];

        foreach ($baseClass->providers['core'] ?? [] as $key => $value) {
            $this->$key = new $value($this);
        }

        foreach ($baseClass->providers['baseClass'] ?? [] as $key => $value) {
            $this->baseClass->$key = new $value($this->baseClass);
        }

        foreach ($baseClass->providers['modules'] ?? [] as $key => $value) {
            $this->$key = new $value($this);
            $this->$key->register();
            $this->$key->boot();
        }

        return $this;
    }

    /**
     * Display Controller result
     * 
     * @param boolen|Response
     * 
     */
    public function run($controller = null, $action = null, $args = [])
    {
        $this->router->setUp($this);
        if (is_null($controller ?? null) and is_null($action ?? null)) {
            $this->router->parseGets();
            $controller = $this->router->controller;
            $action = $this->router->action;
            $namespace = $this->router->namespace;
        }

        $arg = $this->router->parseArgs;

        $response = [];
        $loader = new Loader($this);
        $loadController = $loader->loadController($controller, $namespace); // Loading Controller class
        $controllerObject = $loadController->returnController;
        $response = [];

        if (method_exists($controllerObject, 'start')) {
            $response[] = ['start', []];
        }

        if (method_exists($controllerObject, 'init')) {
            $response[] = ['init', []];
        }

        if (method_exists($controllerObject, $action) or is_callable([$controllerObject, $action])) {
            $response[] = [$action, $args];
        }

        if (method_exists($controllerObject, 'end')) {
            $response[] = ['end',[]];
        }

        foreach ($response as $key => $data) {
            $run = call_user_func_array([$controllerObject, $data[0]], $data[1]);
            if ($run instanceof Response) {
                if (isset($this->debug)) {
                    $this->debug->addHeader(['X-DF-Debug-Controller' => $controller]);
                    $this->debug->addHeader(['X-DF-Debug-Method' => $action]);
                    
                    $run->headers($this->debug->getHeader());
                }

                return $run->display();
            }

        }

        return true;
    }

}
