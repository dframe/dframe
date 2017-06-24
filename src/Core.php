<?php
namespace Dframe;
use Dframe\Router;
/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class Core
{
    
    public function run(){

        $router = new Router();
        return $router->run();

    }

}