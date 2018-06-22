<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dframe\Session;
use Dframe\Messages;
use Dframe\Token;

class Bootstrap
{

    public function __construct()
    {

        $this->session = new Session('session_name'); // Best to set projectName
        $this->msg = new Messages($this->session);     // Default notify class
        $this->token = new Token($this->session);     // Default csrf token

        return $this;
    }

}
