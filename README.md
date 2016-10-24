# Dframe - PHP Framework

[![Latest Stable Version](https://poser.pugx.org/dframe/dframe/v/stable)](https://packagist.org/packages/dframe/dframe) [![Total Downloads](https://poser.pugx.org/dframe/dframe/downloads)](https://packagist.org/packages/dframe/dframe) [![Latest Unstable Version](https://poser.pugx.org/dframe/dframe/v/unstable)](https://packagist.org/packages/dframe/dframe) [![License](https://poser.pugx.org/dframe/dframe/license)](https://packagist.org/packages/dframe/dframe)

The basic tools to build simple and complex pages. Used and tested internally for over 2 years in tens of projects.

1. [Installation](#installation)
2. [Overview](#overview)
	- [Good Practice](docs/overview/GoodPractice.md)

3. Extensions
	- [Router](docs/extensions/Router.md)
	- [Session](docs/extensions/Session.md)
	- [Messages](docs/extensions/Messages.md)

### Installation

```sh
$ composer require dframe/dframe
```

Before run add to .htaccess 

```sh
RewriteEngine On
RewriteCond %{REQUEST_URI} !web/
RewriteRule (.*) web/$1 [L]
RewriteRule ^web/([^/]*)/([^/]*)$ web/index.php?task=$1&action=$2 [L]
```

### Overview

**1. Controller** 
file it is very important for dynamic routing. If you created file **taskForRouter.php** with class with method **ActionForRouter()** your **\Dframe\Router** url will look like ***yourpage.com/taskForRouter/ActionForRouter***

app/Controller/taskForRouter.php:
```php
<?php
namespace Controller;

Class taskForRouterController extends \Controller\Controller
{
    public function ActionForRouter(){
         $exampleModel = $this->loadModel('example'); #Load model
         $view = $this->loadView('index'); #Load view
         
         $getId = $exampleModel->getId($_GET['id']); #Call method
         $view->assign('varForSmarty', $getId); #Set variable to view
         $view->render('exampleNameFile'); #Generate view app/View/templates/exampleNameFile.tpl
    }
}


```

**2. Model** 
is not required in project if you are not using any databases

app/Model/exampleModel.php:
```php
<?php
namespace Model;

Class exampleModel extends Model\Model
{
    public function getId($id){
        return $this->baseClass->db->pdoQuery('SELECT * FROM table WHERE id=? LIMIT 1', array($id))->results();
    }
}
```

**3. View** 
receiving data from Controller and can display more advanced template. You dont have to use view if you using dframe only for **xml**/**json**/**jsonp** it can do controler without templates files

You can use **php**, **twig**, **smarty* or write own View engine 

```php
<?php
namespace Dframe\View;

interface interfaceView
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
View/templates/exampleNameFile.hmtl.php:
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
