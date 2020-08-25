<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\Loader\Loader;
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
     * @param null|string $controller
     * @param null|string $action
     * @param array       $args
     *
     * @return mixed
     */
    public function run($controller = null, $action = null, $args = [])
    {
        $this->router = $this->router->boot();

        if (is_null($controller ?? null) and is_null($action ?? null)) {
            $parseGets = $this->router->parseGets();
            $args = $parseGets['args'];
            $controller = $this->router->controller;
            $action = $this->router->action;
            $namespace = $this->router->namespace;
        }

        $loader = new Loader($this->baseClass);

        $Controller = $loader->loadController($controller, $namespace ?? '\\');
        $response = [];

        if (method_exists($Controller, 'start')) {
            $response[] = ['start', []];
        }

        if (method_exists($Controller, 'init')) {
            $response[] = ['init', []];
        }

        if (method_exists($Controller, $action) or is_callable([$Controller, $action])) {
            $response[] = [$action, $args];
        }

        if (method_exists($Controller, 'end')) {
            $response[] = ['end', []];
        }

        foreach ($response as $key => $data) {
            $run = call_user_func_array([$Controller, $data[0]], $data[1]);
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
