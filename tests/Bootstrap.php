<?php
use Dframe\Session;
use Dframe\Messages;
use Dframe\Token;

$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
$autoloader->addPsr4('Dframe\tests\\', __DIR__);

class Bootstrap
{
    
    public function __construct(){

        $this->session  = new Session('session_name'); // Best to set projectName
        $this->msg = new Messages($this->session);     // Default notify class
        $this->token  = new Token($this->session);     // Default csrf token

        return $this;
    }

}