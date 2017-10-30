## Dframe\Messages
Is a helpful class It helps to quickly add to the session messages that may display user

You have two type params for **adding** *(First)* and for **display** *(Second)* message. First type consists of **e**, **s**, **w**, **i** and second of **error**, **success**, **warning**, **info**



##### Methods example #1
Just display after add.
```php
use Dframe\Messages;
use Dframe\Session;

include_once 'vendor/autoload.php';

$msg = new Messages(new Session('hashSaltRandomForSession')); // Join the current session
$msg->add('s', 'Success Message!');
//$msg->add('s', 'Success Message!', 'page/index'); // with redirect 
$msg->hasMessages('success'): // Will return array['success']
$msg->hasMessages(): // Will return all array

$msg->clear('success'); // remove success msg
$msg->clear(); // remove all msg

var_dump($msg->display('success'));
```


##### Methods example #2*
Set message and refresh/redirect
```php
use Dframe\Messages;
use Dframe\Router;
use Dframe\Session;

include_once 'vendor/autoload.php';

$router = new Router();
$msg = new Messages(new Session('hashSaltRandomForSession')); // Join the current session

if(!empty($msg->hasMessages())){
   var_dump($msg->display('success'));
   var_dump($msg->display('error'));
   var_dump($msg->display('info'));
   var_dump($msg->display('warning'));
   die();
}

$msg->add('s', 'Success Message!'); 
$msg->add('e', 'Error Message!'); 
$msg->add('i', 'Info Message!'); 
$msg->add('w', 'Warning Message!');

$router->redirect('page/index');

```

*Not Recommended if you have just one msg and after that you break code return and redirect client to other page

##### Methods example #3

```php
use Dframe\Messages;
use Dframe\Session;

include_once 'vendor/autoload.php';

$msg = new Messages(new Session('hashSaltRandomForSession')); // Join the current session

if(isset($msg->hasMessages())){
   var_dump($msg->display('success'));
   die();
}

$msg->add('s', 'Success Message!', 'page/index');
return;

```