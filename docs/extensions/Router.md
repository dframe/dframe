## Dframe\Router
Methods

Router is alredy defined in core dframe so you just use $this->router in controller or view files. If you want use only router component use $router = new Dframe\Router();

```php

$isActive = $this->router->isActive('page/index') // For check if you are on page
var_dump($isActive); // true/false

$publicWeb = $this->router->publicWeb('img/example.jpg') // For load web/* files
var_dump($publicWeb);  // http://example.com/img/example.jpg

$assets = $this->router->assets('ventor/js/exampleJs.js') // For load app/view/* files and cache web/assets/*
var_dump($assets);  // http://example.com/assets/exampleJs.js

$makeUrl = $this->router->makeUrl('page/index') // To create link
var_dump($makeUrl); // yourPage.com/page/index

$this->router->setHttps(true) // reinit from Config

$router->redirect('page/index'); // To redirect yourPage.com/page/index
```
