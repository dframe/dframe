<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\Router\Response;

/**
 * Core Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Core extends Loader
{

    /**
     * Display Controller result
     *
     * @param null  $controller
     * @param null  $action
     * @param array $args
     *
     * @return mixed
     */
    public function run($controller = null, $action = null, $args = [])
    {
        $this->router = $this->router->boot($this);

        if (is_null($controller ?? null) and is_null($action ?? null)) {
            $this->router->parseGets();
            $controller = $this->router->controller;
            $action = $this->router->action;
            $namespace = $this->router->namespace;
        }

        $arg = $this->router->parseArgs;

        $loader = new Loader($this->baseClass);
        $loadController = $loader->loadController($controller, $namespace ?? '\\'); // Loading Controller class

        if (isset($loadController->returnController)) {
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
                $response[] = ['end', []];
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
        }

        return true;
    }
}
