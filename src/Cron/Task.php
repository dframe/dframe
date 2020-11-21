<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Cron;

use Dframe\Task\Exceptions\TaskException;
use Dframe\Loader\Loader;
use Exception;
use InvalidArgumentException;

/**
 * Config Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class Task extends Loader
{
    /**
     * @param       $key
     * @param       $callback
     * @param array $bind
     * @param int   $ttl
     *
     * @return array
     * @throws Exception
     */
    protected function inLock($key, $callback, array $bind = [], $ttl = 3600): array
    {
        if (!defined('APP_DIR')) {
            throw new TaskException('Please Define APP_DIR in Main config.php', 500);
        }

        $dir = APP_DIR . 'View/cache/logs/';
        $file = $key . '.txt';
        $this->checkDir($dir);
        $dirLog = $dir . $file;

        if (!is_callable($callback)) {
            throw new InvalidArgumentException();
        }

        $fp = fopen($dirLog, "w");
        if (flock($fp, LOCK_EX | LOCK_NB)) { // do an exclusive lock
            $data = call_user_func_array($callback, $bind);

            flock($fp, LOCK_UN); // release the lock
            $this->lockTime($key, $ttl);
        } else {
            return ['return' => false];
        }
        fwrite($fp, date("d-m-Y H:i:s"));
        fclose($fp);
        return ['return' => true, 'response' => $data];
    }

    /**
     * @param $path
     *
     * @throws Exception
     */
    protected function checkDir($path): void
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new Exception('Unable to create' . $path, '', 403);
            }
        }
    }

    /**
     * @param     $key
     * @param int $ttl
     *
     * @return bool
     * @throws Exception
     */
    protected function lockTime($key, $ttl = 59): bool
    {
        if (!defined('APP_DIR')) {
            throw new TaskException('Please Define APP_DIR in Main config.php', 500);
        }

        $dir = $dirLog = APP_DIR . 'View/cache/logs/';
        $file = $key . '.txt';
        $this->checkDir($dir);
        $dirLog = $dir . $file;

        if (file_exists($dirLog) and filemtime($dirLog) + $ttl > time()) {
            return false;
        }

        $fp = fopen($dirLog, "w");
        fwrite($fp, date("d-m-Y H:i:s"));
        fclose($fp);

        return true;
    }
}
