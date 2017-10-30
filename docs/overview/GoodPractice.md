# Goog Practice


Goood practice allow keep your code in harmony. Writing code do not tell youtself that he is only for you. Will come once a time that you will need help somone. So if your code will be unreadable it will take more time to analyze what's the problem is.

### Good tips
**Controller**

- Before loading View load first Model.
- Name of the variable create from name model and word Model (**$NameModel**)
- Variable view we use usually once so create just **$view**

```php

public function myMethod(){
    $firstModel = $this->loadModel('First');
    $secondModel = $this->loadModel('Second');
    $view = $this->loadView('Index');
}
```

If you have some validation access code try to do like this

```php

public function myProcetedMethod(){
    if($this->baseClass->session->authLogin() != true){
        return $this->router->reditect('page/index');
    }

    $firstModel = $this->loadModel('First');
    $secondModel = $this->loadModel('Second');
    $view = $this->loadView('Index');
}
```


If you have some validation $_POST, $_GET with msg code try to do like this

```php

public function myProcetedAndPostMethod(){
    if ($this->baseClass->session->authLogin() != true) {
        return $this->router->reditect('page/index');
    }

    if (isset($_POST['someValue'])) {
        return $this->baseClass->msg->add('s', 'Yes Post!.', 'page/index');
    }

    $firstModel = $this->loadModel('First');
    $secondModel = $this->loadModel('Second');
    $view = $this->loadView('Index');

}
```

But if you use json try to use like this
```php

public function myProcetedAndPostMethod(){
    if ($this->baseClass->session->authLogin() != true) {
        return $this->router->reditect('page/index');
    }

    $firstModel = $this->loadModel('First');
    $secondModel = $this->loadModel('Second');
    $view = $this->loadView('Index');

    if (!isset($_POST['someValue']) AND !empty($_POST['someValue'])) {
        return $view->renderJSON(array('return' => '1', 'response' => 'empty someVlue Post');
    }

}
```
Custom Response ?
```php
    public function customHeader() 
    {
        $view = $this->loadView('Index');
        $view->assign('contents', 'Example assign');
        return Response::create($view->fetch('index'))
            ->status(200)
            ->header([
                'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT', 
                'Cache-Control' => 'no-cache',
                'Pragma', 'no-cache'
             ])
             
            
    }
```


**View**

In view not a lot is happend. In standard View/index.php should be assign should for front-developer.

```php
<?php
namespace View;
use Dframe\Config;

class IndexView extends \View\View
{

    public function init(){
        if(isset($this->router)){
            $this->assign('router', $this->router);
        }
```
If you use [Dframe\Message](../extensions/Messages.md) add
```php
        if ($this->baseClass->msg->hasMessages('error')) {
            $this->assign('msgError', $this->baseClass->msg->display('error'));
        } elseif ($this->baseClass->msg->hasMessages('success')) {
            $this->assign('msgSuccess', $this->baseClass->msg->display('success'));
        } elseif ($this->baseClass->msg->hasMessages('warning')) {
            $this->assign('msgWarning', $this->baseClass->msg->display('warning'));
        } elseif ($this->baseClass->msg->hasMessages('info')) {
            $this->assign('msgInfo', $this->baseClass->msg->display('info'));
        }
```
If you use [Dframe\Session](../extensions/Session.md) add
```php
    $this->assign('authLogin', $this->baseClass->session->authLogin());
```

Yes you can assign to View Model class for example generate url links or to set isset for permission user etc.