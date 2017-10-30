## Method Init();

Its a good method, it's simple
```
public function __construct()
```
but allways you must use ``` parent::__construct()``` so you Can use **init**! simple

```php
public function init(){
    /**
      * Your own __construct without parent::__construct()
      * Just like that !
      */
}
```