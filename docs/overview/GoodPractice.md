# Goog Practice


Goood practice allow keep your code in harmony. Writing code do not tell youtself that he is only for you. Will come once a time that you will need help somone. So if your code will be unreadable it will take more time to analyze what's the problem is.

### Good tips
**Controller**

- Before loading View load first Model.
- Name of the variable create from name model and word Model (**$nameModel**)
- Variable view we use usually once so create just **$view**

```php
<?php

public function myMethod(){
    $firstModel = $this->loadModel('first');
    $secondModel = $this->loadModel('second');
    $view = $this->loadView('index');

}
```

If you have some validation access code try to do like this

```php
<?php

public function myProcetedMethod(){
    if($this->baseClass->session->authLogin() != true){
        $this->router->reditect('page/index');
        return;
    }

    $firstModel = $this->loadModel('first');
    $secondModel = $this->loadModel('second');
    $view = $this->loadView('index');
}
```


If you have some validation $_POST, $_GET with msg code try to do like this

```php
<?php

public function myProcetedAndPostMethod(){
    if($this->baseClass->session->authLogin() != true){
        $this->router->reditect('page/index');
        return;
    }

    if($_POST['someValue']){
        $this->baseClass->msg->add('s', 'No post.', 'page/index');
        return;
    }

    $firstModel = $this->loadModel('first');
    $secondModel = $this->loadModel('second');
    $view = $this->loadView('index');

}
```

But if you use json try to use like this
```php
<?php

public function myProcetedAndPostMethod(){
    if($this->baseClass->session->authLogin() != true){
        $this->router->reditect('page/index');
        return;
    }

    $firstModel = $this->loadModel('first');
    $secondModel = $this->loadModel('second');
    $view = $this->loadView('index');

    if(!isset($_POST['someValue']) AND !empty($_POST['someValue']))
        $view->renderJSON(array('return' => '1', 'response' => 'empty someVlue Post');

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

            $this->assign('router', $this->router);
```
If you use [Dframe\Message](../extensions/Messages.md) add
```php
            if($this->baseClass->msg->hasMessages('error'))
                $this->assign('msgError', $this->baseClass->msg->display('error'));
            elseif($this->baseClass->msg->hasMessages('success'))
                $this->assign('msgSuccess', $this->baseClass->msg->display('success'));
            elseif($this->baseClass->msg->hasMessages('warning'))
                $this->assign('msgWarning', $this->baseClass->msg->display('warning'));
            elseif($this->baseClass->msg->hasMessages('info'))
                $this->assign('msgInfo', $this->baseClass->msg->display('info'));
```
If you use [Dframe\Session](../extensions/Session.md) add
```php
            $this->assign('authLogin', $this->baseClass->session->authLogin());
```

Yes you can assign to View Model class for example generate url links or to set isset for permission user etc.
