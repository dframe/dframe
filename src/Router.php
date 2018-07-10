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

use Dframe\Router\Exceptions\InvalidArgumentException;
use Dframe\Router\Exceptions\RuntimeException;

/**
 * Router class
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Router
{
    /**
     * @var string[]
     */
    public $aRouting = [];

    /**
     * @var string[]
     */
    private $aRoutingParse = [];

    /**
     * @var string
     */
    private $sURI;

    /**
     * @var boolean
     */
    private $subdomain = false;

    /**
     * Delay Redirect
     */
    public $delay = null;

    /**
     * @var string[]
     */
    public $parseArgs = [];

    /**
     * @var string
     */
    private $routesFile = 'routes.php';

    /**
     * @var string
     */
    private $controllersFile = 'controllers.php';

    /**
     * @var string[]
     */
    private $usedControllers = [];

    /**
     * @var string
     */
    private $controllerDirs = APP_DIR . 'Controller/';

    /**
     * @var string
     */
    private $cacheDir = APP_DIR . 'View/cache/';

    /**
     * __construct Class
     */
    public function __construct()
    {
        if (ini_get('display_errors') == "on") {
            $this->debug = new Debug();
        }

        if (!defined('HTTP_HOST') and isset($_SERVER['HTTP_HOST'])) {
            define('HTTP_HOST', $_SERVER['HTTP_HOST']);
        } elseif (!defined('HTTP_HOST')) {
            define('HTTP_HOST', '');
        }

        $this->domain = HTTP_HOST;
        $aURI = explode('/', $_SERVER['SCRIPT_NAME']);
        array_pop($aURI);
        $this->sURI = implode('/', $aURI) . '/';
        $this->sURI = str_replace('/web/', '/', $this->sURI);

        $this->routerConfig = Config::load('router');

        $this->setHttps($this->routerConfig->get('https', false));
        $this->aRouting = $this->routerConfig->get();
        if (empty($this->aRouting)) {
            $this->aRouting = [
                'https' => false,
                'NAME_CONTROLLER' => 'page',
                'NAME_METHOD' => 'login',
                'publicWeb' => '',

                'routes' => [
                    'default' => [
                        '[task]/[action]/[params]',
                        'task=[task]&action=[action]',
                        'params' => '(.*)',
                        '_params' => [
                            '[name]/[value]/',
                            '[name]=[value]'
                        ]
                    ],
                ]
            ]; // For url
        }



        $this->aRoutingParse = $this->routerConfig->get('routes', $this->aRouting['routes']); // For parsing array

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
            if ((isset($_SERVER['REQUEST_SCHEME']) and (!empty($_SERVER['REQUEST_SCHEME']) and ($_SERVER['REQUEST_SCHEME'] == 'https') or !empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') or (!empty($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == '443'))) {
                $this->requestPrefix = 'https://';
            }
        }

        $annotationRoute = $this->routerConfig->get('annotation', false);
        if ($annotationRoute === true) {

            if (PHP_SAPI !== 'cli') {
                $routesFile = 'routes.php';
                $controllersFile = 'controllers.php';
                $usedControllers = [];
                $controllerDirs = APP_DIR . 'Controller/';
                $cacheDir = APP_DIR . 'View/cache/';

        // We save controller dirs
                if (is_string($controllerDirs)) {
                    $controllerDirs = [$controllerDirs];
                }

                if (!is_array($controllerDirs)) {
                    throw new InvalidArgumentException('Controllers directory must be either string or array');
                }

                $this->controllerDirs = [];
                foreach ($controllerDirs as $d) {
                    $realPath = realPath($d);
                    if ($realPath !== false) {
                        $this->controllerDirs[] = $realPath;
                    }
                }

            // We save the cache dir
                if (!is_dir($cacheDir)) {
                    if (!mkdir($cacheDir, 0777, true)) {
                        throw new RuntimeException('Can\'t create cache directory');
                    }
                }

                if (!is_writable($cacheDir)) {
                    throw new RuntimeException('Cache directory must be writable by web server');
                }

                $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $this->generateRoutes();

                $routesConfig = Config::load('routes', APP_DIR . 'View/cache/')->get();
                if (!empty($routesConfig)) {
                    $this->aRoutingParse = array_merge($routesConfig, $this->aRoutingParse);
                    $this->aRouting['routes'] = array_merge($routesConfig, $this->aRouting['routes']);
                }
            }
        }
    }

    /**
     * Display Controller result
     * 
     * @param boolen|Response
     * 
     */
    public function run($controller = null, $action = null, $arg = [])
    {
        if (is_null($controller) and is_null($action)) {
            $this->parseGets();
            $controller = $_GET['task'];
            $action = $_GET['action'];
        }

        $arg = $this->parseArgs;
        $bootstrap = new \Bootstrap();
        $bootstrap->router = $this;
        $loader = new Loader($bootstrap);
        $loadController = $loader->loadController($controller); // Loading Controller class

        $controller = $loadController->returnController;
        $response = [];

        if (method_exists($controller, 'start')) {
            $response[] = 'start';
        }

        if (method_exists($controller, 'init')) {
            $response[] = 'init';
        }

        if (method_exists($controller, $action) or is_callable([$controller, $action])) {
            $response[] = $action;
        }

        if (method_exists($controller, 'end')) {
            $response[] = 'end';
        }

        foreach ($response as $key => $data) {
            if (is_callable([$controller, $data])) {
                $run = $controller->$data();
                if ($run instanceof Response) {
                    if (isset($this->debug)) {
                        $this->debug->addHeader(array('X-DF-Debug-Method' => $action));
                        $run->headers($this->debug->getHeader());
                    }
                    return $run->display();
                }
            }
        }

        return true;
    }

    /**
     * Set up http/https
     * 
     * @param boolen $option
     * 
     */

    public function setHttps($option = false)
    {
        if (!in_array($option, [true, false])) {
            throw new InvalidArgumentException('Incorect option', 403);
        }

        if ($option == true) {
            $this->requestPrefix = 'https://';
        } else {
            $this->requestPrefix = 'http://';
            if ((isset($_SERVER['REQUEST_SCHEME']) and (!empty($_SERVER['REQUEST_SCHEME']) and ($_SERVER['REQUEST_SCHEME'] == 'https') or !empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') or (!empty($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == '443'))) {
                $this->requestPrefix = 'https://';
            }
        }


        $this->https = $option;
        return $this;
    }

    /**
     * Check current active page
     * 
     * @param string|array $url
     * 
     * @return boolen
     */
    public function isActive($url)
    {
        if ($this->makeUrl($url, true) == str_replace($this->sURI, '', $_SERVER['REQUEST_URI'])) {
            return true;
        }

        return false;
    }

    /**
     * Gerenate full url for files
     * 
     * @param string $sUrl
     * @param string $path
     * 
     * @return string
     */
    public function publicWeb($sUrl = null, $path = null)
    {
        if (is_null($path)) {
            $path = $this->aRouting['publicWeb'];
        }

        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix . $this->domain . '/' . $path;
        $sUrl .= $sExpressionUrl;

        unset($this->subdomain);
        $this->domain = HTTP_HOST;
        $this->setHttps($this->routerConfig->get('https', false));

        return $sUrl;
    }

    /**
     * Gerenate url
     * 
     * @param string $sUrl
     * @param string $onlyExt
     * 
     * @return string
     */
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
            $aParams = [];
        }

        $findKey = explode('?', $sUrl);
        if (isset($findKey[0])) {
            $findKey = $findKey[0];
        }

        if (defined('MOD_REWRITE') and MOD_REWRITE == true) {

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
                        $sExpressionUrl = str_replace('[params]', $this->parseParams($this->aRouting['routes'][$findKey]['_params'][0], $aParams), $sExpressionUrl);
                    } elseif (!empty($aParams)) {
                        $sExpressionUrl = $sExpressionUrl . "?" . http_build_query($aParams);
                    }
                }
            } else {
                $sExpressionUrl = $this->aRouting['routes']['default'][0];
                $sExpressionUrl = str_replace('[task]', $sTask, $sExpressionUrl);
                $sExpressionUrl = str_replace('[action]', $sAction, $sExpressionUrl);
                if (isset($aParams)) {
                    $sExpressionUrl = str_replace('[params]', $this->parseParams($this->aRouting['routes']['default']['_params'][0], $aParams), $sExpressionUrl);
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

        if (!empty($this->subdomain)) {
            $HTTP_HOST = $this->subdomain . '.' . $this->domain;
        }

        $sUrl = null;
        if ($onlyExt === false) {
            $sUrl = $this->requestPrefix . $HTTP_HOST . '/';
        }

        $sUrl .= $sExpressionUrl;
        $sUrl = rtrim($sUrl, '/');


        unset($this->subdomain);
        $this->domain = HTTP_HOST;
        $this->setHttps($this->routerConfig->get('https', false));

        return $sUrl;
    }

    /**
     * Parse url params into a 'request' 
     * 
     * @param string $sRouting
     * @param string $aParams
     * 
     * @return string
     */
    private function parseParams($sRouting, $aParams)
    {
        $sReturn = null;

        foreach ($aParams as $key => $value) {
            $sReturn .= str_replace(['[name]', '[value]'], [$key, $value], $sRouting);
        }

        return $sReturn;
    }

    /**
     * Parse request
     */
    public function parseGets()
    {
        $sRequest = preg_replace('!' . $this->sURI . '(.*)$!i', '$1', $_SERVER['REQUEST_URI']);
        if (defined('MOD_REWRITE') and MOD_REWRITE == true) {

            if (substr($sRequest, -1) != '/') {
                $sRequest .= '/';
            }

            $sGets = $this->parseUrl($sRequest);
            $sGets = str_replace('?', '&', $sGets);
            parse_str($sGets, $aGets);
            $_GET['task'] = !empty($aGets['task']) ? $aGets['task'] : $this->aRouting['NAME_CONTROLLER'];
            unset($aGets['task']);
            $_GET['action'] = !empty($aGets['action']) ? $aGets['action'] : $this->aRouting['NAME_METHOD'];
            unset($aGets['action']);
            $_GET = array_merge($_GET, $aGets);
        } else {
            $_GET['task'] = !empty($_GET['task']) ? $_GET['task'] : $this->aRouting['NAME_CONTROLLER'];
            $_GET['action'] = !empty($_GET['action']) ? $_GET['action'] : $this->aRouting['NAME_METHOD'];
        }
    }


    /**
     * Return Current path
     * 
     * @return string
     */
    public function currentPath()
    {
        $sRequest = preg_replace('!' . $this->sURI . '(.*)$!i', '$1', $_SERVER['REQUEST_URI']);
        if (defined('MOD_REWRITE') and MOD_REWRITE == true) {

            if (substr($sRequest, -1) != '/') {
                $sRequest .= '/';
            }

            $sGets = $this->parseUrl($sRequest);
            $sGets = str_replace('?', '&', $sGets);
        }

        return $sGets;
    }

    /**
     * Match given request
     * 
     * @param string $sRequest
     * @param string $routingParse
     * 
     * @return string
     */
    private function parseUrl($sRequest, $routingParse = null)
    {
        $sVars = null;

        if ($routingParse == null) {
            $routingParse = $this->aRoutingParse;
        }

        $sRequest = str_replace('?', '&', $sRequest);

        foreach ($routingParse as $k => $v) {
            if (!is_array($v)) {
                continue;
            }

            preg_match_all('!\[(.+?)\]!i', $v[0], $aExpression_);
            $sExpression = preg_replace_callback(
                '!\[(.+?)\]!i',
                function ($m) use ($k) {
                    return $this->transformParam($m[1], $k);
                },
                $v[0]
            );

            if (preg_match_all('!' . $sExpression . '!i', $sRequest, $aExpression__)) {
                $args = [];

                if (isset($v['args'])) {
                    $args = $v['args'];
                }

                foreach ($aExpression__ as $k_ => $v_) {
                    foreach ($v_ as $kkk => $vvv) {
                        if (!isset($aExpression_[1][$k_ - 1])) {
                            $aExpression_[1][$k_ - 1] = null;
                        }

                        if ($kkk > 0) {
                            $aExpression[] = [$aExpression_[1][$k_ - 1] . '_' . $kkk, $vvv];
                        } else {
                            $aExpression[] = [$aExpression_[1][$k_ - 1], $vvv];
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
                        $sVars = $sVars . $this->parseUrl($v_[1], [$v['_' . $v_[0]]]);
                    }
                }
                $this->parseArgs = $args;
                break;
            }
        }
        
        if(isset($this->debug)){
            $this->debug->addHeader(array('X-DF-Debug-sVars' => $sVars));
        }
        return $sVars;
    }

    /**
     * Prepares the regexp
     * 
     * @param string $sParam
     * @param string $k
     * 
     * @return string
     */
    private function transformParam($sParam, $k)
    {
        if (isset($this->aRoutingParse[$k][$sParam]) and !is_array($this->aRoutingParse[$k][$sParam])) {
            return $this->aRoutingParse[$k][$sParam];
        } else {
            return '(.+?)';
        }
    }

    /**
     * Redirect
     *
     * @param string $url The URI
     * @param string $status
     * 
     * @return object
     */
    public static function redirect($url = '', $status = 301)
    {
        return Response::redirect($url, $status);
    }

    /**
     * Redirect delay
     *
     * @param string $delay time in seconds
     * 
     * @return object
     */
    public function delay(int $delay)
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * Set up subdomain prefix
     *
     * @param string $subdomain
     * 
     * @return object
     */
    public function subdomain($subdomain)
    {
        $this->subdomain = $subdomain;
        return $this;
    }

    /**
     * Set up domain
     *
     * @param string $domain
     * 
     * @return object
     */
    public function domain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Set up new route
     *
     * @param string $newRoute
     * 
     * @return object
     */
    public function addRoute($newRoute)
    {
        $this->aRouting['routes'] = array_merge($this->aRouting['routes'], $newRoute);
        $this->aRoutingParse = array_merge($this->aRoutingParse, $newRoute);
    }

    public function response()
    {
        return new Response();
    }

    /**
     * Annotations parser
     */
    private function generateRoutes()
    {
        $parsingNeeded = !file_exists($this->cacheDir . $this->routesFile);
        // We look for controller files
        $files = $this->findControllerFiles();

        // We check if there has been modifications since last cache generation
        if (!$parsingNeeded) {
            $routesCacheMtime = filemtime($this->cacheDir . $this->routesFile);
            foreach ($files as $file => $mtime) {
                if ($mtime > $routesCacheMtime) {
                    $parsingNeeded = true;
                    break;
                }
            }
        }

        // We look for deleted controller files
        if (!$parsingNeeded and file_exists($this->cacheDir . $this->controllersFile)) {
            include_once $this->cacheDir . $this->controllersFile;
            foreach ($this->usedControllers as $controllerFile) {
                if (!file_exists($controllerFile)) {
                    $parsingNeeded = true;
                    break;
                }
            }
        }

        $routes = [];
        foreach ($files as $file => $mtime) {
            $parseFile = $this->parseFile($file);
            if (!empty($parseFile)) {
                $routes = array_merge($routes, $parseFile);
            }
        }

        if (!empty($routes)) {

            usort(
                $routes,
                function ($a, $b) {
                    if (strlen($a['routePath']) == strlen($b['routePath'])) return 0;
                    return strcmp($b['routePath'], $a['routePath']) ? : strlen($b['routePath']) - strlen($a['routePath']);
                }
            );
          
            // We regenerate cache file if needed
            if ($parsingNeeded) {
                $controllerFiles = [];
                $commonFileContent = '<?php' . "\r\n" . '/**' . "\r\n" . ' * annotations router %s cache file, create ' . date('c') . "\r\n" . ' */' . "\r\n\r\n";
                $routesFileContent = sprintf($commonFileContent, 'routes');
                $controllersFileContent = sprintf($commonFileContent, 'controllers');
                $routesFileContent .= 'return array(';

                foreach ($routes as $key => $route) {
                    $routesFileContent .= "\r\n";
                    $routesFileContent .= "    '" . $route['routeName'] . "' => array(" . "\r\n";
                    $routesFileContent .= "        '" . $route['routePath'] . "'," . "\r\n";
                    $routesFileContent .= "        'task=" . $route['task'] . "&action=" . $route['action'] . $route['substring'] . "'," . "\r\n";
                    $routesFileContent .= "    )," . "\r\n";
                }

                $routesFileContent = rtrim($routesFileContent, ',' . "\r\n");
                $routesFileContent .= "\r\n" . ");";
                file_put_contents($this->cacheDir . $this->routesFile, $routesFileContent);
                $usedControllers = (count($controllerFiles) > 0) ? '$this->usedControllers = [\'' . join('\',\'', $controllerFiles) . '\'];' : '';
                file_put_contents($this->cacheDir . $this->controllersFile, $controllersFileContent . $usedControllers);
            }
        }
    }

    /**
     * Find all file in controller dir
     */
    private function findControllerFiles()
    {
        $result = [];
        foreach ($this->controllerDirs as $dir) {
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
     * Parsing annotations
     * 
     * @param string $file
     * 
     * @return string
     */
    private function parseFile($file)
    {
        $result = '';
        $routes = [];
        $appDir = str_replace('web/../app/', '', APP_DIR);
        $task = str_replace($appDir . 'app' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . '', '', $file);
        $task = rtrim($task, '.php');
        $task = str_replace(DIRECTORY_SEPARATOR, ',', $task);

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
            $path = APP_DIR . str_replace('\\', "/", $path);
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

            $sVars = null;
            foreach ($methods as $m) {
                $sVars = null;
                if ($m->isStatic()) {
                    continue;
                }

                if (preg_match('/@Route\(\s*(.*)*\)/', $m->getDocComment(), $matches) === 1) {
                    preg_match_all('/(?![(@Route()])([@a-zA-Z0-9"[\]:_> \'(.*)\/[=])+["]/', $matches[0], $route2);
                    $routeName = null;
                    if (preg_match('/name=["](.*)["]/', $route2[0][1], $matches)) {
                        $routeName = $matches[1];
                    }
                    if (empty($routeName)) {
                        throw new InvalidArgumentException('Incorect name', 403);
                    }

                    $routePath = trim($route2[0][0], '"');
                    $routePath = trim($routePath);
                    $routePath = ltrim($routePath, '/');
                    $lChar = substr($routePath, -1);
                    if ($lChar == ']') {
                        $routePath = $routePath . "/";
                    }
                    preg_match_all('!\[(.+?)\]!i', $routePath, $aExpression_);
                    $iCount = count($aExpression_[0]);
                    for ($i = 0; $i < $iCount; $i++) {
                        if ($aExpression_[0][$i] != '[params]') {
                            $sVars .= '&' . $aExpression_[1][$i] . '=' . $aExpression_[0][$i];
                        }
                    }
                    $routes[$routePath] = [
                        'routeName' => $routeName,
                        'routePath' => $routePath,
                        'task' => $task,
                        'action' => $m->name,
                        'substring' => $sVars
                    ];

                }
            }

            return $routes;
        }
    }
}
