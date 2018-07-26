<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\BaseException;
use Dframe\Session;
use Dframe\Router;

/**
 * Message Class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 * @author Mike Everhart <mike@plasticbrain.net>
 */
class Messages
{

    public $msgId;
    public $msgTypes = ['help', 'info', 'warning', 'success', 'error'];

    /**
     * Add a message to the queue
     *
     * @param Object $session
     */
    public function __construct($driver)
    {
        $this->driver = $driver;
        if(!($this->driver instanceof \Psr\SimpleCache\CacheInterface) === true){
            throw new \Exception("This class Require instance Of Dframe\Session", 1);
        }

        // Generate a unique ID for this user and session
        $this->msgId = md5(uniqid());

        $keyExists = $this->driver->keyExists('flash_messages');
        if ($keyExists === false) {
            $this->driver->set('flash_messages', []);
        }
    }

    /**
     * Add a message to the queue
     *
     * @param string $type     The type of message to add
     * @param string $message  The message
     * @param string $redirect (optional) If set, the user will be redirected to this URL
     *
     * @return mixed
     */
    public function add($type, $message, $redirect = null)
    {

        if (!isset($type) or !isset($message[0])) {
            return false;
        }
        // Replace any shorthand codes with their full version
        if (strlen(trim($type)) === 1) {
            $type = str_replace(['h', 'i', 'w', 'e', 's'], ['help', 'info', 'warning', 'error', 'success'], $type);
        }

        $router = new Router();

        try {
            if (!in_array($type, $this->msgTypes)) {  // Make sure it's a valid message type
                throw new BaseException('"' . strip_tags($type) . '" is not a valid message type!', 501);
            }
        } catch (BaseException $e) {
            $msg = null;
            if (ini_get('display_errors') === "on") {
                $msg .= '<pre>';
                $msg .= 'Message: <b>' . $e->getMessage() . '</b><br><br>';

                $msg .= 'Accept: ' . $_SERVER['HTTP_ACCEPT'] . '<br>';
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $msg .= 'Referer: ' . $_SERVER['HTTP_REFERER'] . '<br><br>';
                }

                $msg .= 'Request Method: ' . $_SERVER['REQUEST_METHOD'] . '<br><br>';

                $msg .= 'Current file Path: <b>' . $this->router->currentPath() . '</b><br>';


                $msg .= 'File Exception: ' . $e->getFile() . ':' . $e->getLine() . '<br><br>';
                $msg .= 'Trace: <br>' . $e->getTraceAsString() . '<br>';
                $msg .= '</pre>';

                return Response::create($msg)->display();
            }

            return Response::create($e->getMessage())->status(501)->display();
        }

        $get = $this->driver->get('flash_messages');
        $get[$type][] = $message;
        $this->driver->set('flash_messages', $get);

        if (!is_null($redirect)) {
            return $router->redirect($redirect, 301);
        }

        return true;
    }

    /**
     * Display the queued messages
     *
     * @param string $type  Which messages to display
     * @param bool   $print True print the messages on the screen
     *
     * @return mixed
     */
    public function display($type = 'all', $print = false)
    {
        $messages = '';
        $data = '';

        // Print a certain type of message?
        if (in_array($type, $this->msgTypes)) {
            $flashMessages = $this->driver->get('flash_messages');
            foreach ($flashMessages[$type] as $msg) {
                $messages .= $msg;
            }

            $data .= $messages;

            // Clear the viewed messages
            $this->clear($type);
            // Print ALL queued messages
        } elseif ($type === 'all') {
            $flashMessages = $this->driver->get('flash_messages');
            foreach ($flashMessages as $type => $msgArray) {
                $messages = '';
                foreach ($msgArray as $msg) {
                    $messages .= $msg;
                }
                $data .= $messages;
            }

            // Clear ALL of the messages
            $this->clear();
            // Invalid Message Type?
        } else {
            return false;
        }

        // Print everything to the screen or return the data
        if ($print) {
            echo $data;
        } else {
            return $data;
        }
    }


    /**
     * Check to  see if there are any queued error messages
     *
     * @return bool true There ARE error messages false There are NOT any error messages
     */
    public function hasErrors()
    {
        $flashMessages = $this->driver->get('flash_messages');
        return empty($flashMessages['error']) ? false : true;
    }

    /**
     * Check to see if there are any ($type) messages queued
     *
     * @param string $type The type of messages to check for
     *
     * @return bool
     */
    public function hasMessages($type = null)
    {
        if (!is_null($type)) {
            $flashMessages = $this->driver->get('flash_messages');
            if (!empty($flashMessages[$type])) {
                return $flashMessages[$type];
            }
        } else {
            $flashMessages = $this->driver->get('flash_messages');
            foreach ($this->msgTypes as $type) {
                if (!empty($flashMessages[$type])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Clear messages from the session data
     *
     * @param string $type The type of messages to clear
     *
     * @return bool
     */
    public function clear($type = 'all')
    {
        if ($type === 'all') {
            $this->driver->remove('flash_messages');
        } else {
            $flashMessages = $this->driver->get('flash_messages');
            unset($flashMessages[$type]);
            $this->driver->set('flash_messages', $flashMessages);
        }

        return true;
    }


    /**
     *
     * @return bool
     */

    public function __toString()
    {
        return $this->hasMessages();
    }
}
