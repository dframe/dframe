<?php
namespace Dframe;
use Dframe\Config;
use Dframe\Loader;
use Dframe\Router\Response;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE (MIT)
 */

class Router
{

    public $aRouting;
    private $_aRoutingParse;
    private $_sURI;
    private $_subdomain = false;
    public $delay = null;
    public $parseArgs = array();

    public function __construct()
    {

        if (!defined('HTTP_HOST') AND isset($_SERVER['HTTP_HOST'])) {
            define('HTTP_HOST', $_SERVER['HTTP_HOST']);

        } elseif (!defined('HTTP_HOST')) {
            define('HTTP_HOST', '');
        }

        $this->domain = HTTP_HOST;

        $aURI = explode('/', $_SERVER['SCRIPT_NAME']);
        
        array_pop($aURI);
        $this->_sURI = implode('/', $aURI).'/';
        $this->_sURI = str_replace('/web/', '/', $this->_sURI);

        $routerConfig = Config::load('router');
        $this->_setHttps($routerConfig->get('https', false));

        $this->aRouting = $routerConfig->get(); // For url
        $this->_aRoutingParse = $routerConfig->get(); // For parsing array

        // Check forced Https
        if ($this->https == true) {
            $this->requestPrefix = 'https://';

            // If forced than redirect
            if (isset($_SERVER['REQUEST_SCHEME']) AND ((!empty($_SERVER['REQUEST_SCHEME']) AND $_SERVER['REQUEST_SCHEME'] == 'http'))) {
                return Response::create()->header([
                        'Refresh' => $this->requestPrefix.$this->domain.'/'.$_SERVER['REQUEST_URI']
                    ]);
            }
            
        } else {
            $this->requestPrefix = 'http://';
            
            if ((isset($_SERVER['REQUEST_SCHEME']) AND (!empty($_SERVER['REQUEST_SCHEME']) AND ($_SERVER['REQUEST_SCHEME'] == 'https') OR !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') OR (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'))) {
                $this->requestPrefix = 'https://';
            }

        }
    }

    public function run($controller = null, $action = null, $arg = array())
    {

        if (is_null($controller) AND is_null($action)) {
            $this->parseGets();
            $controller = $_GET['task'];
            $action = $_GET['action'];
        }

        $arg = $this->parseArgs;

        $bootstrap = new \Bootstrap();
        $bootstrap->router = $this;

        $loader = new Loader($bootstrap);
        $controller = $loader->loadController($controller); // Loading Controller class

        $response = array();

        if (method_exists($controller, 'start')) {
            $response[] = $controller->start();
        }
        
        if (method_exists($controller, 'init')) {
            $response[] = call_user_func_array(array($controller, 'init'), $arg);
        }

        if (method_exists($controller, $action) AND is_callable(array($controller, $action))) {
            $response[] = call_user_func_array(array($controller, (string)$action), $arg);
        }
        
        if (method_exists($controller, 'end')) {
            $response[] = $controller->end();
        }

        return $response;
    }
 
    private function _setHttps($option = false)
    {
        if (!in_array($option, array(true, false))) {
            throw new \Exception("Incorect option", 403);
        }

        $this->https = $option;
    }

    /**
     * @parms string ||array $url (folder,)controller/action 
     * Sprawdzanie czy to jest aktualnie wybrana zakładka
     */
    public function isActive($url) 
    {

        if ($this->makeUrl($url, true) == str_replace($this->_sURI, '', $_SERVER['REQUEST_URI'])) {
            return true;
        }

        return false;

    }

    public function publicWeb($sUrl = null, $path = null)
    {
        if (is_null($path)) {
            $path = $this->aRouting['publicWeb'];
        }

        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.$this->domain.'/'.$path;
        $sUrl .= $sExpressionUrl;
        
        return $sUrl;
    }

    public function makeUrl(string $sUrl = null, $onlyExt = false)
    {

        $aParamsHook = explode('#', $sUrl);
        $aParams = explode('?', $aParamsHook[0]);
        $aParams_ = explode('/', $aParams[0]);
        $sTask = $aParams_[0];

        $sAction = null;
        if (isset($aParams_[1]) AND !empty($aParams_[1])) { 
            $sAction = $aParams_[1];
        }

        if (isset($aParams[1])) { 
            parse_str($aParams[1], $aParams);
        } else { 
            $aParams = array();
        }
        
        $findKey = explode('?', $sUrl);
        if (isset($findKey[0])) {
            $findKey = $findKey[0];
        }


        if (MOD_REWRITE) {

            if (isset($this->aRouting[$findKey])) {

                $sExpressionUrl = $this->aRouting[$findKey][0];
                foreach ($aParams AS $key => $value) {
                    $sExpressionUrl = str_replace('['.$key.']', $value, $sExpressionUrl, $count);
                    if ($count > 0) {
                        unset($aParams[$key]);
                    }

                }

                if (isset($aParams)) {
                    if (isset($this->aRouting[$findKey]['_params'])) {
                        $sExpressionUrl = str_replace('[params]', $this->_parseParams($this->aRouting[$findKey]['_params'][0], $aParams), $sExpressionUrl);
                   
                    } elseif (!empty($aParams)) {
                        $sExpressionUrl = $sExpressionUrl . "?" . http_build_query($aParams);
                    }
                }

            } else {

                $sExpressionUrl = $this->aRouting['default'][0];

                $sExpressionUrl = str_replace('[task]', $sTask, $sExpressionUrl);
                $sExpressionUrl = str_replace('[action]', $sAction, $sExpressionUrl);
                if (isset($aParams)) {
                    $sExpressionUrl = str_replace('[params]', $this->_parseParams($this->aRouting['default']['_params'][0], $aParams), $sExpressionUrl);
                }


            }

        } else {

            if (empty($sTask)) {
                $sExpressionUrl = '';

            } else {

                if (isset($this->aRouting[$findKey])) {
    
                    $sExpressionUrl0 = $this->aRouting[$findKey][1];
                    foreach ($aParams AS $key => $value) {
                        $sExpressionUrl0 = str_replace('['.$key.']', $value, $sExpressionUrl0, $count);
                        if ($count > 0) {
                            unset($aParams[$key]);
                        }
                    }

                    $sExpressionUrl = $sExpressionUrl0;
                    
                } else {

                    $sExpressionUrl = 'task='.$sTask;
                    if (!empty($sAction)) {
                        $sExpressionUrl = 'task='.$sTask.'&action='.$sAction;
                    }
        
                }

                if (!empty($aParams)) {
                    if (!empty($sExpressionUrl)) {
                        $sExpressionUrl .= '&';
                    }

                    $sExpressionUrl = $sExpressionUrl.http_build_query($aParams);
                }

                $sExpressionUrl = 'index.php?'.$sExpressionUrl;
            }

        }

        $parsedUrl = \parse_url($this->domain);
        if (isset($parsedUrl['scheme'])) {
            $this->requestPrefix = $parsedUrl['scheme'] . '://';
            $this->domain = ltrim($this->domain, $parsedUrl['scheme'] . '://');
        }

        $HTTP_HOST = $this->domain;
        if (!empty($this->_subdomain)) {
            $HTTP_HOST = $this->_subdomain.'.'.$this->domain;
        }

        $sUrl = '';
        if ($onlyExt === false) {
            $sUrl = $this->requestPrefix.$HTTP_HOST.'/';
        }

        $sUrl .= $sExpressionUrl;

        $sUrl = rtrim($sUrl, '/');
        return $sUrl;
    }

    private function _parseParams($sRouting, $aParams)
    {
        $sReturn = null;
        foreach ($aParams AS $key => $value) {
            $sReturn .= str_replace(array('[name]', '[value]'), array($key, $value), $sRouting);
        }
        return $sReturn;
    }

    public function parseGets()
    {

        $sRequest = preg_replace('!'.$this->_sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
        
        if (MOD_REWRITE) {

            if (substr($sRequest, -1)!='/') {
                $sRequest .= '/';
            }

            $sGets = $this->_parseUrl($sRequest);
            $sGets = str_replace('?', '&', $sGets);

            parse_str($sGets, $aGets);

            $_GET['task'] = !empty($aGets['task'])?$aGets['task']:$this->aRouting['NAME_CONTROLLER'];
            unset($aGets['task']);

            $_GET['action'] = !empty($aGets['action'])?$aGets['action']:$this->aRouting['NAME_METHOD'];
            unset($aGets['action']);

            $_GET = array_merge($_GET, $aGets);

        } else {

            $_GET['task'] = !empty($_GET['task'])?$_GET['task']:$this->aRouting['NAME_CONTROLLER'];
            $_GET['action'] = !empty($_GET['action'])?$_GET['action']:$this->aRouting['NAME_METHOD'];
            
        }

    }

    public function currentPath()
    {

        $sRequest = preg_replace('!'.$this->_sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
        
        if (MOD_REWRITE) {

            if (substr($sRequest, -1)!='/') {
                $sRequest .= '/';
            }

            $sGets = $this->_parseUrl($sRequest);
            $sGets = str_replace('?', '&', $sGets);
            
        } else {

            $sGets = $_SERVER['QUERY_STRING'];
        }


        return $sGets;

    }

    private function _parseUrl($sRequest)
    {   

        $sVars = null;
        $sRequest = str_replace('?', '/?', $sRequest);
        
        foreach ($this->_aRoutingParse AS $k => $v) {
            
            if (!is_array($v)) {
                continue;
            }

            preg_match_all('!\[(.+?)\]!i', $v[0], $aExpression_);
            $sExpression = preg_replace_callback(
                '!\[(.+?)\]!i', function ($m) use ($k) { 
                    return $this->_transformParam($m[1], $k);
                }, $v[0]
            );


            if (preg_match_all('!'.$sExpression.'!i', $sRequest, $aExpression__)) {

                $args = array();
                if (isset($v['args'])) {
                    $args = $v['args'];
                }

                foreach ($aExpression__ AS $k_ => $v_) {
                    foreach ($v_ AS $kkk => $vvv) {

                        if (!isset($aExpression_[1][$k_-1])) {
                            $aExpression_[1][$k_-1] = null;
                        }
                        
                        if ($kkk>0) {
                            $aExpression[] = array($aExpression_[1][$k_-1].'_'.$kkk, $vvv);
                        } else {
                            $aExpression[] = array($aExpression_[1][$k_-1], $vvv);
                        }
                    
                    }
                }

                unset($aExpression[0]);
                $iCount = count($aExpression__[0]);
                if ($iCount>1) {
                    for ($i=0;$i<$iCount;$i++) {
                        if ($i>0) {
                            $sVars .= '&'.preg_replace('!\[(.+?)\]!i', '[$1_'.$i.']', $v[1]);
                        } else {
                            $sVars = '&'.$v[1];
                        }                        
                    }

                } else {                
                    $sVars = '&'.$v[1];
                }

                foreach ($aExpression AS $k => $v_) {

                    if (!isset($v['_'.$v_[0]])) {
                        $v['_'.$v_[0]] = null;
                    }
                    
                    if (!is_array($v['_'.$v_[0]])) {
                        foreach ($args as $key => $value) {
                            $args[$key] = str_replace('['.$v_[0].']', $v_[1], $args[$key]);
                        }

                        $sVars = str_replace('['.$v_[0].']', $v_[1], $sVars);
                    
                    } else {
                        $this->_aRoutingParse = array($v['_'.$v_[0]]);
                        $sVars = $sVars.$this->_parseUrl($v_[1]);

                    }
                }
                $this->parseArgs = $args;  
                break;                

            }

        }    

        return $sVars;
    }

    private function _transformParam($sParam, $k)
    {
        if (isset($this->aRouting[$k][$sParam]) AND !is_array($this->aRouting[$k][$sParam])) {
            return $this->aRouting[$k][$sParam];
        } else { 
            return '(.+?)';
        }

    }

    /**
     * Przekierowanie adresu 
     *
     * @param  string $url CONTROLLER/MODEL?parametry
     * @return void
     */
    
    public function redirect($url = '', $status = 301) 
    {

    	$response = Response::create();
        $response->status($status);
        
        if ($this->delay != null) {
            $header = array(
            	'Refresh' => $this->delay."; url=".$this->makeUrl($url)
            );
        } else {
        	$header = array(
                'Location' => $this->makeUrl($url)
            );
        }

        $response->header($header);
        return $response;
    }

    public function delay(int $delay)
    {
        $this->delay = $delay;
        return $this;
    }

    public function subdomain($subdomain)
    {
        $this->_subdomain = $subdomain;
        return $this;
        
    }

    public function domain($domain)
    {
        $this->domain = $domain;
        return $this;
        
    }

    public function addRoute($newRoute)
    {
        $this->aRouting = array_merge($this->aRouting, $newRoute);
    }

    public function response()
    {
        return new Response();

    }

}