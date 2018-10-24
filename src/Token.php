<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

/**
 * Token Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Token
{
    /**
     * @var string
     */
    protected $driver;

    /**
     * @var array
     */
    protected $token = [];

    /**
     * @var array
     */
    protected $time = [];

    /**
     * constructor.
     *
     * @param string $driver
     */
    public function __construct($driver)
    {
        $this->driver = $driver;
        if (!($this->driver instanceof \Psr\SimpleCache\CacheInterface) === true) {
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
     *
     */
    public function clear()
    {
        $this->token = [];
        $this->time = [];

        $this->driver->set('token', $this->token);
        $this->driver->set('timeToken', $this->time);
    }

    /**
     * @param      $keys
     * @param null $default
     */
    public function getMultiple($keys, $default = null)
    {
    }

    /**
     * @param      $values
     * @param null $ttl
     */
    public function setMultiple($values, $ttl = null)
    {
    }

    /**
     * @param $keys
     */
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

    /**
     * @param      $key
     * @param      $token
     * @param bool $delete
     *
     * @return bool
     */
    public function isValid($key, $token, $delete = false)
    {
        $getToken = $this->get($key);

        if ($delete === true) {
            $this->delete($key);
        }

        if ($getToken === $token) {
            return true;
        }

        return false;
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
     * @param $key
     *
     * @return mixed|null
     */
    public function getTime($key)
    {
        return isset($this->time[$key]) ? $this->time[$key] : null;
    }

    /**
     * @param $key
     * @param $time
     *
     * @return $this
     */
    public function setTime($key, $time)
    {
        if (isset($this->token[$key])) {
            $this->time[$key] = intval($time);
            $this->driver->set('timeToken', $this->time);
        }

        return $this;
    }

    /**
     * @param $key
     *
     * @return $this
     */
    public function generate($key)
    {
        $this->set($key, md5(uniqid(rand(), true)));
        $this->setTime($key, time() + 3600);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param null   $ttl
     *
     * @return $this
     */
    public function set($key, $value, $ttl = null)
    {
        $this->token[$key] = $value;
        $this->driver->set('token', $this->token);

        return $this;
    }

    /**
     * @param $key
     */
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
}
