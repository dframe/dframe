# Dframe/DframeFramework

The basic tools to build simple and complex pages. Used and tested internally for over 2 years. Used in tens of projects.

### Tech

* [Wrapper PDO] - PHP PDO Class Wrapper ! (Base class - modified)
* [S.M.A.R.T.Y] - Default Template Engine (available: php, twig)


### Installation

Before run add to .htaccess 

```sh
RewriteEngine On
RewriteCond %{REQUEST_URI} !web/
RewriteRule (.*) web/$1 [L]
```



### Example Usage

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

   [Wrapper PDO]: <https://github.com/neerajsinghsonu/PDO_Class_Wrapper>
   [S.M.A.R.T.Y]: <https://github.com/smarty-php/smarty>
