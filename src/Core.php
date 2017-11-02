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
