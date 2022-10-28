# Dframe/Cron - Component

[![Build Status](https://travis-ci.org/dframe/cron.svg?branch=master)](https://travis-ci.org/dframe/cron) [![Latest Stable Version](https://poser.pugx.org/dframe/cron/v/stable)](https://packagist.org/packages/dframe/cron) [![Total Downloads](https://poser.pugx.org/dframe/cron/downloads)](https://packagist.org/packages/dframe/cron) [![Latest Unstable Version](https://poser.pugx.org/dframe/cron/v/unstable)](https://packagist.org/packages/dframe/cron) [![License](https://poser.pugx.org/dframe/cron/license)](https://packagist.org/packages/dframe/cron)

![php framework dframe logo](https://dframeframework.com/img/logo_full.png)

### Documentation - [Cron PHP](https://dframeframework.com/en/docs/dframe/master/cron/overview)

### Composer

```sh
$ composer require dframe/cron
```

Cron
---------

Cron is a service to perform tasks periodically. It allows you to execute a command at a specified time. Sending emails
is such an example.

```php 
use Dframe\Cron\Task;
use Dframe\Router\Response;

set_time_limit(0);
ini_set('max_execution_time', 0);
date_default_timezone_set('Europe/Warsaw');

require_once dirname(__DIR__) . '/../../../vendor/autoload.php';
require_once dirname(__DIR__) . '/../../../web/config.php';

/**
 * An anonymous Cron class that calls itself
 */
return (new class() extends Task
{

    /**
     * Init function
     *
     * @return array
     */
    public function init()
    {
        $cron = $this->inLock('mail', [$this->loadModel('Mail/Mail'), 'sendMails'], []);
        if ($cron['return'] == true) {
            $mail = $cron['response'];
            return Response::renderJSON(['code' => 200, 'message' => 'Cron Complete', 'data' => ['mail' => ['data' => $mail['response']]]]);
        }

        return Response::renderJSON(['code' => 403, 'message' => 'Cron in Lock'])->status(403);

    }

})->init()->display();
```

Methods
---------

**lockTime(string $key, $ttl = 3600)**

Lock time

```php
if ($this->lockTime('mail')) {
    return Response::renderJSON(['code' => 403, 'message' => 'Time Lock'])->status(403);
}
```

**inLock(string $key, object $loadModel, string $method, $args = [], $ttl = 3600)**

This method has a built-in method that blocks it until complete.

```php
$this->inLock('mail', [$this->loadModel('Mail/Mail'), 'sendMails'], []);
```