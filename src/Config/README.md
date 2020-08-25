# Dframe/Config - Component
[![Build Status](https://travis-ci.org/dframe/config.svg?branch=master)](https://travis-ci.org/dframe/config) [![Latest Stable Version](https://poser.pugx.org/dframe/config/v/stable)](https://packagist.org/packages/dframe/config) [![Total Downloads](https://poser.pugx.org/dframe/config/downloads)](https://packagist.org/packages/dframe/config) [![Latest Unstable Version](https://poser.pugx.org/dframe/config/v/unstable)](https://packagist.org/packages/dframe/config) [![License](https://poser.pugx.org/dframe/config/license)](https://packagist.org/packages/dframe/config)

![php framework dframe logo](https://dframeframework.com/img/logo_full.png)

### Documentation - [Config PHP](https://dframeframework.com/en/docs/dframe/master/config/overview)
 
### Composer

```sh
$ composer require dframe/config
```

### Standalone

```php
use Dframe\Component\Config\Config;

include_once 'vendor/autoload.php';
$configPath = __DIR__. DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR;

$config = Config::load('ConfigFile', $configPath);
$config->get('key1'); // Return value
$config->get('keyValid', 'yes'); // return 'yes' ||  if key does not exist then you can replace value
```

License
----

Open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
