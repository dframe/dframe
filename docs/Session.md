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