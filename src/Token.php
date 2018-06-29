<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

/**
 * Token Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */

class Token 
{

    protected $driver;
    protected $token = [];
    protected $time = [];

    /**
     * constructor.
     *
     * @param string $driver
     * @param array  $config
     */

    public function __construct($baseClass)
    {
        $this->driver = $baseClass->session;
        if (!$this->driver instanceof \Psr\SimpleCache\CacheInterface) {
            throw new \Exception("This class Require instance Of Dframe\Session", 1);
        }

        $token = $this->driver->get('token');
        if (!empty($token)) {
            $this->token = $token;
        }

        $timeToken = $this->driver->get('timeToken');
        if (!empty($timeToken)) {
            $this->time = $timeToken;
        }
    }

    /**
     * @param string $key
     * @param null   $default
     * 
     * @return mixed
     */

    public function get($key, $default = null)
    {
        if (isset($this->token[$key]) and $this->getTime($key) >= time()) {
            return $this->token[$key];
        }

        return $this->generate($key)->token[$key];
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param null   $ttl
     * 
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $this->token[$key] = $value;
        $this->driver->set('token', $this->token);
        return $this;
    }

    public function delete($key)
    {

        if (isset($this->token[$key])) {
            unset($this->token[$key]);
        }

        if (isset($this->time[$key])) {
            unset($this->time[$key]);
        }

        $this->driver->set('token', $this->token);
        $this->driver->set('timeToken', $this->time);
    }

    public function clear()
    {
        $this->token = [];
        $this->time = [];

        $this->driver->set('token', $this->token);
        $this->driver->set('timeToken', $this->time);
    }

    public function getMultiple($keys, $default = null)
    {
    }

    public function setMultiple($values, $ttl = null)
    {
    }

    public function deleteMultiple($keys)
    {
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function has($key)
    {
        return $this->isValid($key);
    }


    public function generate($key)
    {
        $this->set($key, md5(uniqid(rand(), true)));
        $this->setTime($key, time() + 3600);
        return $this;
    }

    public function setTime($key, $time)
    {
        if (isset($this->token[$key])) {
            $this->time[$key] = intval($time);
            $this->driver->set('timeToken', $this->time);
        }

        return $this;
    }

    public function getTime($key)
    {
        return isset($this->time[$key]) ? $this->time[$key] : null;
    }

    public function isValid($key, $token, $delete = false)
    {
        $getToken = $this->get($key);

        if ($delete == true) {
            $this->delete($key);
        }

        if ($getToken == $token) {
            return true;
        }

        return false;
    }

    /**
     * @deprecated
     *
     * @return $this
     */

    public function getToken($key)
    {
        $caller = next(debug_backtrace());
        trigger_error($message . ' in <strong>' . $caller['function'] . '</strong> called from <strong>' . $caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong>' . "\n<br />error handler use get(" . $key . ")", E_USER_DEPRECATED);

        return $this->get($key);
    }


    /**
     * @deprecated
     *
     * @return $this
     */

    public function setToken($key, $value)
    {
        $caller = next(debug_backtrace());
        trigger_error($message . ' in <strong>' . $caller['function'] . '</strong> called from <strong>' . $caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong>' . "\n<br />error handler use set(" . $key . ")", E_USER_DEPRECATED);

        return $this->set($key, $value);
    }

    /**
     * @deprecated
     *
     * @return $this
     */

    public function remove($key)
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated use delete(' . $key . ')', E_USER_DEPRECATED);
        return $this->delete($key);
    }
}
