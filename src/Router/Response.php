<?php
/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Router;

use Dframe\Config;

/**
 * Short Description
 *
 * @author Sławek Kaleta <slaszka@gmail.com>
 */
class Response extends \Dframe\Router
{

    public $status = 200;

    private $_body = '';

    private $_headers = array();

    public static $code = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
    );

    public function __construct($body = false)
    {
        if (isset($body)) {
            $this->_body = $body;
        }
        return $this;
    }

    public static function create($body = null)
    {
        return new Response($body);
    }

    public static function render($body = null)
    {
        return new Response($body);
    }

    public static function renderJSON($body = null, $status = null)
    {
        $body = json_encode($body);
        $Response = new Response($body);

        if (isset($status)) {
            $Response->status($status);
        }

        $Response->headers(array('Content-Type' => 'application/json'));
        return $Response;
    }

    public static function renderJSONP($body = null, $status = null)
    {

        $callback = null;
        if (isset($_GET['callback'])) {
            $callback = $_GET['callback'];
        }

        $Response = new Response($callback . '(' . json_encode($body) . ')');

        if (isset($status)) {
            $Response->status($status);
        }

        $Response->headers(array('Content-Type' => 'application/jsonp'));
        return $Response;
    }
    
    public function json($json)
    {
        $this->headers(array('Content-Type' => 'application/json'));
        $this->_body = json_encode($json);
        return $this;
    }

    public function status($code)
    {
        $this->status = $code;
        return $this;
    }

    public function headers($headers = false)
    {
        $this->_headers = array_merge($this->_headers, $headers);
        return $this;
    }
    
    public function body($body = null)
    {
        $this->_body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function display()
    {

        if (!headers_sent()) {
            if (PHP_SAPI !== 'cli') {
                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');
                $status = (!empty($this->status) ? $this->status : 200);
                $string = sprintf('%s %d %s', $protocol, $status, self::$code[$status]);

                header($string, true, $status); // Default header
                if (!empty($this->_headers)) {
                    foreach ($this->_headers as $field => $value) {
                        if (is_array($value)) {
                            foreach ($value as $v) {
                                header("$field".': '.$v, false);
                            }
                        } else {
                            header("$field".': '.$value);
                        }
                    }
                }
            }
        }

        if (!empty($this->getBody())) {
            echo $this->getBody();
        }
    }

    public function __toString()
    {
        return $this->_body;
    }
}
