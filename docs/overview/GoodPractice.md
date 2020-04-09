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

Custom Response ?
```php
    public function customHeader() 
    {
        $view = $this->loadView('Index');
        $view->assign('contents', 'Example assign');
        return Response::create($view->fetch('index'))
            ->status(200)
            ->headers([
                'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT', 
                'Cache-Control' => 'no-cache',
                'Pragma', 'no-cache'
             ]);
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


Yes you can assign to View Model class for example generate url links or to set isset for permission user etc.
