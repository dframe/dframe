<?php
namespace Dframe;
use Dframe\Router;
use Dframe\Router\Response;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE (MIT)
 */

class Core
{
    
    public function run()
    {
        $router = new Router();
        $run = $router->run();

        foreach ($run as $key => $data) {
            if (!is_object($data)) {
                Response::create($data)->display();
            } elseif (is_object($data)) {
                $data->display();
            }
        }

    }

    public function setView($view)
    {
        $this->view = $view;
    }

}