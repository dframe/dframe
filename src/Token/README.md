# Dframe/Token - Component
[![Build Status](https://travis-ci.org/dframe/token.svg?branch=master)](https://travis-ci.org/dframe/token) [![Latest Stable Version](https://poser.pugx.org/dframe/token/v/stable)](https://packagist.org/packages/dframe/token) [![Total Downloads](https://poser.pugx.org/dframe/token/downloads)](https://packagist.org/packages/dframe/token) [![Latest Unstable Version](https://poser.pugx.org/dframe/token/v/unstable)](https://packagist.org/packages/dframe/token) [![License](https://poser.pugx.org/dframe/token/license)](https://packagist.org/packages/dframe/token)

![php framework dframe logo](https://dframeframework.com/img/logo_full.png)

 
### Composer

```sh
$ composer require dframe/token
```

### Standalone

```php

$driver = new \Dframe\Component\Session\Session('sessionName');

/** 
 * $driver Can be any class implements interface \Psr\SimpleCache\CacheInterface 
 */
$token  = new \Dframe\Component\Token\Token($driver); 
$key = $token->generate('evidenceToken')->get('evidenceToken');  // Generate hash
$isValid = $token->isValid('evidenceToken', $key);            // Return true/false
$has = $token->has('evidenceToken');            // Return true/false
```

License
----

Open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)