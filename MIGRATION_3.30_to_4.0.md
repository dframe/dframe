### Migration 3.30 to 4.00

Add in *web/config.php* new const **APP_NAME** 
#[c2170a8](https://github.com/dframe/dframe-demo/commit/c2170a8e83c7d73a9926c62e97a4bba680a24bf6)

```php
define('APP_NAME', "dframe_demo");   // Project Name
```

Add in *app/bootstrap.php*
#[1530c85](https://github.com/dframe/dframe-demo/commit/1530c8561befa1fb46811aa8960959209445d47f)

```php
use Dframe\Router;
$this->providers['core'] = [
    'router' => Router::class,
];

```
