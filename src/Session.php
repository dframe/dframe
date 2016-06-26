<?php
namespace Dframe;

/*
Copyright (C) 2015  Sławomir Kaleta

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

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