ROUTER

before 
```php
return array(
    'NAME_CONTROLLER' => 'page',
    'NAME_METHOD' => 'index',
    'publicWeb' => '',
    'assetsPath' => 'assets',
    'default' => array(
        '[task]/[action]/[params]',
        'task=[task]&action=[action]',
        'params' => '(.*)',
        '_params' => array(
            '[name]/[value]/', 
            '[name]=[value]'
            )
        ), 
        
```

After

```php

return array(
    'NAME_CONTROLLER' => 'page',
    'NAME_METHOD' => 'index',
    'publicWeb' => '',

    'assets' => array(
	    'minifyCssEnabled' => true,
	    'minifyJsEnabled' => true,
	    'assetsDir' => 'assets',
	    'assetsPath' => APP_DIR.'View/',
	    'cacheDir' => 'cache',
	    'cachePath' => APP_DIR.'../web/',
	    'cacheUrl' => HTTP_HOST.'/',
    ),
    
    'routes' => array(        
        'default' => array(
            '[task]/[action]/[params]',
            'task=[task]&action=[action]',
            'params' => '(.*)',
            '_params' => array(
                '[name]/[value]/', 
                '[name]=[value]'
                )
            )
            
````
