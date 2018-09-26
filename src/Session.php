<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

/**
 * Session Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */

class Session implements \Psr\SimpleCache\CacheInterface
{
    protected $name;
    protected $ipAddress;
    protected $userAgent;

    /**
     * Session constructor.
     *
     * @param array $app
     */
    public function __construct($app = [])
    {
        $options = $this->app->config['session'] ?? [];
        $this->name = APP_NAME ?? '_sessionName';

        if (!isset($_SESSION)) {
            $cookie = [
                'lifetime' => $options['cookie']['lifetime'] ?? 0,
                'path' => $options['cookie']['path'] ?? '/',
                'domain' => $options['cookie']['domain'] ?? null,
                'secure' => $options['cookie']['secure'] ?? ($_SERVER['HTTPS'] ?? null),
                'httpOnly' => $options['cookie']['httpOnly'] ?? false,
            ];

            session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
            session_name($this->name);
            session_start();
        }

        if (php_sapi_name() != 'cli') {
            $this->ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $this->userAgent = $_SERVER["HTTP_USER_AGENT"] ?? 'unknown';

            if ($this->isValidFingerprint() != true) {
                // Refresh Session
                $_SESSION = [];
                $_SESSION['_fingerprint'] = $this->getFingerprint();
            }
        }
    }

    /**
     * @return bool
     */
    public function isValidFingerprint()
    {
        $_fingerprint = $this->getFingerprint();
        if (isset($_SESSION['_fingerprint']) and $_SESSION['_fingerprint'] === $_fingerprint) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getFingerprint()
    {
        return md5($this->ipAddress . $this->userAgent . $this->name);
    }

    /**
     * Register the session.
     *
     * @param int $time .
     */
    public function register($time = 60)
    {
        $_SESSION['sessionId'] = session_id();
        $_SESSION['sessionTime'] = intval($time);
    }

    /**
     * Checks if the session is registered.
     *
     * @return bool
     */
    public function authLogin()
    {
        if (!empty($_SESSION['sessionId'])) {
            return true;
        }

        return false;
    }

    /**
     * @param       $key
     * @param array $in
     *
     * @return bool
     */
    public function keyExists($key, $in = [])
    {
        if (empty($in)) {
            $in = $_SESSION;
        }

        if (array_key_exists($key, $in) === true) {
            return true;
        }

        return false;
    }

    /**
     * Set session key.
     *
     * @param string $key
     * @param mixed  $value
     * @param null   $tll
     *
     * @return bool|void
     */
    public function set($key, $value, $tll = null)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * get session key.
     *
     * @param string $key
     * @param string $or
     *
     * @return string|null
     */
    public function get($key, $or = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $or;
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        $this->delete($key);
    }

    /**
     * @param string $key
     *
     * @return bool|void
     */
    public function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function end()
    {
        $this->clear();
    }

    /**
     * @return bool|void
     */
    public function clear()
    {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * @param iterable $values
     * @param null     $ttl
     *
     * @return bool|void
     */
    public function setMultiple($values, $ttl = null)
    {
        //todo
    }

    /**
     * @param iterable $keys
     * @param null     $default
     *
     * @return iterable|void
     */
    public function getMultiple($keys, $default = null)
    {
        //todo
    }

    /**
     * @param iterable $keys
     *
     * @return bool|void
     */
    public function deleteMultiple($keys)
    {
        //todo
    }

    /**
     * @param string $key
     *
     * @return bool|void
     */
    public function has($key)
    {
        //todo
    }
}
