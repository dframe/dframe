<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Router;

use Dframe\Config\Config;
use Dframe\Router\Exceptions\InvalidArgumentException;
use Dframe\Router\Exceptions\RouterException;
use Dframe\Router\Exceptions\RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;

use function parse_url;

/**
 * Router class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Router
{
    /**
     * Path logs
     */
    protected const CACHE_DIR = APP_DIR . 'View/cache/';

    /**
     * Path logs
     */
    protected const LOG_DIR = self::CACHE_DIR . '/logs/';

    /**
     * Path logs
     */
    protected const LOG_FILE_NAME = 'router.txt';

    /**
     * Path Controller
     */
    protected const CONTROLLER_DIR = APP_DIR . 'Controller/';

    /**
     * Path Controller
     */
    public const APP_DIR = 'web' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . '';

    /**
     * Path for replaced Controller namespace
     */
    public const TASK_REPLACE_CONTROLLER_PATH = 'app' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . '';

    /**
     * @var array
     */
    public array $routeMap = [];

    /**
     * Delay Redirect.
     *
     * @var mixed
     */
    public $delay = null;

    /**
     * @var Config
     */
    public Config $routerConfig;

    /**
     * @var bool
     */
    public bool $https;

    /**
     * @var string
     */
    public string $requestPrefix;

    /**
     * @var object
     */

    public $app;

    /**
     * @var string
     */
    public string $namespace;

    /**
     * @var string
     */
    public string $controller;

    /**
     * @var string
     */
    public string $action;

    /**
     * @var array
     */
    protected array $routeMapParse = [];

    /**
     * @var string
     */
    protected string $uri;

    /**
     * @var string|null
     */
    protected ?string $subdomain = null;

    /**
     * @var string
     */
    protected string $routesFile = 'routes.php';

    /**
     * @var string
     */
    protected string $controllersFile = 'controllers.php';

    /**
     * @var string[]
     */
    protected array $usedControllers = [];

    /**
     * @var array
     */
    protected array $controllerDirs;

    /**
     * @var string
     */
    protected string $cacheDir;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * @var array
     */
    protected array $routesAdd = [];

    /**
     * Redirect.
     *
     * @param string $url The URI
     * @param int    $status
     *
     * @return Response
     */
    public static function redirect($url = '', $status = 301): Response
    {
        return Response::redirect($url, $status);
    }

    /**
     * __construct Class
     *
     * @return $this | string
     */
    public function boot()
    {
        if (!defined('HTTP_HOST') and isset($_SERVER['HTTP_HOST'])) {
            define('HTTP_HOST', $_SERVER['HTTP_HOST']);
        } elseif (!defined('HTTP_HOST')) {
            define('HTTP_HOST', '');
        }

        $this->domain = HTTP_HOST;
        $uri = explode('/', $_SERVER['SCRIPT_NAME']);
        array_pop($uri);
        $this->uri = implode('/', $uri) . '/';
        $this->uri = str_replace('/web/', '/', $this->uri);

        $this->routerConfig = Config::load('router');

        $this->setHttps($this->routerConfig->get('https', false));
        $this->routeMap = $this->routerConfig->get();
        if (empty($this->routeMap)) {
            $this->routeMap = [
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
                            '[name]=[value]',
                        ],
                    ],
                ],
            ]; // For url
        }

        $this->routeMapParse = $this->routerConfig->get('routes', $this->routeMap['routes']); // For parsing array

        // Check forced HTTPS
        if ($this->https === true) {
            $this->requestPrefix = 'https://';
            // If forced than redirect
            if (isset($_SERVER['REQUEST_SCHEME']) and ((!empty($_SERVER['REQUEST_SCHEME']) and $_SERVER['REQUEST_SCHEME'] === 'http'))) {
                return Response::create()->headers(
                    [
                        'Refresh' => $this->requestPrefix . $this->domain . '/' . $_SERVER['REQUEST_URI'],
                    ]
                )->display();
            }
        } else {
            $this->requestPrefix = $this->getRequestPrefix(false);
        }

        $routerConfig = $this->app->config['router'] ?? [];

        $this->routeMap['routes'] = array_merge($this->routeMap['routes'] ?? [], $routerConfig['routes'] ?? []);
        $this->routeMapParse = array_merge($routerConfig['routes'] ?? [], $this->routeMapParse ?? []);

        if (!defined('APP_DIR')) {
            throw new RouterException('Please Define APP_DIR in Main config.php', 500);
        }

        $cacheDir = self::CACHE_DIR;
        $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        // We save the cache dir
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir, 0777, true)) {
                throw new RuntimeException('Can\'t create cache directory');
            }
        }

        if (!is_writable($cacheDir)) {
            throw new RuntimeException('Cache directory must be writable by web server');
        }

        $annotationRoute = $this->routerConfig->get('annotation', false);
        if ($annotationRoute === true) {
            if (PHP_SAPI !== 'cli') {
                $controllerDirs = [self::CONTROLLER_DIR];
                $this->controllerDirs = [];
                foreach ($controllerDirs as $d) {
                    $realPath = realpath($d);
                    if ($realPath !== false) {
                        $this->controllerDirs[] = $realPath;
                    }
                }

                $this->generateRoutes();
            }
        }

        $routesConfig = Config::load('routes', self::CACHE_DIR)->get();

        if (!empty($routesConfig)) {
            if (is_array($routesConfig) and $this->isAssoc($routesConfig) === false) {
                foreach ($routesConfig as $value) {
                    $this->routeMapParse = array_merge($value, $this->routeMapParse);
                    $this->routeMap['routes'] = array_merge($value, $this->routeMap['routes']);
                }
            } elseif (is_array($routesConfig)) {
                $this->routeMapParse = array_merge($routesConfig, $this->routeMapParse);
                $this->routeMap['routes'] = array_merge($routesConfig, $this->routeMap['routes']);
            }
        }

        return $this;
    }

    /**
     * Set up http/https
     *
     * @param bool $option
     *
     * @return $this
     */
    public function setHttps($option = false): self
    {
        if (!in_array($option, [true, false])) {
            throw new InvalidArgumentException('Incorrect option', 403);
        }

        $this->requestPrefix = $this->getRequestPrefix($option);
        $this->https = $option;

        return $this;
    }

    /**
     * @param bool $option
     *
     * @return string
     */
    protected function getRequestPrefix(bool $option): string
    {
        if ($option === true) {
            $requestPrefix = 'https://';
        } else {
            $requestPrefix = 'http://';
            if ((isset($_SERVER['REQUEST_SCHEME']) and (!empty($_SERVER['REQUEST_SCHEME']) and ($_SERVER['REQUEST_SCHEME'] === 'https') or !empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] === 'on') or (!empty($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] === '443'))) {
                $requestPrefix = 'https://';
            }
        }

        return $requestPrefix;
    }

    /**
     * Annotations parser.
     *
     * @return void
     */
    protected function generateRoutes(): void
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

        if (!empty($routes) && $parsingNeeded) {
            $this->regenerateRouts($routes);
        }
    }

    /**
     * Find all file in controller dir.
     *
     * @return array
     */
    protected function findControllerFiles(): array
    {
        $result = [];
        foreach ($this->controllerDirs as $dir) {
            $directoryIterator = new RecursiveDirectoryIterator($dir);
            $iterator = new RecursiveIteratorIterator($directoryIterator);
            $files = new RegexIterator($iterator, '/\.php$/i', RecursiveRegexIterator::GET_MATCH);
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
     * @return string|array
     */
    protected function parseFile($file)
    {
        $routes = [];

        if (!defined('APP_DIR')) {
            throw new RouterException('Please Define APP_DIR in Main config.php', 500);
        }

        $appDir = str_replace(
            self::APP_DIR,
            '',
            APP_DIR
        );

        $task = str_replace(self::TASK_REPLACE_CONTROLLER_PATH, '', $file);
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
            // We find class info's
            $path = str_replace('Controller.php', '.php', $className . '.php');
            $path = APP_DIR . str_replace('\\', '/', $path);

            if (is_file($path)) {
                include_once $path;
            }

            $reflector = new ReflectionClass($className);

            $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $m) {
                $vars = '';
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
                        throw new InvalidArgumentException('Incorrect name', 403);
                    }

                    $routePath = trim($route2[0][0], '"');
                    $routePath = trim($routePath);
                    $routePath = ltrim($routePath, '/');
                    $lChar = substr($routePath, -1);
                    if ($lChar == ']') {
                        $routePath = $routePath . '/';
                    }
                    preg_match_all('!\[(.+?)\]!i', $routePath, $expression_);
                    $iCount = count($expression_[0]);
                    for ($i = 0; $i < $iCount; $i++) {
                        if ($expression_[0][$i] != '[params]') {
                            $vars .= '&' . $expression_[1][$i] . '=' . $expression_[0][$i];
                        }
                    }
                    $routes[$routePath] = [
                        'routeName' => $routeName,
                        'routePath' => $routePath,
                        'task' => $task,
                        'action' => $m->name,
                        'substring' => $vars,
                    ];
                }
            }

            return $routes;
        }

        return null;
    }

    /**
     * @param $routes
     *
     * @return void
     */
    public function regenerateRouts($routes): void
    {
        usort(
            $routes,
            function ($a, $b) {
                if (strlen($a['routePath']) === strlen($b['routePath'])) {
                    return 0;
                }
                return strcmp(
                    $b['routePath'],
                    $a['routePath']
                ) ?: strlen($b['routePath']) - strlen($a['routePath']);
            }
        );

        $controllerFiles = [];
        $commonFileContent = '<?php' . "\r\n" . '/**' . "\r\n" . ' * annotations router %s cache file, create ' . date(
                'c'
            ) . "\r\n" . ' */' . "\r\n\r\n";
        $routesFileContent = sprintf($commonFileContent, 'routes');
        $controllersFileContent = sprintf($commonFileContent, 'controllers');
        $routesFileContent .= 'return [';

        foreach ($routes as $key => $route) {
            $routesFileContent .= "\r\n";
            $routesFileContent .= "   '" . $route['routeName'] . "' => [" . "\r\n";
            $routesFileContent .= "     '" . $route['routePath'] . "'," . "\r\n";
            $routesFileContent .= "     'task=" . $route['task'] . "&action=" . $route['action'] . $route['substring'] . "'," . "\r\n";
            $routesFileContent .= "   ]," . "\r\n";
        }

        $routesFileContent = rtrim($routesFileContent, ',' . "\r\n");
        $routesFileContent .= "\r\n" . "];";
        file_put_contents($this->cacheDir . $this->routesFile, $routesFileContent);
        $usedControllers = (count($controllerFiles) > 0) ? '$this->usedControllers = [\'' . implode(
                '\',\'',
                $controllerFiles
            ) . '\'];' : '';
        file_put_contents($this->cacheDir . $this->controllersFile, $controllersFileContent . $usedControllers);
    }

    /**
     * @param array $arr
     *
     * @return bool
     */
    public function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @return array|string[]
     */
    public function getRoutes()
    {
        return $this->routeMap;
    }

    /**
     * @param $routes
     *
     * @return void
     */
    public function setRoutes($routes): void
    {
        $this->routeMap = array_merge($this->routeMap, $routes);
    }

    /**
     * Check current active page
     *
     * @param string $url
     *
     * @return bool
     */
    public function isActive(string $url): bool
    {
        if ($this->makeUrl($url, true) === str_replace($this->uri, '', $_SERVER['REQUEST_URI'])) {
            return true;
        }

        return false;
    }

    /**
     * Generate url
     *
     * @param string|null $url
     * @param string|bool $onlyExt
     *
     * @return null|string
     */
    public function makeUrl(string $url = null, $onlyExt = false): ?string
    {
        $paramsHook = explode('#', $url);
        $params = explode('?', $paramsHook[0]);
        $params_ = explode('/', $params[0]);
        $task = $params_[0];
        $action = null;

        if (isset($params_[1]) and !empty($params_[1])) {
            $action = $params_[1];
        }

        if (isset($params[1])) {
            parse_str($params[1], $params);
        } else {
            $params = [];
        }

        $findKey = explode('?', $url);
        if (isset($findKey[0])) {
            $findKey = $findKey[0];
        }

        if (defined('MOD_REWRITE') and MOD_REWRITE === true) {
            $expressionUrl = $this->expressionUrlWithModRewrite($findKey, $params, $task, $action);
        } else {
            $expressionUrl = $this->expressionUrlWithoutModRewrite($findKey, $params, $task, $action);
        }

        $parsedUrl = parse_url($this->domain);

        if (isset($parsedUrl['scheme'])) {
            $this->requestPrefix = $parsedUrl['scheme'] . '://';
            $this->domain = ltrim($this->domain, $parsedUrl['scheme'] . '://');
        }

        $HTTP_HOST = $this->domain;

        if (!empty($this->subdomain)) {
            $HTTP_HOST = $this->subdomain . '.' . $this->domain;
        }

        $url = null;
        if ($onlyExt === false) {
            $url = $this->requestPrefix . $HTTP_HOST . '/';
        }

        $url .= $expressionUrl;
        $url = rtrim($url, '/');

        unset($this->subdomain);
        $this->domain = HTTP_HOST;
        $this->setHttps($this->routerConfig->get('https', false));

        return $url;
    }

    /**
     * @param string $findKey
     * @param array  $params
     * @param string $task
     * @param string $action
     *
     * @return mixed|string
     */
    protected function expressionUrlWithModRewrite($findKey, $params, $task, $action)
    {
        if (isset($this->routeMap['routes'][$findKey])) {
            $expressionUrl = $this->routeMap['routes'][$findKey][0];
            foreach ($params as $key => $value) {
                $expressionUrl = str_replace('[' . $key . ']', $value, $expressionUrl, $count);
                if ($count > 0) {
                    unset($params[$key]);
                }
            }

            if (isset($params)) {
                if (isset($this->routeMap['routes'][$findKey]['_params'])) {
                    $expressionUrl = str_replace(
                        '[params]',
                        $this->parseParams($this->routeMap['routes'][$findKey]['_params'][0], $params),
                        $expressionUrl
                    );
                } elseif (!empty($params)) {
                    $expressionUrl = $expressionUrl . '?' . http_build_query($params);
                }
            }
        } else {
            $expressionUrl = $this->routeMap['routes']['default'][0];
            $expressionUrl = str_replace('[task]', $task ?? "", $expressionUrl);
            $expressionUrl = str_replace('[action]', $action ?? "", $expressionUrl);
            if (isset($params)) {
                $expressionUrl = str_replace(
                    '[params]',
                    $this->parseParams($this->routeMap['routes']['default']['_params'][0], $params) ?? "",
                    $expressionUrl
                );
            }
        }

        return $expressionUrl;
    }

    /**
     * Parse url params into a 'request'
     *
     * @param string $routing
     * @param array  $params
     *
     * @return string
     */
    protected function parseParams($routing, $params): ?string
    {
        $return = null;

        foreach ($params as $key => $value) {
            $return .= str_replace(['[name]', '[value]'], [$key, $value], $routing);
        }

        return $return;
    }

    /**
     * @param string $findKey
     * @param array  $params
     * @param string $task
     * @param string $action
     *
     * @return mixed|string
     */
    protected function expressionUrlWithoutModRewrite($findKey, $params, $task, $action)
    {
        if (empty($task)) {
            $expressionUrl = '';
        } else {
            if (isset($this->routeMap['routes'][$findKey])) {
                $expressionUrl0 = $this->routeMap['routes'][$findKey][1];
                foreach ($params as $key => $value) {
                    $expressionUrl0 = str_replace('[' . $key . ']', $value, $expressionUrl0, $count);
                    if ($count > 0) {
                        unset($params[$key]);
                    }
                }

                $expressionUrl = $expressionUrl0;
            } else {
                $expressionUrl = 'task=' . $task;
                if (!empty($action)) {
                    $expressionUrl = 'task=' . $task . '&action=' . $action;
                }
            }

            if (!empty($params)) {
                if (!empty($expressionUrl)) {
                    $expressionUrl .= '&';
                }
                $expressionUrl = $expressionUrl . http_build_query($params);
            }
            $expressionUrl = 'index.php?' . $expressionUrl;
        }

        return $expressionUrl;
    }

    /**
     * @param null|string $url
     * @param null|string $path
     *
     * @return null|string
     */
    public function publicWeb($url = null, $path = null): ?string
    {
        if (is_null($path)) {
            $path = $this->routeMap['publicWeb'];
        }

        $expressionUrl = $url;
        $url = $this->requestPrefix . $this->domain . '/' . $path;
        $url .= $expressionUrl;

        unset($this->subdomain);
        $this->domain = HTTP_HOST;
        //$this->setHttps($this->routerConfig->get('https', false));

        return $url;
    }

    /**
     * Parse request.
     *
     * @return array
     */
    public function parseGets(): array
    {
        $request = preg_replace('!' . $this->uri . '(.*)$!i', '$1', $_SERVER['REQUEST_URI']);
        if (defined('MOD_REWRITE') and MOD_REWRITE === true) {
            if (substr($request, -1) != '/') {
                $request .= '/';
            }

            $parseUrl = $this->parseUrl($request);
            $this->namespace = $parseUrl['v']['namespace'] ?? '';
            parse_str($parseUrl['sVars'], $gets);

            $this->controller = !empty($gets['task']) ? $gets['task'] : $this->routeMap['NAME_CONTROLLER'];
            unset($gets['task']);

            $this->action = !empty($gets['action']) ? $gets['action'] : $this->routeMap['NAME_METHOD'];
            unset($gets['action']);
            $_GET = array_merge($_GET, $gets);
        } else {
            $this->controller = !empty($_GET['task']) ? $_GET['task'] : $this->routeMap['NAME_CONTROLLER'];
            $this->action = !empty($_GET['action']) ? $_GET['action'] : $this->routeMap['NAME_METHOD'];
        }

        $_GET['task'] = $this->controller;
        $_GET['action'] = $this->action;

        return $parseUrl ?? [];
    }

    /**
     * Match given request
     *
     * @param string $request
     * @param string|null $routingParse
     *
     * @return array
     */
    protected function parseUrl($request, $routingParse = null): array
    {
        $vars = null;
        $args = [];
        $v = [];

        if ($routingParse === null) {
            $routingParse = $this->routeMapParse;
        }

        $path = trim(explode('?', $request)[0], '/');

        foreach ($routingParse as $k => $v) {
            if (!is_array($v)) {
                continue;
            }

            preg_match_all('!\[(.+?)\]!i', $v[0], $expression_);
            $expressionMatch = preg_replace_callback(
                '!\[(.+?)\]!i',
                function ($m) use ($k) {
                    return $this->transformParam($m[1], $k);
                },
                $v[0]
            );

            if (preg_match_all('!^' . $expressionMatch . '$!i', $path, $expression__)) {
                $args = [];
                $expression = [];

                if (isset($v['args'])) {
                    $args = $v['args'];
                }

                foreach ($expression__ as $k_ => $v_) {
                    foreach ($v_ as $kkk => $vvv) {
                        if (!isset($expression_[1][$k_ - 1])) {
                            $expression_[1][$k_ - 1] = null;
                        }

                        if ($kkk > 0) {
                            $expression[] = [$expression_[1][$k_ - 1] . '_' . $kkk, $vvv];
                        } else {
                            $expression[] = [$expression_[1][$k_ - 1], $vvv];
                        }
                    }
                }

                unset($expression[0]);
                $iCount = count($expression__[0]);

                if ($iCount > 1) {
                    for ($i = 0; $i < $iCount; $i++) {
                        if ($i > 0) {
                            $vars .= '&' . preg_replace('!\[(.+?)\]!i', '[$1_' . $i . ']', $v[1]);
                        } else {
                            $vars = '&' . $v[1];
                        }
                    }
                } else {
                    if (isset($v['methods'][$_SERVER['REQUEST_METHOD']])) {
                        $vars = '&' . $v['methods'][$_SERVER['REQUEST_METHOD']];
                    } elseif (isset($v[1])) {
                        $vars = '&' . $v[1];
                    } else {
                        continue;
                    }
                }

                foreach ($expression as $v_) {
                    if (!isset($v['_' . $v_[0]])) {
                        $v['_' . $v_[0]] = null;
                    }

                    if (!is_array($v['_' . $v_[0]])) {
                        foreach ($args as $key => $value) {
                            $args[$key] = str_replace('[' . $v_[0] . ']', $v_[1], $args[$key]);
                        }
                        $vars = str_replace('[' . $v_[0] . ']', $v_[1], $vars);
                    } else {
                        $vars = $vars . $this->parseUrl($v_[1], [$v['_' . $v_[0]]])['sVars'];
                    }
                }

                break;
            }
        }

        if (isset($this->app->debug)) {
            $this->app->debug->addHeader(['X-DF-Debug-sVars' => $vars]);
        }

        return ['v' => $v, 'sVars' => $vars, 'args' => $args];
    }

    /**
     * Prepares the regexp
     *
     * @param string $param
     * @param string $k
     *
     * @return string
     */
    protected function transformParam($param, $k): string
    {
        if (isset($this->routeMapParse[$k][$param]) and !is_array($this->routeMapParse[$k][$param])) {
            return $this->routeMapParse[$k][$param];
        } else {
            return '(.+?)';
        }
    }

    /**
     * Return Current path
     *
     * @return string
     */
    public function currentPath(): string
    {
        $request = preg_replace('!' . $this->uri . '(.*)$!i', '$1', $_SERVER['REQUEST_URI']);
        if (defined('MOD_REWRITE') and MOD_REWRITE === true) {
            if (substr($request, -1) != '/') {
                $request .= '/';
            }

            $parseUrl = $this->parseUrl($request);
            $gets = $parseUrl['sVars'];
        } else {
            $gets = $_SERVER['QUERY_STRING'];
        }

        return $gets;
    }

    /**
     * Redirect delay.
     *
     * @param int $delay time in seconds
     *
     * @return self
     */
    public function delay(int $delay): self
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Set up subdomain prefix.
     *
     * @param string $subdomain
     *
     * @return self
     */
    public function subdomain($subdomain): self
    {
        $this->subdomain = $subdomain;

        return $this;
    }

    /**
     * Set up domain.
     *
     * @param string $domain
     *
     * @return self
     */
    public function domain($domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Set up new route.
     *
     * @param array $newRoute
     *
     * @return void
     */
    public function addRoute($newRoute): void
    {
        $this->routeMap['routes'] = array_merge($this->routeMap['routes'], $newRoute);
        $this->routeMapParse = array_merge($this->routeMapParse, $newRoute);

        foreach ($newRoute as $name => $value) {
            $this->routesAdd[$value[0]] = $newRoute;
        }

        $return = '<?php return ';
        $route = [];
        foreach ($this->routesAdd as $value) {
            $route[] = $value;
        }
        $return .= var_export($route, true);

        $return .= ';';
        file_put_contents($this->cacheDir . $this->routesFile, $return);
    }

    /**
     * @return Response
     */
    public function response(): Response
    {
        return new Response();
    }
}
