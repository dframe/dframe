### Migration 4.0 to 4.1

Replace 
```php
use Dframe\Config;
use Dframe\Session;
use Dframe\Messages;
use Dframe\Token;
```

In to 
```php
use Dframe\Component\Config\Config;
use Dframe\Component\Session\Session;
use Dframe\Component\Messages\Messages;
use Dframe\Component\Token\Token;
```