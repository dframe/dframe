# Dframe/Sesssion - Component

[![Build Status](https://travis-ci.org/dframe/session.svg?branch=master)](https://travis-ci.org/dframe/session) [![Latest Stable Version](https://poser.pugx.org/dframe/session/v/stable)](https://packagist.org/packages/dframe/session) [![Total Downloads](https://poser.pugx.org/dframe/session/downloads)](https://packagist.org/packages/dframe/session) [![Latest Unstable Version](https://poser.pugx.org/dframe/session/v/unstable)](https://packagist.org/packages/dframe/session) [![License](https://poser.pugx.org/dframe/session/license)](https://packagist.org/packages/dframe/session)

![php framework dframe logo](https://dframeframework.com/img/logo_full.png)

### Documentation - [Session PHP](https://dframeframework.com/en/docs/dframe/master/session/overview)

### Composer

```sh
$ composer require dframe/session
```

### Standalone

```php
use Dframe\Session\Session;
$this->session = new Session('name');
$session = new Session('HashSaltRandomForSession');
$session->register();                        // Set session_id and session_time - default 60
$session->authLogin();                        // Return true/false if session is registered
$session->set($key, $value);                   // set $_SESSION[$key] = $value;
$session->get($key, $or = null);                // get $_SESSION[$key];
$session->remove($key);                       // unset($_SESSION[$key]);
$session->end();                            // session_destroy
```

License
----

Open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

