<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

/**
 * Session Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */

class Session
{

    function __construct()
    {
        $name = '_sessionName';
        $options = [];

        $this->name = $name;

        if (!isset($_SESSION)) {
            $cookie = array(
                'lifetime' => isset($options['cookie']['lifetime']) ? $options['cookie']['lifetime'] : 0,
                'path' => isset($options['cookie']['path']) ? $options['cookie']['path'] : '/',
                'domain' => isset($options['cookie']['domain']) ? $options['cookie']['domain'] : null,
                'secure' => isset($options['cookie']['secure']) ? $options['cookie']['secure'] : isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null,
                'httponly' => isset($options['cookie']['httponly']) ? $options['cookie']['httponly'] : false,
            );

            session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
            session_name($this->name);
            session_start();
        }

        if (php_sapi_name() != 'cli') {
            $this->ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $this->userAgent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : 'unknown';

            if ($this->isValidFingerprint() != true) {
                // Refresh Session
                $_SESSION = array();
                $_SESSION['_fingerprint'] = $this->_getFingerprint();
            }
        }
    }

    private function _getFingerprint()
    {
        return md5($this->ipAddress . $this->userAgent . $this->name);
    }

    /**
     * Register the session.
     *
     * @param integer $time.
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
        } else {
            return false;
        }
    }

    public function keyExists($key, $in = false)
    {
        if (isset($in)) {
            $in = $_SESSION;
        }

        if (array_key_exists($key, $in) == true) {
            return true;
        }

        return false;
    }

    /**
     * Set session key
     *
     * @param string $key
     * @param string $value
     */

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * get session key
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

    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function end()
    {
        session_destroy();
        $_SESSION = array();
    }

    public function isValidFingerprint()
    {

        $_fingerprint = $this->_getFingerprint();
        if (isset($_SESSION['_fingerprint']) and $_SESSION['_fingerprint'] == $_fingerprint) {
            return true;
        }

        return false;
    }
}
