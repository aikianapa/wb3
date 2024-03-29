<?php

final class wbRouter
{
    /*
    $routes = array(
      // 'url' => 'контроллер/действие/параметр1/параметр2/параметр3'
      '/' => 'MainController/index', // главная страница
      '/(p1:str)(p2:num)unit(p3:any).htm' => '/show/page/$1/$2/$3', // главная страница
      '/contacts' => 'MainController/contacts', // страница контактов
      '/blog' => 'BlogController/index', // список постов блога
      '/blog/(:num)' => 'BlogController/viewPost/$1', // просмотр отдельного поста, например, /blog/123
      '/blog/(:any)/(:num)' => 'BlogController/$1/$2', // действия над постом, например, /blog/edit/123 или /blog/dеlete/123
      '/(:any)' => 'MainController/anyAction' // все остальные запросы обрабатываются здесь
    );

    // добавляем все маршруты за раз
    wbRouter::addRoute($routes);

    // а можно добавлять по одному
    wbRouter::addRoute('/about', 'MainController/about');
    echo '<br><br>';
    // непосредственно запуск обработки
    print_r(wbRouter::getRoute());
    */

    public static $routes = array();
    private static $params = array();
    private static $names = array();
    public static $requestedUrl = '';
    public static $lang = '';

    public function init()
    {
        $this->fix($_GET, $_SERVER['QUERY_STRING']);
        // _POST и _COOKIE не фиксить - будут проблемы с фильтрами и тд
        $dir = explode("/", __DIR__);
        array_pop($dir);
        $dir = implode("/", $dir);
        $_ENV['base'] = "/tpl/";
        $_ENV['path_app'] = ($_SERVER['DOCUMENT_ROOT'] > "") ? $_SERVER['DOCUMENT_ROOT'] : $dir;
        $_ENV['path_engine'] = $_ENV['path_app'] . '/engine';
        $_ENV['dir_engine'] = __DIR__;
        $_ENV['dir_app'] = $_ENV['path_app'];
        $_ENV['path_tpl'] = $_ENV['path_app'] . $_ENV['base'];
        $_ENV['dbe'] = $_ENV['path_engine'] . '/database';             // Engine data
        $_ENV['dba'] = $_ENV['path_app'] . '/database';    // App data
        $_ENV['dbec'] = $_ENV['path_engine'] . '/database/_cache';             // Engine data
        $_ENV['dbac'] = $_ENV['path_app'] . '/database/_cache';    // App data
        $_ENV['drve'] = $_ENV['path_engine'] . '/drivers';             // Engine data
        $_ENV['drva'] = $_ENV['path_app'] . '/drivers';    // App data

        $route_a = $_ENV['path_app'] . '/router.ini';
        $route_e = $_ENV['path_engine'] . '/router.ini';
        $rese = glob($_ENV['path_engine'] . '/modules/*/router.ini');
        $resa = glob($_ENV['path_app'] . '/modules/*/router.ini');
        $res = array_merge($rese, $resa);
        foreach ($res as $r) {
            $this->addRouteFile($r);
        }
        is_file($route_a) ? $this->addRouteFile($route_a) : null;
        is_file($route_e) ? $this->addRouteFile($route_e) : null;
        $_ENV["route"] = $this->getRoute();
        if ($_ENV['route']['controller'] == 'form' && substr($_ENV['route']['uri'], -1) == '/' && $_ENV['route']['uri'] > '/') {
            // если имя страницы совпадает с именем папки - фиксим
            $_ENV['route']['uri'] = substr($_ENV['route']['uri'], 0, -1);
        }
    }


    // Добавить маршрут
    public static function addRoute($route, $destination = null, $rewrite = false)
    {
        if ($destination != null && !is_array($route)) {
            //$route = [$route => $destination];
            if ($rewrite or !isset(self::$routes[$route])) {
                self::$routes[$route] = $destination;
            }
            //self::$routes = array_merge(self::$routes, $route);
        }
    }


    public function addRouteFile($file)
    {
        $this->rewrite = false;
        if (is_file($file)) {
            $route = file($file);
        } elseif (is_file($_ENV['path_app'] . '/' . $file)) {
            $route = file($_ENV['path_app'] . '/' . $file);
        }
        if (!isset($route)) {
            return;
        }
        foreach ((array)$route  as $key => $r) {
            if (trim($r) == '[rewrite]') {
                $this->rewrite = true;
            } else {
                $r = explode('=>', $r);
                if (count($r) == 2) {
                    $this->addRoute(trim($r[0]), trim($r[1]), $this->rewrite);
                }
            }
        }
    }

    // Разделить переданный URL на компоненты
    public static function splitUrl($url)
    {
        return preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);
    }

    // Текущий обработанный URL
    public static function getCurrentUrl()
    {
        return (self::$requestedUrl ?: '/');
    }

    public static function getLang($requestedUrl)
    {
        $bc = explode('/', ltrim($requestedUrl, '/'));
        if (isset($bc[0]) && in_array($bc[0], ['ru', 'en', 'uk', 'us', 'fr', 'de', 'jp'])) {
            self::$lang = $bc[0];
            array_shift($bc);
        } elseif (isset($_COOKIE['lang']) && $_COOKIE['lang'] > '') {
            self::$lang = $_COOKIE['lang'];
        } elseif (isset($_SESSION['lang']) && $_SESSION['lang'] > '') {
            self::$lang = $_SESSION['lang'];
        }
        $requestedUrl = '/' . implode('/', $bc);
        return $requestedUrl;
    }

    // Обработка переданного URL
    public static function getRoute($requestedUrl = null)
    {
        // Если URL не передан, берем его из REQUEST_URI
        if ($requestedUrl === null) {
            $request = explode('?', $_SERVER['REQUEST_URI']);
            $uri = reset($request);
            $requestedUrl = urldecode(rtrim($uri, '/'));
            $requestedUrl = rtrim(self::getLang($requestedUrl), '/');
        }
        self::$requestedUrl = $requestedUrl;
        /*
        // если URL и маршрут полностью совпадают
        if (isset(self::$routes[$requestedUrl])) {
            self::$params = self::splitUrl(self::$routes[$requestedUrl]);
            self::$names[] = '';
            $_ENV['route'] = self::returnRoute();
            return $_ENV['route'];
        }
        */
        foreach ((array)self::$routes as $route => $uri) {
            // Заменяем wildcards на рег. выражения
            $name = null;
            self::$names = array();
            $route = str_replace(' ', '', $route);
            if (strpos($route, ':') !== false) {
                // Именование параметров
                preg_match_all("'\((\w+):(\w+)\)'", $route, $matches);
                if (isset($matches[1])) {
                    foreach ($matches[1] as $name) {
                        $route = str_replace('(' . $name . ':', '(:', $route);
                        self::$names[] = $name;
                    }
                }
                $route = str_replace('(:any)', '(.+)', str_replace('(:num)', '([0-9]+)', str_replace('(:str)', '(.[a-zA-Z]+)', $route)));
            }
            // $requestedUrl = wbNormalizePath($requestedUrl);
            if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
                $uri = str_replace('$role', $_SESSION['user']['role'], $uri);
            } else {
                $uri = str_replace(
                    '$role',
                    'null',
                    $uri
                );
            }
            if (preg_match('#^' . $route . '$#', $requestedUrl)) {
                if (strpos($uri, '$') !== false && strpos($route, '(') !== false) {
                    $uri = preg_replace('#^' . $route . '$#', $uri, $requestedUrl);
                }
                self::$params = self::splitUrl($uri);
                break; // URL обработан!
            }
        }
        $_ENV['route'] = self::returnRoute();
        return $_ENV['route'];
    }

    // Сборка ответа
    public static function returnRoute()
    {
        if (isset($_POST['_route'])) {
            return $_POST['_route'];
        }
        $ROUTE = array();
        $controller = 'form';
        $action = 'mode';
        if (array_key_exists('__json', $_POST)) {
            // Принимаем JSON данные
            $_POST = array_merge((array)$_POST, json_decode($_POST['__json'], true));
            unset($_POST['__json']);
        }
        //$form = isset(self::$params[0]) ? self::$params[0]: 'default_form';
        //$mode = isset(self::$params[1]) ? self::$params[1]: 'default_mode';
        foreach (self::$params as $i => $param) {
            if (strpos($param, ':')) {
                $tmp = explode(':', $param);
                $ROUTE[$tmp[0]] = $tmp[1];
            } else {
                if ($i == 0) {
                    $ROUTE['controller'] = $param;
                }
                if ($i == 1) {
                    $ROUTE['mode'] = $param;
                }
                if ($i > 1) {
                    $ROUTE['params'][] = $param;
                }
                if (isset(self::$names[$i])) {
                    $ROUTE['params'][self::$names[$i]] = $param;
                }
            }
        }
        $tmp = explode('?', $_SERVER['REQUEST_URI']);
        if (isset($tmp[1])) {
            parse_str($tmp[1], $get);
            if (!isset($ROUTE['params'])) {
                $ROUTE['params'] = array();
            }
            $ROUTE['params'] = (array)$ROUTE['params'] + (array)$get;
        }

        if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
            $scheme = 'https';
        } elseif (isset($_SERVER['HTTP_X_HTTPS']) and $_SERVER['HTTP_X_HTTPS'] == 1) {
            $scheme = 'https';
        } elseif (isset($_SERVER['SCHEME']) && $_SERVER['SCHEME'] > '') {
            $scheme = $_SERVER['SCHEME'];
        } elseif (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] > '') {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } else {
            $scheme = 'http';
        }
        $ROUTE['token'] = isset($_REQUEST['__token']) ? $_REQUEST['__token'] : null;
        unset($_REQUEST['__token'], $_POST['__token'], $_GET['__token']);

        $ROUTE['method'] = $_SERVER['REQUEST_METHOD'];
        $ROUTE['scheme'] = $scheme;
        $ROUTE['hostname'] = $_SERVER['HTTP_HOST'];
        $ROUTE['port'] = $_SERVER['SERVER_PORT'];
        $ROUTE['localreq'] = $_SERVER['REMOTE_ADDR'] == gethostbyname($ROUTE['hostname']) ? true : false;
        $tmp = explode('.', $ROUTE['hostname']);
        $count = count($tmp);
        if ($count == 1) {
            $ROUTE['domain'] = $tmp[count($tmp) - 1];
            $ROUTE['zone'] = '';
            $ROUTE['subdomain'] = '';
        } else {
            $ROUTE['domain'] = $tmp[count($tmp) - 2] . '.' . $tmp[count($tmp) - 1];
            $ROUTE['zone'] = $tmp[count($tmp) - 1];
            if ($tmp > 2) {
                unset($tmp[$count - 1], $tmp[$count - 2]);
                $ROUTE['subdomain'] = implode('.', $tmp);
            }
        }
        $ROUTE['host'] = $ROUTE['scheme'] . '://' . $ROUTE['hostname'];
        if ($ROUTE['port'] !== '80' and $ROUTE['port'] !== '443') {
            $ROUTE['host'] .= ':' . $ROUTE['port'];
        }

        $ROUTE['uri'] = explode('?', urldecode($_SERVER['REQUEST_URI']));
        isset($ROUTE['uri'][1]) ? $ROUTE['query_string'] = $ROUTE['uri'][1] : $ROUTE['query_string'] = '';
        //parse_str($ROUTE['query_string'],$ROUTE['query']);
        $ROUTE['query'] = $_GET; // уже преобразована для поддержки точек
        $ROUTE['uri'] = $ROUTE['uri'][0];
        $ROUTE['url'] = $ROUTE['host'] . $ROUTE['uri'];
        $ROUTE['path_app'] = $_ENV['path_app'];
        $ROUTE['path_engine'] = $_ENV['path_engine'];
        isset($_SERVER['HTTP_REFERER']) ? $ROUTE['refferer'] = $_SERVER['HTTP_REFERER'] : $ROUTE['refferer'] = "";
        if (is_file($ROUTE['path_app'] . $ROUTE['uri'])) {
            $ROUTE['file'] = $ROUTE['path_app'] . $ROUTE['uri'];
            $ROUTE['fileinfo'] = pathinfo($ROUTE['file']);
        }
        $ROUTE['lang'] = self::$lang;
        $ROUTE['_post'] = $_POST;
        $ROUTE['_get'] = $_GET;
        $cache = (isset($_SERVER['HTTP_CACHE_CONTROL']) && in_array($_SERVER['HTTP_CACHE_CONTROL'],['max-age=0','no-cache'])) ? false : true;
        if (isset($ROUTE['cache']) && $ROUTE['cache'] == 'true') $cache = true;
        if (isset($ROUTE['cache']) && $ROUTE['cache'] == 'false') $cache = false;
        $ROUTE['cache'] = $cache;
        if (count($_GET) OR count($_POST)) $cache = false;
        if (!isset($ROUTE['table']) and isset($ROUTE['form'])) {
            $ROUTE['table'] = $ROUTE['form'];
        }
        return $ROUTE;
    }

    public function fix(&$target, $source, $keep = false)
    {
        if (!$source) {
            return;
        }
        $keys = array();

        $source = preg_replace_callback(
            '/                                                                     
            # Match at start of string or &                                        
            (?:^|(?<=&))                                                           
            # Exclude cases where the period is in brackets, e.g. foo[bar.blarg]
            [^=&\[]*                                                               
            # Affected cases: periods and spaces                                   
            (?:\.|%20)                                                             
            # Keep matching until assignment, next variable, end of string or   
            # start of an array                                                    
            [^=&\[]*                                                               
            /x',
            function ($key) use (&$keys) {
                $keys[] = $key = base64_encode(urldecode($key[0]));
                return urlencode($key);
            },
            $source
        );

        if (!$keep) {
            $target = array();
        }

        parse_str($source, $data);
        foreach ($data as $key => $val) {
            // Only unprocess encoded keys
            if (!in_array($key, $keys)) {
                $target[$key] = $val;
                continue;
            }

            $key = base64_decode($key);
            $target[$key] = $val;

            if ($keep) {
                // Keep a copy in the underscore key version
                $key = preg_replace('/(\.| )/', '_', $key);
                $target[$key] = $val;
            }
        }
    }
}
?>