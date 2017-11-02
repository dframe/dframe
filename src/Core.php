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
 * @author Sławek Kaleta <slaszka@gmail.com>
 */
class Core
{
    
    public function run()
    {
        $router = new Router();
        $run = $router->run();

    }

    public function setView($view)
    {
        $this->view = $view;
    }

}
