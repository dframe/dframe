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
 * Short Description
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Core
{
    public $app;

    public function __construct($bootstrap = null)
    {
        if (!defined('APP_DIR')) {
            throw new BaseException('Please Define appDir in Main config.php', 500);
        }

        if (!defined('SALT')) {
            throw new BaseException('Please Define SALT in Main config.php', 500);
        }


        $this->baseClass = empty($bootstrap) ? new \Bootstrap() : $bootstrap;

        foreach ($bootstrap->providers as $key => $value) {
            $var = new $value($this->baseClass);
            $var->register();
            $var->boot();
        }

        $this->router = new Router($this->baseClass);
        $this->router->assetic = new \Dframe\Asset\Assetic($this->baseClass);

        if (isset($this->baseClass->router)) {
            $this->router = $this->baseClass->router;
            if (isset($this->baseClass->assetic)) {
                $this->router->assetic = $this->baseClass->assetic;
            }
        }

        return $this;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

}
