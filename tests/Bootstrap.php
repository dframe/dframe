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
        ];

        $this->providers['baseClass'] = [
            'session' => \Dframe\Session::class, // Best to set projec
            'msg' => \Dframe\Messages::class,     // Default notify cl
            'token' => \Dframe\Token::class,     // Default csrf token
        ];

        $this->providers['modules'] = [
        ];

        return $this;
    }

}
