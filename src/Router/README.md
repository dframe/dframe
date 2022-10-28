# Dframe/Router - Component

[![Build Status](https://travis-ci.org/dframe/router.svg?branch=master)](https://travis-ci.org/dframe/router) [![Latest Stable Version](https://poser.pugx.org/dframe/router/v/stable)](https://packagist.org/packages/dframe/router) [![Total Downloads](https://poser.pugx.org/dframe/router/downloads)](https://packagist.org/packages/dframe/router) [![Latest Unstable Version](https://poser.pugx.org/dframe/router/v/unstable)](https://packagist.org/packages/dframe/router) [![License](https://poser.pugx.org/dframe/router/license)](https://packagist.org/packages/dframe/router)

![php framework dframe logo](https://dframeframework.com/img/logo_full.png)

### Documentation - [Router PHP](https://dframeframework.com/en/docs/dframe/master/router/overview)

### Composer

```sh
$ composer require dframe/router
```

Simple PHP Router
---------

Creating an application, it's worth taking care of their friendly links. Its has a big part in position in SEO. Link
router work in a similar way as network router. It is responsible for calling the method from controller.

```php

$this->router->addRoute([
'page/:method' => [
'page/[method]/',
'task=page&action=[method]'
]
]);
$this->router->makeUrl('page/:action?action=index'); // Return: https://example.php/page/index
$this->router->isActive('page/:action?action=index'); // Current Website true/false
```

Configuration
===========

We define the table with adresses for our application in the configuration file

- **|https|** - true/false forcing https
- **|NAME_CONTROLLER|** - Name of the default controller
- **|NAME_METHOD|** - Name of the default method from the controller in NAME_CONTROLLER
- **|publicWeb|** - Main folder from which files will be read (js, css)
- **|assetsPath|** - Dynamic folder

- **|docs/docsId|** - Example routing with the |docsId| variable, which contains the |docs/[docsId]/| adress definition
  and the |task| parameters to which it's assigned.
- **|error/404|** - as above
- **|default|** - default definition loading the controller/method. |params| defines the possibility of additional
  parameters appearing, while

```php
'_params' => [
'[name]/[value]/',
'[name]=[value]'
]
````

defines the way the additional foo=bar parameters should be interpreted.

**Config/router.php**

```php
 
 return [
     'https' => false,
     'NAME_CONTROLLER' => 'page',    // Default Controller for router
     'NAME_METHOD' => 'index',       // Default Action for router
     'publicWeb' => '',              // Path for public web (web or public_html)
 
     'assets' => [
         'minifyCssEnabled' => true,
         'minifyJsEnabled' => true,
         'assetsDir' => 'assets',
         'assetsPath' => APP_DIR.'View/',
         'cacheDir' => 'cache',
         'cachePath' => APP_DIR.'../web/',
         'cacheUrl' => HTTP_HOST.'/',
     ],
 
     'routes' => [
         'docs/:page' => [
             'docs/[page]/', 
             'task=Page&action=[page]&type=docs'
         ],
         
         'methods/example/:exampleId' => [
            'methods/example/[exampleId]',
            'methods' => [
                'GET' => 'task=Methods,Example&action=get&exampleid=[exampleId]',
                'POST' => 'task=Methods,Example&action=post&exampleid=[exampleId]',
            ]
         ],
         
         'error/:code' => [
             'error/[code]/', 
             'task=Page&action=error&type=[code]',
             'code' => '([0-9]+)',
             'args' => [
                 'code' => '[code]'
             ],
         ],
         
        ':task/:action' => [
            '[task]/[action]/[params]',
            'task=[task]&action=[action]',
            'params' => '(.*)',
            '_params' => [
                '[name]/[value]/',
                '[name]=[value]'
            ]
        ],

         'default' => [
             '[task]/[action]/[params]',
             'task=[task]&action=[action]',
             'params' => '(.*)',
             '_params' => [
                 '[name]/[value]/', 
                 '[name]=[value]'
             ]
         ]
     ] 
 
 ];
```

Controller
-------------

- makeUrl - is used for generating the full adress. For example |makeurl| - method used for redirections, equivalent of
  |header| but with a parameter being a key from the Config/router.php table. In case of using docs/:docsld it looks as
  the following |redirect|

**Controller/Page.php**

```php
 namespace Controller;
 
 use Dframe\Controller;
 use Dframe\Router\Response;
 
 class PageController extends Controller
 {
 
     /**
      * @return bool
      */
     public function index()
     {
         echo $this->router->makeUrl('docs/:docsId?docsId=23');
         return;
     }
 
     /**
      * @return mixed
      */
     public function docs()
     {
 
         if (!isset($_GET['docsId'])) {
             return $this->router->redirect('error/:code?code=404');
         }
     }
 
     /**
      * @param string $status
      *
      * @return mixed
      */
     public function error($status = '404')
     {
         $routerCodes = $this->router->response();
 
         if (!array_key_exists($status, $routerCodes::$code)) {
             return $this->router->redirect('error/:code?code=500');
         }
 
         $view = $this->loadView('index');
         $smartyConfig = Config::load('view/smarty');
 
         $patchController = $smartyConfig->get('setTemplateDir', APP_DIR . 'View/templates') . '/errors/' . htmlspecialchars($status) . $smartyConfig->get('fileExtension', '.html.php');
 
         if (!file_exists($patchController)) {
             return $this->router->redirect('error/:code?code=404');
         }
 
         $view->assign('error', $routerCodes::$code[$status]);
         return Response::create($view->fetch('errors/' . htmlspecialchars($status)))->headers(['refresh' => '4;' . $this->router->makeUrl(':task/:action?task=page&action=index')]);
     }
 
 }
 ```

View
-------------

assign - it's a method of the template engine that assignes value to a variable which is used in the template files.

**View/templates/index.html.php**

```php
https://
```

Using only PHP

- |router| all already available methods used like in |page/index|

**View/index.php**

```php
namespace View;

use Dframe\Asset\Assetic;

class IndexView extends \View\View
{

     /**
      * @return bool
      */
     public function init()
     {
         $this->router->assetic = new Assetic();
         $this->assign('router', $this->router);
     }
}
```

Dframe\Router\Response;
------

Extention of the basic **Dframe\Router** is **Dframe\Router\Response**, adding functionality of setting the response
status (404, 500, etc.) and their headers.

```php
return Response::create('Hello Word!')
    ->status(200)
    ->headers([
                  'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
                  'Cache-Control' => 'no-cache',
                  'Pragma',
                  'no-cache'
              ]);
```

For generating html.

Render json

```php
return Response::renderJSON(['code' => 200, 'data' => []]);
```

Render json with callback

```php
return Response::renderJSONP(['code' => 200, 'data' => []]);
```

Redirect

```php
return Response::redirect(':task/:action?task=page&action=login');
```