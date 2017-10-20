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
        return $this->response($router->run());

    }
    
    public function response($data){
    	echo $data;
    }

    public function setView($view){
    	$this->view = $view;
    }

}