<?php
namespace Dframe;
use Dframe\Router;

abstract class Controller extends Core
{
	    
    /**
     * Przekierowanie adresu 
     *
     * @param string CONTROLLER/MODEL?parametry
     *
     * @return void
     */
    
    public function redirect($url = '') {
        header("Location: ".$this->router->makeUrl($url));
        exit();
    }

	
}
?>