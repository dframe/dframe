# Dframe\Config

You can fast set and load configs

Create file in app\config\myConfigFile.php
```php
<?php
return [
    'key1' => 'value', 
    'key2' => ['value'],
    'key3' => [
        'key1' => 'value'
    ];
````

Usage in controller
```php
<?php
use Dframe\Config;

include_once 'vendor/autoload.php';

$config = Config::load('myConfigFile');

echo $config->get('key1'); // display 'value'
echo $config->get('keyValid', 'yes'); // display 'yes' ||  if key is not exist then you can replace value
```
