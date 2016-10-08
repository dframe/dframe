# Dframe - PHP Framework

The basic tools to build simple and complex pages. Used and tested internally for over 2 years. Used in tens of projects.

### Installation

```sh
$ composer require dframe/dframe
```

Before run add to .htaccess 

```sh
RewriteEngine On
RewriteCond %{REQUEST_URI} !web/
RewriteRule (.*) web/$1 [L]
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
         $exampleModel = $this->LoadModel('example'); #Załadowanie Modelu
         $view = $this->LoadView('index'); #Ładowanie Widoku
         
         $getId = $exampleModel->getId($_GET['id']); #Wywołanie metody 
         $view->assign('varForSmarty', $getId); #Przekazanie zmiennej do view
         $view->render('exampleNameFile'); #Wygenerowanie pliku app/View/templates/exampleNameFile.tpl
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


```php

$isActive = $router->isActive('page/index') // For check if you are on page
var_dump($isActive); // true/false

$publicWeb = $router->publicWeb('css/style.css') // For load web/* files
var_dump($publicWeb);  // http://example.com/css/style.css

$makeUrl = $router->makeUrl('page/index') // To create link
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
License
----

MIT



### Tech

* [Wrapper PDO] - PHP PDO Class Wrapper ! (Base class - modified)
* [S.M.A.R.T.Y] - Default Template Engine (available: php, twig)

   [Wrapper PDO]: <https://github.com/neerajsinghsonu/PDO_Class_Wrapper>
   [S.M.A.R.T.Y]: <https://github.com/smarty-php/smarty>

