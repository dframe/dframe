<?php 
# DEBUG configuration
if(in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
    ini_set("display_errors", "on"); # Debug setings
    error_reporting(E_ALL);          # Debug setings
} else {
    ini_set("display_errors", "on"); # Debug setings
    error_reporting(E_ALL);          # Debug setings
}   


# Website configuration
define('VERSION', "3.x Dframe"); # Version aplication
define('site_url', 'http://'.$_SERVER['HTTP_HOST']); # Url address
define('SALT', "YOURSALT123"); # SALT

# Database configuration
define('DB_HOST', ""); # Database Host (localhost)
define('DB_USER', ""); # Database Username
define('DB_PASS', ""); # Database Password
define('DB_DATABASE', ""); # Databese Name

# URL configuration
define('MOD_REWRITE', false); # Mod rewrite (ex. task=page&action=login -> page/login )

?>