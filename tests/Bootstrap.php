<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dframe\Session;
use Dframe\Messages;
use Dframe\Token;

class Bootstrap
{

    public function __construct()
    {


        $this->providers['core'] = [
            'router' => \Dframe\Router::class,
            //'debug' => \Dframe\Debug::class,
        ];

        $this->session = new \Dframe\Session('Test'); // Best to set projec
        $this->msg = new \Dframe\Messages($this->session);     // Default notify cl
        $this->token = new \Dframe\Token($this->session);     // Default csrf token


        return $this;
    }

}
