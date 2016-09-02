<?php
namespace Dframe;
use \Dframe\Session;

/**
 * Copyright (C) 2016  
 * @author Paweł Łopyta
 * @author Sławomir Kaleta
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class Token
{
    protected $session;
    
    protected $token = array();
    
    protected $time = array();
    
    // Tylko dla obiektu \Dframe\Session
    public function __construct(Session $session) {
        $this->session = $session;
        
        $token = $this->session->get('token');
        if(!empty($token))
            $this->token = $token;

        $timeToken = $this->session->get('timeToken');

        if(!empty($timeToken))
            $this->time = $timeToken;
    }
    
    public function generate($name) {
        $this->setToken($name, md5(uniqid(rand(), true)));
        $this->setTime($name, time() + 3600);
        return $this;
    }
    
    public function setToken($name, $token) {
        $this->token[$name] = $token;
        $this->session->set('token', $this->token);
        return $this;
    }
    
    public function getToken($name) {
        if(isset($this->token[$name]) && $this->getTime($name) >= time())
            return $this->token[$name];
        return $this->generate($name)->token[$name];
    }
    
    public function remove($name) {
        if(isset($this->token[$name]))
            unset($this->token[$name]);
        
        if(isset($this->time[$name]))
            unset($this->time[$name]);
            
        $this->session->set('token', $this->token);
        $this->session->set('timeToken', $this->time);
    }
    
    public function setTime($name, $time) {
        if(isset($this->token[$name])) {
            $this->time[$name] = intval($time);
            $this->session->set('timeToken', $this->time);
        }
        return $this;
    }
    
    public function getTime($name) {
        return isset($this->time[$name]) ? $this->time[$name] : null;
    }
    
    public function isValid($name, $token, $remove = true) {
        if($this->getToken($name) === $token) {
            if($remove === true)
                $this->remove($name);
            return true;
        }
        return false;
    }   
}