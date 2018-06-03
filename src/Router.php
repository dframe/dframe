<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\Config;
use Dframe\Loader;
use Dframe\Router\Response;

/**
 * Router
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Router
{

    public $aRouting;
    private $_aRoutingParse;
    private $_sURI;
    private $_subdomain = false;
    public $delay = null;
    public $parseArgs = array();

    private $_routesFile = 'routes.php';
    private $_controllersFile = 'controllers.php';
    private $_usedControllers = [];
    private $_controllerDirs = APP_DIR . 'Controller/';
    private $_cacheDir = APP_DIR . 'View/cache/';
    public $routes;

    public function __construct($baseClass)
    {
        $this->app = $baseClass;


        if (!defined('HTTP_HOST') and isset($_SERVER['HTTP_HOST'])) {
            define('HTTP_HOST', $_SERVER['HTTP_HOST']);
        } elseif (!defined('HTTP_HOST')) {
            define('HTTP_HOST', '');
        }

        $this->domain = HTTP_HOST;

        $aURI = explode('/', $_SERVER['SCRIPT_NAME']);

        array_pop($aURI);
        $this->_sURI = implode('/', $aURI) . '/';
        $this->_sURI = str_replace('/web/', '/', $this->_sURI);

        $routerConfig = Config::load('router');
        $this->_setHttps($routerConfig->get('https', false));

        $this->aRouting = $routerConfig->get(); // For url
        $this->_aRoutingParse = $routerConfig->get('routes'); // For parsing array

        $this->aRouting['routes'] = array_merge($this->aRouting['routes'], $this->app->config['router']['routes']);
        $this->_aRoutingParse = array_merge($this->app->config['router']['routes'], $this->_aRoutingParse);

        // Check forced HTTPS
        if ($this->https == true) {
            $this->requestPrefix = 'https://';

            // If forced than redirect
            if (isset($_SERVER['REQUEST_SCHEME']) and ((!empty($_SERVER['REQUEST_SCHEME']) and $_SERVER['REQUEST_SCHEME'] == 'http'))) {
                return Response::create()->headers(
                    [
                        'Refresh' => $this->requestPrefix . $this->domain . '/' . $_SERVER['REQUEST_URI']
                    ]
                )->display();
            }
        } else {
            $this->requestPrefix = 'http://';

            if ((isset($_SERVER['REQUEST_SCHEME']) and (!empty($_SERVER['REQUEST_SCHEME']) and ($_SERVER['REQUEST_SCHEME'] == 'https') or !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') or (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'))) {
                $this->requestPrefix = 'https://';
            }
        }

        $routesFile = 'routes.php';
        $controllersFile = 'controllers.php';
        $usedControllers = array();
        $controllerDirs = APP_DIR . 'Controller/';
        $cacheDir = APP_DIR . 'View/cache/';

        // We save controller dirs
        if (is_string($controllerDirs)) {
            $controllerDirs = [$controllerDirs];
        }

        if (!is_array($controllerDirs)) {
            throw new \InvalidArgumentException('Controllers directory must be either string or array');
        }

        $this->_controllerDirs = [];
        foreach ($controllerDirs as $d) {
            $realPath = realPath($d);
            if ($realPath !== false) {
                $this->_controllerDirs[] = $realPath;
            }
        }
        // We save the cache dir
        if (!is_dir($cacheDir)) {
            $result = @mkdir($cacheDir, 0777, true);
            if ($result === false) {
                throw new \RuntimeException('Can\'t create cache directory');
            }
        }

        if (!is_writable($cacheDir)) {
            throw new \RuntimeException('Cache directory must be writable by web server');
        }
        $this->_cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->_generateRoutes();

    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRoutes($routes)
    {
        $this->routes = array_merge($routes);
    }

    public function run()
    {

        if (is_null($controller ?? null) and is_null($action ?? null)) {
            $this->parseGets();
            $controller = $this->controller;
            $action = $this->action;
            $namespace = $this->namespace;
        }

        $arg = $this->parseArgs;

        $loader = new Loader($this->app);
        $controller = $loader->loadController($controller, $namespace); // Loading Controller class

        $response = array();

        if (method_exists($controller, 'start')) {
            $response[] = 'start';
        }

        if (method_exists($controller, 'init')) {
            $response[] = 'init';
        }

        if (method_exists($controller, $action) or is_callable(array($controller, $action))) {
            $response[] = $action;
        }

        if (method_exists($controller, 'end')) {
            $response[] = 'end';
        }

        foreach ($response as $key => $data) {
            if (is_callable(array($controller, $data))) {
                $run = $controller->$data();
                if ($run instanceof Response) {
                    return $run->display();
                }
            }
        }

        return true;
    }

    private function _setHttps($option = false)
    {
        if (!in_array($option, array(true, false))) {
            throw new \InvalidArgumentException('Incorect option', 403);
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
        $sUrl = $this->requestPrefix . $this->domain . '/' . $path;
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
        if (isset($aParams_[1]) and !empty($aParams_[1])) {
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
            if (isset($this->aRouting['routes'][$findKey])) {
                $sExpressionUrl = $this->aRouting['routes'][$findKey][0];
                foreach ($aParams as $key => $value) {
                    $sExpressionUrl = str_replace('[' . $key . ']', $value, $sExpressionUrl, $count);
                    if ($count > 0) {
                        unset($aParams[$key]);
                    }
                }

                if (isset($aParams)) {
                    if (isset($this->aRouting['routes'][$findKey]['_params'])) {
                        $sExpressionUrl = str_replace('[params]', $this->_parseParams($this->aRouting['routes'][$findKey]['_params'][0], $aParams), $sExpressionUrl);
                    } elseif (!empty($aParams)) {
                        $sExpressionUrl = $sExpressionUrl . "?" . http_build_query($aParams);
                    }
                }
            } else {
                $sExpressionUrl = $this->aRouting['routes']['default'][0];

                $sExpressionUrl = str_replace('[task]', $sTask, $sExpressionUrl);
                $sExpressionUrl = str_replace('[action]', $sAction, $sExpressionUrl);
                if (isset($aParams)) {
                    $sExpressionUrl = str_replace('[params]', $this->_parseParams($this->aRouting['routes']['default']['_params'][0], $aParams), $sExpressionUrl);
                }
            }
        } else {
            if (empty($sTask)) {
                $sExpressionUrl = '';
            } else {
                if (isset($this->aRouting['routes'][$findKey])) {
                    $sExpressionUrl0 = $this->aRouting['routes'][$findKey][1];
                    foreach ($aParams as $key => $value) {
                        $sExpressionUrl0 = str_replace('[' . $key . ']', $value, $sExpressionUrl0, $count);
                        if ($count > 0) {
                            unset($aParams[$key]);
                        }
                    }

                    $sExpressionUrl = $sExpressionUrl0;
                } else {
                    $sExpressionUrl = 'task=' . $sTask;
                    if (!empty($sAction)) {
                        $sExpressionUrl = 'task=' . $sTask . '&action=' . $sAction;
                    }
                }

                if (!empty($aParams)) {
                    if (!empty($sExpressionUrl)) {
                        $sExpressionUrl .= '&';
                    }

                    $sExpressionUrl = $sExpressionUrl . http_build_query($aParams);
                }

                $sExpressionUrl = 'index.php?' . $sExpressionUrl;
            }
        }

        $parsedUrl = \parse_url($this->domain);
        if (isset($parsedUrl['scheme'])) {
            $this->requestPrefix = $parsedUrl['scheme'] . '://';
            $this->domain = ltrim($this->domain, $parsedUrl['scheme'] . '://');
        }

        $HTTP_HOST = $this->domain;
        if (!empty($this->_subdomain)) {
            $HTTP_HOST = $this->_subdomain . '.' . $this->domain;
        }

        $sUrl = '';
        if ($onlyExt === false) {
            $sUrl = $this->requestPrefix . $HTTP_HOST . '/';
        }

        $sUrl .= $sExpressionUrl;

        $sUrl = rtrim($sUrl, '/');
        return $sUrl;
    }

    private function _parseParams($sRouting, $aParams)
    {
        $sReturn = null;
        foreach ($aParams as $key => $value) {
            $sReturn .= str_replace(array('[name]', '[value]'), array($key, $value), $sRouting);
        }
        return $sReturn;
    }

    public function parseGets()
    {

        $sRequest = preg_replace('!' . $this->_sURI . '(.*)$!i', '$1', $_SERVER['REQUEST_URI']);

        if (MOD_REWRITE) {
            if (substr($sRequest, -1) != '/') {
                $sRequest .= '/';
            }

            $sGets = $this->_parseUrl($sRequest);

            $this->namespace = $sGets['v']['namespace'] ?? '';

            $sGets = str_replace('?', '&', $sGets['sVars']);
            parse_str($sGets, $aGets);

            $this->controller = !empty($aGets['task']) ? $aGets['task'] : $this->aRouting['NAME_CONTROLLER'];
            unset($aGets['task']);

            $this->action = !empty($aGets['action']) ? $aGets['action'] : $this->aRouting['NAME_METHOD'];
            unset($aGets['action']);

            //$_GET = array_merge($_GET, $aGets);

        } else {
            $this->controller = !empty($_GET['task']) ? $_GET['task'] : $this->aRouting['NAME_CONTROLLER'];
            $this->action = !empty($_GET['action']) ? $_GET['action'] : $this->aRouting['NAME_METHOD'];
        }
    }

    public function currentPath()
    {

        $sRequest = preg_replace('!' . $this->_sURI . '(.*)$!i', '$1', $_SERVER['REQUEST_URI']);

        if (MOD_REWRITE) {
            if (substr($sRequest, -1) != '/') {
                $sRequest .= '/';
            }

            $sGets = $this->_parseUrl($sRequest);
            $sGets = str_replace('?', '&', $sGets['sVars']);
        } else {
            $sGets = $_SERVER['QUERY_STRING'];
        }


        return $sGets;
    }

    private function _parseUrl($sRequest)
    {

        $sVars = null;
        $sRequest = str_replace('?', '/?', $sRequest);

        foreach ($this->_aRoutingParse as $k => $v) {
            if (!is_array($v)) {
                continue;
            }

            preg_match_all('!\[(.+?)\]!i', $v[0], $aExpression_);
            $sExpression = preg_replace_callback('!\[(.+?)\]!i', function ($m) use ($k) {
                return $this->_transformParam($m[1], $k);
            }, $v[0]);


            if (preg_match_all('!' . $sExpression . '!i', $sRequest, $aExpression__)) {
                $args = array();
                if (isset($v['args'])) {
                    $args = $v['args'];
                }

                foreach ($aExpression__ as $k_ => $v_) {
                    foreach ($v_ as $kkk => $vvv) {
                        if (!isset($aExpression_[1][$k_ - 1])) {
                            $aExpression_[1][$k_ - 1] = null;
                        }

                        if ($kkk > 0) {
                            $aExpression[] = array($aExpression_[1][$k_ - 1] . '_' . $kkk, $vvv);
                        } else {
                            $aExpression[] = array($aExpression_[1][$k_ - 1], $vvv);
                        }
                    }
                }

                unset($aExpression[0]);
                $iCount = count($aExpression__[0]);
                if ($iCount > 1) {
                    for ($i = 0; $i < $iCount; $i++) {
                        if ($i > 0) {
                            $sVars .= '&' . preg_replace('!\[(.+?)\]!i', '[$1_' . $i . ']', $v[1]);
                        } else {
                            $sVars = '&' . $v[1];
                        }
                    }
                } else {
                    $sVars = '&' . $v[1];
                }

                foreach ($aExpression as $k => $v_) {
                    if (!isset($v['_' . $v_[0]])) {
                        $v['_' . $v_[0]] = null;
                    }

                    if (!is_array($v['_' . $v_[0]])) {
                        foreach ($args as $key => $value) {
                            $args[$key] = str_replace('[' . $v_[0] . ']', $v_[1], $args[$key]);
                        }

                        $sVars = str_replace('[' . $v_[0] . ']', $v_[1], $sVars);
                    } else {
                        $this->_aRoutingParse = array($v['_' . $v_[0]]);
                        $sVars = $sVars . $this->_parseUrl($v_[1])['sVars'];
                    }
                }
                $this->parseArgs = $args;
                break;
            }
        }

        return array('v' => $v, 'sVars' => $sVars);
    }

    private function _transformParam($sParam, $k)
    {
        if (isset($this->aRouting['routes'][$k][$sParam]) and !is_array($this->aRouting['routes'][$k][$sParam])) {
            return $this->aRouting['routes'][$k][$sParam];
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

    public static function redirect($url = '', $status = 301)
    {
        return Response::redirect($url, $status);
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
        $this->aRouting['routes'] = array_merge($this->aRouting['routes'], $newRoute);
    }

    public function response()
    {
        return new Response();
    }

    private function _generateRoutes()
    {
        $parsingNeeded = !file_exists($this->_cacheDir . $this->_routesFile);
        // We look for controller files
        $files = $this->_findControllerFiles();
        // We check if there has been modifications since last cache generation
        if (!$parsingNeeded) {
            $routesCacheMtime = filemtime($this->_cacheDir . $this->_routesFile);
            foreach ($files as $file => $mtime) {
                if ($mtime > $routesCacheMtime) {
                    $parsingNeeded = true;
                    break;
                }
            }
        }
        // We look for deleted controller files
        if (!$parsingNeeded && file_exists($this->_cacheDir . $this->_controllersFile)) {
            require_once $this->_cacheDir . $this->_controllersFile;
            foreach ($this->_usedControllers as $controllerFile) {
                if (!file_exists($controllerFile)) {
                    $parsingNeeded = true;
                    break;
                }
            }
        }
        // We regenerate cache file if needed
        if ($parsingNeeded) {
            $controllerFiles = [];
            $commonFileContent = '<?php' . "\r\n" . '/**' . "\r\n" . ' * annotations router %s cache file, create ' . date('c') . "\r\n" . ' */' . "\r\n\r\n";

            $routesFileContent = sprintf($commonFileContent, 'routes');
            $controllersFileContent = sprintf($commonFileContent, 'controllers');

            $routesFileContent .= 'return array(';
            foreach ($files as $file => $mtime) {
                // We generate routes for current file
                $content = $this->_parseFile($file);
                if ($content !== '') {
                    $routesFileContent .= $content;
                    $controllerFiles[] = $file;
                }
            }

            $routesFileContent = rtrim($routesFileContent, ',' . "\r\n");
            $routesFileContent .= "\r\n" . ");";

            file_put_contents($this->_cacheDir . $this->_routesFile, $routesFileContent);
            $usedControllers = (count($controllerFiles) > 0) ? '$this->_usedControllers = [\'' . join('\',\'', $controllerFiles) . '\'];' : '';
            file_put_contents($this->_cacheDir . $this->_controllersFile, $controllersFileContent . $usedControllers);
        }

        $routesConfig = Config::load('routes', APP_DIR . 'View/cache/')->get();
        if (!empty($routesConfig)) {
            $this->_aRoutingParse = array_merge($routesConfig, $this->_aRoutingParse);
        }
    }

    private function _findControllerFiles()
    {
        $result = [];
        foreach ($this->_controllerDirs as $dir) {
            $directoryIterator = new \RecursiveDirectoryIterator($dir);
            $iterator = new \RecursiveIteratorIterator($directoryIterator);

            $files = new \RegexIterator($iterator, '/\.php$/i', \RecursiveRegexIterator::GET_MATCH);
            foreach ($files as $k => $v) {
                $result[$k] = filemtime($k);
            }
        }
        return $result;
    }

    /**
     * @param string $file
     * @return string
     */
    private function _parseFile($file)
    {
        $result = '';

        $appDir = str_replace('web/../app/', '', APP_DIR);
        $task = str_replace($appDir . 'app\Controller\\', '', $file);
        $task = rtrim($task, '.php');
        $task = str_replace('\\', ',', $task);
   
        // We load file content
        $content = file_get_contents($file);

        // We search for namespace
        $namespace = null;
        if (preg_match('/namespace\s+([\w\\\_-]+)/', $content, $matches) === 1) {
            $namespace = $matches[1];
        }
        
        // We look for class name
        if (preg_match('/class\s+([\w_-]+)/', $content, $matches) === 1) {
            $className = ($namespace !== null) ? $namespace . '\\' . $matches[1] : $matches[1];
            // We find class infos

            $path = str_replace('Controller.php', '.php', $className . '.php');
            $path = APP_DIR . str_replace("\\", "/", $path);
            if (is_file($path)) {
                include_once $path;
            }

            $reflector = new \ReflectionClass($className);

            $prefix = '';
            if (preg_match('/@RoutePrefix\(["\'](((?!(["\'])).)*)["\']\)/', $reflector->getDocComment(), $matches) === 1) {
                $prefix = $matches[1];
            }

            $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);

            $result = '';
            foreach ($methods as $m) {
                if ($m->isStatic()) {
                    continue;
                }

                if (preg_match('/@Route\(\s*["\']([^\'"]*)["\'][^)]*\)/', $m->getDocComment(), $matches) === 1) {
                    $routePath = $matches[1];
                    $route = $matches[0];
                    $methods = '\'GET\'';

                    if (preg_match('/methods={([^}]*)}/', $route, $matches) === 1) {
                        $methods = str_replace('"', "'", $matches[1]);
                    }

                    $routeName = null;
                    if (preg_match('/name=["](.*)["]/', $route, $matches)) {
                        $routeName = $matches[1];
                    }

                    if (empty($routeName)) {
                        throw new \InvalidArgumentException('Incorect name', 403);
                    }

                    $routePath = ltrim($routePath, '/');

                    $lChar = substr($routePath, -1);
                    if ($lChar == ']') {
                        $routePath = $routePath . "/";
                    }

                    $result .= "\r\n";
                    $result .= "    '" . $routeName . "' => array(" . "\r\n";
                    $result .= "        '" . $routePath . "'," . "\r\n";
                    $result .= "        'task=" . $task . "&action=" . $m->name . "'," . "\r\n";
                    $result .= "    )," . "\r\n";
                }
            }
        }

        return $result;
    }

}
