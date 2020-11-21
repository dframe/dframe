<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Session;

use Psr\SimpleCache\CacheInterface;

/**
 * Session Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Session implements CacheInterface
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $ipAddress;

    /**
     * @var string
     */
    protected string $userAgent;

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

            session_set_cookie_params(
                $cookie['lifetime'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httpOnly']
            );
            session_name($this->name);
            session_start();
        }

        if (php_sapi_name() != 'cli') {
            $this->ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $this->userAgent = $_SERVER["HTTP_USER_AGENT"] ?? 'unknown';

            if ($this->isValidFingerprint() !== true) {
                // Refresh Session
                $_SESSION = [];
                $_SESSION['_fingerprint'] = $this->getFingerprint();
            }
        }
    }

    /**
     * @return bool
     */
    public function isValidFingerprint(): bool
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
    protected function getFingerprint(): string
    {
        return md5($this->ipAddress . $this->userAgent . $this->name);
    }

    /**
     * Register the session.
     *
     * @param int $time .
     */
    public function register($time = 60): void
    {
        $_SESSION['sessionId'] = session_id();
        $_SESSION['sessionTime'] = (int)$time;
    }

    /**
     * Checks if the session is registered.
     *
     * @return bool
     */
    public function authLogin(): bool
    {
        if (!empty($_SESSION['sessionId'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $key
     */
    public function remove($key): void
    {
        $this->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key): void
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     *
     */
    public function end(): void
    {
        $this->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null): void
    {
        foreach ($values as $value) {
            $this->set($value['key'], $value['value'], $ttl);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $tll = null): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * {@inheritdoc}
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
    public function get($key, $or = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $or;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys) : bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->keyExists($key);
    }

    /**
     * @param       $key
     * @param array $in
     *
     * @return bool
     */
    public function keyExists($key, $in = []): bool
    {
        if (empty($in)) {
            $in = $_SESSION;
        }

        if (array_key_exists($key, $in) === true) {
            return true;
        }

        return false;
    }
}
