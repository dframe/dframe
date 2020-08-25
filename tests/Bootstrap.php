<?php

require_once __DIR__ . '/../vendor/autoload.php';

define('APP_NAME', 'Dframe');

use Dframe\Router\Router;
use Dframe\Session\Session;
use Dframe\Messages;
use Dframe\Token\Token;

/**
 * Class Bootstrap
 */
class Bootstrap
{
     /**
      * @var
      */
     public $providers;
     /**
      * @var
      */
     public $modules;

     /**
      * @var \Dframe\Session
      */
     public $session;


     /**
      * @var \Dframe\Messages
      */
     public $msg;

     /**
      * @var \Dframe\Token
      */
     public $token;

     /**
      * Bootstrap constructor.
      *
      * @throws Exception
      */
     public function __construct()
     {
          $this->providers = [
                'core' => [
                     'router' => Router::class
                     //'debug' => \Dframe\Debug::class,
                ]
          ];

          $this->session = new Session('Test'); // Best to set project name
          $this->session->set('token', ['THIS_IS_TOKEN']);
          $this->session->set('timeToken', ['THIS_IS_TIME_TOKEN']);

          $this->msg = new Messages($this->session);      // Default notify cl
          $this->token = new Token($this->session);      // Default CSRF token

          return $this;
     }
}
