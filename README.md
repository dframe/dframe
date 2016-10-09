# Dframe - PHP Framework

The basic tools to build simple and complex pages. Used and tested internally for over 2 years in tens of projects.

1. [Installation](#installation)
2. [Overview](#overview)
3. [Extensions](#extensions) 
	- [Router](#dframerouter)	
	- [Session](#dframesession)
	- [Messages](#dframemessages)
	


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
     * @param array $data Dane do wyświetlenia
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

# Extensions

## Dframe\Config
You can fast set and load configs

Create file in app\config\myConfigFile.php
```php
<?php
return array(
    'key1' => 'value', 
    'key2' => array('value'),
    'key3' => array(
        'key1' => 'value'
    );
````

Usage in controller
```php
<?php
$config = \Dframe\Core\Config::load('myConfigFile');

echo $config->get('key1'); // display 'value'
echo $config->get('keyValid', 'yes'); // display 'yes' ||  if key is not exist then you can replace value
```
## Dframe\Router
Methods

Router is alredy defined in core dframe so you just use $this->router in controller or view files. If you want use only router component use $router = new Dframe\Router();

```php

$isActive = $this->router->isActive('page/index') // For check if you are on page
var_dump($isActive); // true/false

$publicWeb = $this->router->publicWeb('css/style.css') // For load web/* files
var_dump($publicWeb);  // http://example.com/css/style.css

$makeUrl = $this->router->makeUrl('page/index') // To create link
var_dump($makeUrl); // yourPage.com/page/index

$router->redirect('page/index'); // To redirect yourPage.com/page/index
```

## Dframe\Session
Methods
```php
$session  = new Session('HashSaltRandomForSession');
$session->register(); // Set session_id and session_time - default 60
$session->authLogin(); // Return true/false if session is registrer
$session->set($key, $value); // set $_SESSION[$key] = $value;
$session->get($key, $or = null); // get $_SESSION[$key]; 
$session->remove($key) // unset($_SESSION[$key]);
$session->end(); // session_destroy
```

## Dframe\Messages
Is a helpful class It helps to quickly add to the session messages that may display user
message Type: error, success, warning, info

Methods
```php
$msg = new Messages(new Session('HashSaltRandomForSession')); // Join the current session
$msg->add('s', 'Success Message!');
$msg->add('s', 'Success Message!', 'page/index'); // You can add redirect by Dframe\Router

$msg->hasMessages('success'): // Will return array['success']
$msg->hasMessages(): // Will return all array

$msg->clear('success'); // remove success msg
$msg->clear(); // remove all msg


var_dump($msg->display('success'));
```


License
----

MIT



### Tech

* [Wrapper PDO] - PHP PDO Class Wrapper ! (Base class - modified)
* [S.M.A.R.T.Y] - Default Template Engine (available: php, twig)

   [Wrapper PDO]: <https://github.com/neerajsinghsonu/PDO_Class_Wrapper>
   [S.M.A.R.T.Y]: <https://github.com/smarty-php/smarty>

