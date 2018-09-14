<?php

require_once __DIR__ . '/../vendor/autoload.php';

define('APP_NAME', 'Dframe');

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
