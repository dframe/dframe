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

            if (!empty($data) AND !is_object($data)) {
                return Response::create($data)->display();
            } elseif (!empty($data) AND is_object($data) AND method_exists($data, 'display')) {
                return $data->display();
            }

        }

    }

    public function setView($view)
    {
        $this->view = $view;
    }

}