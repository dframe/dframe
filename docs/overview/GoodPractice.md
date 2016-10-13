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
    if($haveAccess != true){
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
    if($haveAccess != true){
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
    if($haveAccess != true){
        $this->router->reditect('page/index');
        return;
    }

    $firstModel = $this->loadModel('first');
    $secondModel = $this->loadModel('second');
    $view = $this->loadView('index');

    if(!$_POST['someValue'])
        $view->renderJSON(array('return' => '1', 'response' => 'empty someVlue Post');

}
```

