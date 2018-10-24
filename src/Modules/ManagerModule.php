<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Modules;

/**
 * Manager Module
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */

class ManagerModule
{
    public $app;

    /**
     * ManagerModule constructor.
     *
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Load router.
     *
     * @param  string $path
     *
     * @return void
     */
    protected function loadRoutes($path)
    {
        $this->app->config['router']['routes'] = array_merge($this->app->config['router']['routes'] ?? [], (require $path)['routes']);
    }


    /**
     * Load model.
     *
     * @param  array $path
     *
     * @return void
     */
    protected function loadModels($path)
    {
        $this->app->config['model'] = array_unique(array_merge($this->app->config['model'] ?? [], $path));
    }

    /**
     * Load controller.
     *
     * @param  array $path
     *
     * @return void
     */
    protected function loadControllers($path)
    {
        $this->app->config['controller'] = array_unique(array_merge($this->app->config['controller'] ?? [], $path));
    }
}
