<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Token;

use Exception;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Token Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Token implements CacheInterface
{
    /**
     * @var CacheInterface
     */
    protected $driver;

    /**
     * @var array
     */
    protected array $token = [];

    /**
     * @var array
     */
    protected array $time = [];

    /**
     * constructor.
     *
     * @param CacheInterface $driver
     */
    public function __construct($driver)
    {
        $this->driver = $driver;
        if (!($this->driver instanceof CacheInterface) === true) {
            throw new Exception("This class Require instance Of Psr\SimpleCache\CacheInterface", 1);
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
     * @param string      $key
     * @param null|string $default
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
     *
     * @return mixed|null
     */
    public function getTime($key)
    {
        return isset($this->time[$key]) ? $this->time[$key] : null;
    }

    /**
     * @param string $key
     * @param        $time
     *
     * @return self
     */
    public function setTime($key, $time): self
    {
        if (isset($this->token[$key])) {
            $this->time[$key] = intval($time);
            $this->driver->set('timeToken', $this->time);
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function generate($key): self
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
     * @return self
     */
    public function set($key, $value, $ttl = null): self
    {
        $this->token[$key] = $value;
        $this->driver->set('token', $this->token);

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function clear(): void
    {
        $this->token = [];
        $this->time = [];

        $this->driver->set('token', $this->token);
        $this->driver->set('timeToken', $this->time);
    }

    /**
     * @param iterable $keys
     * @param null     $default
     *
     * @return iterable|void
     */
    public function getMultiple($keys, $default = null)
    {
        $cache = [];
        foreach ($keys as $key) {
            $cache[$key] = $this->get($key, $default);
        }

        return $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $value) {
            $this->set($value['key'], $value['value'], $ttl);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return $this;
    }

    /**
     * @param string $key
     */
    public function delete($key): void
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

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        if (isset($this->token[$key]) and $this->getTime($key) >= time()) {
            return true;
        }

        return false;
    }

    /**
     * @param      $key
     * @param      $token
     * @param bool $delete
     *
     * @return bool
     */
    public function isValid($key, $token, $delete = false): bool
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
}
