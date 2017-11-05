# Dframe - PHP Framework 

[![Build Status](https://travis-ci.org/dframe/dframe.svg?branch=master)](https://travis-ci.org/dframe/dframe) [![Latest Stable Version](https://poser.pugx.org/dframe/dframe/v/stable)](https://packagist.org/packages/dframe/dframe) [![Total Downloads](https://poser.pugx.org/dframe/dframe/downloads)](https://packagist.org/packages/dframe/dframe) [![Latest Unstable Version](https://poser.pugx.org/dframe/dframe/v/unstable)](https://packagist.org/packages/dframe/dframe) [![License](https://poser.pugx.org/dframe/dframe/license)](https://packagist.org/packages/dframe/dframe)

The basic tools to build simple and complex pages.

1. [Installation](#installation)
2. [Overview](#overview)
	- [Good Practice](docs/overview/GoodPractice.md)

3. Extensions
	- [Router](docs/extensions/Router.md)
	- [Session](docs/extensions/Session.md)
	- [Messages](docs/extensions/Messages.md)

### Website [dframeframework.com](http://dframeframework.com/pl/page/index)   
Language
[Polish](http://dframeframework.com/pl/page/index) | [English](http://dframeframework.com/en/page/index) - coming soon

### Installation

```sh
$ composer require dframe/dframe
```

Before run add to .htaccess 

```sh
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ web/$1

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ web/index.php [QSA,L]
```

### Overview

**1. Controller** 
file it is very important for dynamic routing. If you created file **TaskForRouter.php** with class with method **ActionForRouter()** your **\Dframe\Router** url will look like ***yourpage.com/TaskForRouter/ActionForRouter***

app/Controller/TaskForRouter.php:
```php
<?php
namespace Controller;

Class TaskForRouterController extends \Controller\Controller
{
    public function ActionForRouter(){
         $exampleModel = $this->loadModel('Example'); #Load model
         $view = $this->loadView('Index'); #Load view
         
         $getId = $exampleModel->getId($_GET['id']); #Call method
         $view->assign('varForSmarty', $getId); #Set variable to view
         return $view->render('exampleNameFile'); #Generate view app/View/templates/exampleNameFile.tpl
    }
}


```

**2. Model** 
is not required in project if you are not using any databases

app/Model/ExampleModel.php:
```php
<?php
namespace Model;

Class ExampleModel extends Model\Model
{
    public function getId($id){
        return $this->baseClass->db->pdoQuery('SELECT * FROM table WHERE id = ? LIMIT 1', array($id))->result();
    }
}
```

**3. View** 
receiving data from Controller and can display more advanced template. You dont have to use view if you using dframe only for **xml**/**json**/**jsonp** it can do controler without templates files

You can use **php**, **twig**, **smarty* or write own View engine 

```php
<?php
namespace Dframe\View;

interface ViewInterface
{

    /**
     * Set the var to the template
     *
     * @param string $name 
     * @param string $value
     *
     * @return void
     */

    public function assign($name, $value);

    /**
     * Return code
     *
     * @param string $name - Filename
     * @param string $path - Alternative Path
     *
     * @return void
     */
    
    public function fetch($name, $path=null);

    /**
     * Include File
     */
    public function renderInclude($path);
     
    /**
     * Display JSON.
     * @param array $data
     */
    public function renderJSON($data);
 
    /**
     * Display JSONP.
     * @param array $data
     */
    public function renderJSONP($data);

}
```
S.M.A.R.T.Y example
View/templates/exampleNameFile.html.php:
```html
<html>
    <head>
        <title>SmartyNews!</title>
    </head>
<body>
    <h3>{$varForSmarty.title}</h3>
    <p>Napisał {$varForSmarty.autor} dnia {$varForSmarty.data}</p>
    <p>{$varForSmarty.description|truncate:200:"..."}</p>
</body>
</html>
```


License
----

MIT



### Tech
[Dframe\Database](https://github.com/dusta/Database)- PHP PDO Class Wrapper
[S.M.A.R.T.Y](https://github.com/smarty-php/smarty) - Default Template Engine (available: php, twig)
