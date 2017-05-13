<?php
namespace Dframe;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class Session
{
    
    function __construct($session_name){
        if(!isset($_SESSION)){
           session_name($session_name);
           session_start();
        }
    }

    public function register($time = 60){
        $_SESSION['session_id'] = session_id();
        $_SESSION['session_time'] = intval($time);
    }

    public function authLogin(){
        if(!empty($_SESSION['session_id']))
            return true;
        else 
            return false;

    }

    public function keyExists($key, $in = false){
        if(isset($in))
            $in = $_SESSION;

        if(array_key_exists($key, $in) == true)
            return true;
        
        return false;
    }
    
    public function set($key, $value){
        $_SESSION[$key] = $value;
    }

    public function get($key, $or = null){
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $or;
    }
    
    public function remove($key) {
        if(isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function end(){
        session_destroy();
        $_SESSION = array();
    }
       
}