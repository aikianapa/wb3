<?php
require_once __DIR__. '/modules/cms/cms_formsclass.php'; // important!!!

// Author: oleg_frolov@mail.ru
use Nahid\JsonQ\Jsonq;
use Adbar\Dot;

//use Spatie\Async\Pool;

class wbApp
{
    public $settings;
    public $route;
    public $item;
    public $dom;
    public $out;
    public $template;
    public $router;
    public $render;
    public $vars;
    public $lang;
    public $dict;
    public $data;
    public $tpl;
    public $user;
    public $token;

    public function __construct($settings=[])
    {
        $this->settings = (object)[];

        foreach ($settings as $key => $val) {
            $this->settings->$key = $val;
        }

        isset($this->settings->driver) ? null : $this->settings->driver = 'json' ;
        $this->vars = new Dot();
        $vars = [
          '_env'  => &$_ENV,
          '_get'  => &$_GET,
          '_srv'  => &$_SERVER,
          '_post' => &$_POST,
          '_req'  => &$_REQUEST,
          '_route'=> &$_ENV['route'],
          '_sett' => &$_ENV['settings'],
          '_var'  => &$_ENV['variables'],
          '_sess' => &$_SESSION,
          '_user' => &$_SESSION['user'],
          '_cookie'=>&$_COOKIE,
          '_cook'  =>&$_COOKIE,
          '_mode' => &$_ENV['route']['mode'],
          '_form' => &$_ENV['route']['form'],
          '_item' => &$_ENV['route']['item'],
          '_param'=> &$_ENV['route']['param'],
          '_locale'=> &$_ENV['locale'],
          '_lang'   => &$this->lang
      ];
        $this->vars->setReference($vars);
        $this->initApp();
    }

    public function __call($func, $params)
    {
        $wbfunc='wb'.$func;
        $_ENV['app'] = &$this;
        $res = null;
        if (method_exists($this, $func)) {
            $this->$func();
        } elseif (is_callable($wbfunc)) {
            $prms = [];
            foreach ($params as $k => $i) {
                $prms[] = '$params['.$k.']';
            }
            eval('$res = $wbfunc('.implode(',', $prms).');');
            return $res;
        } elseif (!is_callable($func)) {
            die("Function {$wbfunc} not defined");
        } else {
            $par = [];
            for ($i=0; $i<count($params); $i++) {
                $par[] = '$params['.$i.']';
            }
            eval('$res = $func('.implode(",", $par).');');
            return $res;
        }
    }

    public function getCacheId()
    {
        $uri = $this->route->uri;
        $lang = $this->vars('_sess.lang');
        return md5($uri.'_'.$lang);
    }

    public function setCache($out = '')
    {
        if (!isset($_GET['update']) and (count($_GET) or count($_POST))) {
            return;
        }
        $cid = $this->getCacheId();
        $sub = substr($cid, 0, 2);
        $dir = $this->vars('_env.dbac').'/'.$sub;
        $name = $dir.'/'.$cid.'.html';
        strpos(' '.$out, '<!DOCTYPE html>') ? null : $out = '<!DOCTYPE html>'.$out;
        is_dir($dir) ? null : mkdir($dir, 0777, true);
        file_put_contents($name, $out, LOCK_EX);
        $lastModified = filemtime($name);
    }

    public function cacheControl()
    {
        $this->vars('_sett.devmode') == 'on' ? $cache = null : $cache = true;
        if ($cache && isset($_SERVER['HTTP_CACHE_CONTROL'])) {
            parse_str($_SERVER['HTTP_CACHE_CONTROL'], $cc);
            isset($cc['no-cache']) ? $cache = null : null;
        }
        $cache && ((!count($_POST) and isset($_GET['update']) and count($_GET) == 1) or count($_POST) or count($_GET)) ? $cache = null : null;
        return $cache;
    }



    public function getCache()
    {
        $cache = $this->cacheControl();
        if ($cache == null) {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            return null;
        }

        $cid = $this->getCacheId();
        $sub = substr($cid, 0, 2);
        $dir = $this->vars('_env.dbac').'/'.$sub;
        $name = $dir.'/'.$cid.'.html';

        if (is_file($name)) {
            if ($this->vars('_sett.cache') > ''  and ((time() - filectime($name)) >= intval($this->vars('_sett.cache')))) {
                // Делаем асинхронный запрос с обновлением кэша
                header("Cache-Control: no-cache, no-store, must-revalidate");
                header("Pragma: no-cache");
                //$this->shadow($this->route->uri);
                return null;
            } else {
                header("Cache-control: public");
                header("Pragma: cache");
                header("Expires: " . gmdate("D, d M Y H:i:s", time()+$this->vars('_sett.cache')) . " GMT");
                header("Cache-Control: max-age=".$this->vars('_sett.cache'));
            }
            return file_get_contents($name);
        }
        return null;
    }


    public function shadow($uri)
    {
        // отправка url запроса без ожидания ответа
        $url = $this->route->host.$uri;
        $cook = http_build_query($_COOKIE, '', '; ');
        $params = ['__token'=>$_SESSION["token"]];
        foreach ($params as $key => &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);
        $parts=parse_url($url);


        $out = "POST ".$uri." HTTP/1.1\r\n";
        $out.= "Host: ".$this->route->hostname."\r\n";
        $out.= "Cookie: {$cook}\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: ".strlen($post_string)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if (isset($post_string)) {
            $out.= $post_string;
        }

        $b64 = base64_encode(json_encode($out));
        exec("cd {$this->route->path_engine} && php shadow.php uri='{$uri}' scheme={$parts['scheme']} host={$this->route->hostname} port={$this->route->port} headers={$b64} &");

    }

    public function router()
    {
        $this->router->init();
        $route = $this->router->getRoute();
        $_ENV["route"] = $route;
        $this->route = wbArrayToObj($route);
        return $this->route;
    }

    public function getField($item, $fld)
    {
        $fields = new Dot();
        $fields->setReference($item);
        return $fields->get($fld);
    }

    public function login($user)
    {
        is_string($user) ? $user = $this->itemRead('users', $user) : null;
        is_object($user) ? null : $user = $this->arrayToObj($user);
        isset($user->avatar) ? null : $user->avatar = [0=>['img'=>"",'alt'=>'User','title'=>'']];
        (array)$user->avatar === $user->avatar ? null : $user->avatar=['img'=>"/uploads/users/{$user->id}/{$user->avatar->img}",'alt'=>'User','title'=>''];
        $user->group = wbArrayToObj(wbItemRead("users", $user->role));
        if (!$user->group or $user->group->active !== 'on' or $user->active !== 'on') {
            return false;
        }
        $user->group->url_logout == "" ? $user->group->url_logout = "/" : null;
        $user->group->url_login == "" ? $user->group->url_login = "/" : null;

        unset($user->password);
        $arr = $this->objToArray($user);
        $this->vars("_sess.user", $arr);
        $this->vars("_env.user", $arr);
        setcookie("user", $user->id, time()+3600);
        $this->user = $user;
        $this->token = $this->getToken();
        $this->vars("_sess.token",$this->token);
        return $user;
    }

    public function initApp()
    {
        $this->router = new wbRouter();
        $this->router->init();
        $this->route = $this->arrayToObj($_ENV['route']);
        $this->InitEnviroment();
        $this->driver();
        $this->InitSettings($this);
        $this->InitFunctions($this);
        $this->controller();
    }

    public function driver()
    {
        $this->settings->_driver = 'json';
        if (is_file($this->route->path_app."/database/_driver.ini")) {
            $drv = file_get_contents($this->route->path_app."/database/_driver.ini");
            $drv = wbSetValuesStr($drv);
            $drv = parse_ini_string($drv, true);
            if (isset($drv["driver"])) {
                $drvlist = $drv["driver"];
                unset($drv["driver"]);
            } else {
                $drvlist = [];
                $drv=[];
            }
            $flag = true;
            foreach ($drv as $driver => $options) {
                if ($flag) {
                    $this->settings->_driver = $driver;
                    $flag = false;
                }
                $this->settings->driver_options[$driver] = $options;
            }
            $this->settings->driver_tables = &$drvlist;
        }
        include_once $this->route->path_engine."/drivers/json/init.php";
        include_once $this->route->path_engine."/drivers/init.php";
    }

    public function controller($controller = null)
    {
        if (is_callable('customRoute')) {
            customRoute($this->route);
        }
        if ($this->route->controller !== 'module' && substr($this->mime($this->route->uri), 0, 6) == 'image/') {
            $this->route->controller = 'thumbnails';
        }

        $controller ? null : $controller = $this->route->controller;
        if ($controller) {
            if (isset($this->route->file) && in_array($this->route->fileinfo->extension, ["php","html"])) {
                return;
            }
            $path = "/controllers/{$controller}.php";
            if (is_file($this->route->path_app . $path)) {
                require_once $this->route->path_app . $path;
            } elseif (is_file($this->route->path_engine.$path)) {
                require_once $this->route->path_engine.$path;
            }
            $class = "ctrl".ucfirst($controller);
            if (!class_exists($class)) {
                echo "Controller not found: {$controller}";
            } else {
                new $class($this);
            }
            return $this;
        }
    }


    public function filterItem($item)
    {
        if ($this->vars("_post._filter")) {
            $filter = $this->vars("_post._filter");
        }
        if (!isset($filter)) {
            return true;
        }

        $vars = new Dot();
        $vars->setReference($item);
        foreach ($filter as $fld => $val) {
            if (is_string($val)) {
                $val = preg_replace('/^\%(.*)\%$/', "", $val);
            }
            if ($val !== "") {
                if (in_array(substr($fld, -5), ["__min","__max"])) {
                    if (substr($fld, -5) == "__min" and $val > $vars->get(substr($fld, 0, -5))) {
                        return false;
                    }
                    if (substr($fld, -5) == "__max" and $val < $vars->get(substr($fld, 0, -5))) {
                        return false;
                    }
                } elseif ((string)$val === $val and $vars->get($fld) !== $val) {
                    return false;
                } elseif ((array)$val === $val and !in_array($vars->get($fld), $val) and $val !== []) {
                    return false;
                }
            }
        }
        return true;
    }


    public function fieldBuild($dict=[], $data=[])
    {
        (array)$dict == $dict ? $dict = wbArrayToObj($dict) : null;

        if ($dict->name == "") {
            return "";
        }
        $this->dict = $dict;
        isset($data["data"]) ? $this->item = $data["data"] : $this->item = [];
        $this->data = $data;
        $this->tpl = $this->getForm('snippets', $dict->type);
        //$this->tpl = $this->fromString('<html>'.$this->tpl->outer().'</html>');
        if (!is_object($this->tpl)) {
            $this->tpl = $this->fromString("<b>Snippet {$dict->type} not found</b>");
        }
        $this->tpl->dict = $this->dict;
        $this->tpl->item = $this->item;

        $this->tpl->setAttributes($dict);
        $this->tpl->find("input:first,textarea:first")->attr("name", $this->dict->name);

        if (isset($this->dict->prop) and $this->dict->prop->style > "") {
            $this->tpl->find("[style]")->attr("style", $this->dict->prop->style);
        } else {
            $this->tpl->find("[style]")->removeAttr("style");
        }
        $func = __FUNCTION__ . "_". $dict->type;
        !method_exists($this, $func) ? $func = __FUNCTION__ . "_". "common" : null;
        return $this->$func();
    }


    public function fieldBuild_multiinput()
    {
        $mult = $this->tpl;
        $mult->item = $this->item;
        $mult->dict = $this->dict;
        if (isset($mult->dict->prop->multiflds)) {
            $lang = $_SESSION['lang'];
            foreach($mult->dict->prop->multiflds as &$fld) {
                if (isset($fld->label->$lang)) {
                    $fld->label = $fld->label->$lang;
                }
            }
        }
        $mult->fetch();
        return $mult;
    }

    public function fieldBuild_treeselect()
    {
        $tag = $this->tpl;
        $tag->find("select")->setAttributes((array)$this->dict);
        $tag->fetch($this->item);
        return $tag;
    }

    public function fieldBuild_image()
    {
        $img = $this->tpl;
        $img->item = $this->item;
        $img->item['_name'] = $this->dict->name;
        $img->item['_form'] = 'treedata';
        $img->item['_item'] = $this->data['id'];
        $img->fetch();
        return $img;
    }

    public function fieldBuild_images()
    {
        $img = $this->tpl;
        $img->item = $this->item;
        $img->item['_name'] = $this->dict->name;
        $img->item['_form'] = 'treedata';
        $img->item['_item'] = $this->data['id'];
        $img->fetch();
        return $img;
    }

    public function fieldBuild_forms()
    {
        $form = $this->tpl;
        $form->item = $this->item;
        $form->dict = $this->dict;
        $form->find("wb-include")->setAttributes($form->dict->prop);
        $form->fetch();
        return $form;
    }

    public function fieldBuild_include()
    {
        $form = $this->tpl;
        $form->item = $this->item;
        $form->dict = $this->dict;
        $form->find("wb-include")->setAttributes($form->dict->prop);
        $form->fetch();
        return $form;
    }

    public function fieldBuild_common()
    {
        $common = &$this->tpl;
        $common->find("[wb]")->setAttributes((array)$this->tpl->dict);
        $common->find("wb-module")->setAttributes((array)$this->tpl->dict);
        $common->fetch();
        if ($this->tpl->dict->type == 'langinp') {
            $common->find('.mod-langinp[placeholder]')->attr('placeholder', $common->dict->label);
        }
        return $common;
    }

    public function fieldBuild_enum()
    {
        $lines=[];
        if (isset($this->dict->prop) && isset($this->dict->prop->enum) && $this->dict->prop->enum > "") {
            $arr=explode(",", $this->dict->prop->enum);
            foreach ($arr as $i => $line) {
                $lines[$line] = ['id' => $line, 'name' => $line];
            }
        }
        $res = $this->tpl->fetch(["enum" => $lines]);
        $val = $this->data['data'][$this->dict->name];

        if ((array)$val === $val) {
            $val = wbJsonEncode($val);
        }
        if (isset($this->data['data'][$this->dict->name]) && $val > '') {
            $res->find('option[value="'.$val.'"]')->attr("selected", true);
        } else {
            $res->find("option[value]:first")->attr("selected", true);
        }
        return $res;
    }


    public function fieldBuild_module()
    {
        $mod = $this->fromString($this->dict->prop->code);
        $mod->find("[name]:first")->attr('name', $this->dict->name);
        return $mod;
    }

    public function addEvent($name, $params=[])
    {
        $evens = json_decode(base64_decode($this->vars("_cookie.events")), true);
        $events[$name] = $params;
        $events = base64_encode(json_encode($events));
        setcookie("events", $events, time()+3600, "/"); // срок действия сутки
    }

    public function addEditor($name, $path, $label = null)
    {
        $this->addTypeModule("editor", $name, $path, $label);
    }

    public function addModule($name, $path, $label = null)
    {
        $this->addTypeModule("module", $name, $path, $label);
    }

    public function addDriver($name, $path, $label = null)
    {
        $this->addTypeModule("driver", $name, $path, $label);
    }

    public function addTypeModule($type, $name, $path, $label = null)
    {
        $types = [
             "module"=>"_env.modules.{$name}"
            ,"editor"=>"_env.editors.{$name}"
            ,"driver"=>"_env.drivers.{$name}"
            ,"uploader"=>"_env.drivers.{$name}"
        ];
        $dir = dirname($path);

        $dir = realpath($dir);

        if (in_array($type, array_keys($types))) {
            if ($label == null) {
                $label = $name;
            }
            if (!$this->vars($types[$type])) {
                $this->vars($types[$type], [
                   "name"=>$name
                   ,"path"=>$path
                   ,"dir"=>$dir
                   ,"label"=>$label
                 ]);
            } elseif ($label !== $name) {
                $this->vars($types[$type].".label", $label);
            }
        } else {
            throw new \Exception('Wrong module type: '.$type.' Use available types: '.implode(", ", array_keys($types)));
        }
    }

    public function module()
    {
        $args = func_get_args();
        if (!isset($args[0])) {
            return null;
        }
        $mod = $args[0];
        unset($args[0]);
        if (!count($args)) {
            $args[]=$this;
        }
        $class = 'mod' . ucfirst($mod);
        /*
        if (is_file($this->vars('_env.path_app')."/modules/{$mod}/{$mod}.php")) {
            require $this->vars('_env.path_app')."/modules/{$mod}/{$mod}.php";
        } else if (is_file($this->vars('_env.path_engine')."/modules/{$mod}/{$mod}.php")) {
            require $this->vars('_env.path_engine')."/modules/{$mod}/{$mod}.php";
        } else {
            return null;
        }
        */
        $rc = new ReflectionClass($class);
        return @$rc->newInstanceArgs($args);
    }

    public function json($data)
    {
        $json = new Jsonq();
        if (is_string($data)) {
            $data=wbItemList($data);
        } elseif (!is_array($data)) {
            $data=(array)$data;
        }
        return $json->collect($data);
    }

    public function dot(&$array=[])
    {
        $array = (array)$array;
        $dot = new Dot();
        $dot->setReference($array);
        return $dot;
    }

    public function cond($condition, $item)
    {
        // пытаемся преобразовать в json строку с одинарными ковычками
        $re = '/\'\{(.*)\'(.*)\:(.*)}\'/mu';
        preg_match($re, $condition, $matches);
        if (isset($matches[0])) {
            $repl = substr($matches[0], 1, -1);
            $json = str_replace("'", '"', $repl);
            $this->isJson($json) ? $condition = str_replace($repl, $json, $condition) : null;
        }
        // ======
        if (in_array(substr(trim($condition), 0, 1), ['"',"'"])) {
            $res = wbEval($condition);
        } else {
            $cond = explode(" ", $condition);
            if (!strpos($cond[0], "(")) {
                $dot = $this->dot($item);
                $cond[0] = eval('return $dot->get("'. $cond[0] .'");');
                (array)$cond[0] === $cond[0] ? $cond[0] = wbJsonEncode($cond[0]) : null;
                $cond[0] = "'".$cond[0]."'";
                $condition = implode(' ', $cond);
            }
            $res = wbEval($condition);
        }
        return $res;
    }

    public function settings()
    {
        $this->settings = &$_ENV["settings"];
        return $this->settings;
    }

    public function vars()
    {
        $count = func_num_args();
        $args = func_get_args();
        if ($count == 0) {
            return;
        }
        if ($count == 1) {
            return $this->vars->get($args[0]);
        }
        if ($count == 2) {
            return $this->vars->set($args[0], $args[1]);
        }
    }


    public function getRoute()
    {
        $this->route = &$_ENV["route"];
        return $this->route;
    }

    public function template($name="default.php")
    {
        $this->template=$this->getTpl($name);
        $this->dom = clone $this->template;
        return $this->dom;
    }

    public function getForm($form = null, $mode = null, $engine = null)
    {
        $_ENV['error'][__FUNCTION__] = '';
        $error = null;
        null == $form ? $form = $this->vars->get("_route.form") : 0;
        null == $mode ? $mode = $this->vars->get("_route.mode") : 0;
        $form == '_settings' ? $formname = substr($form, 1) : $formname = $form;

        $modename = $mode;
        strtolower(substr($modename, -4)) == ".ini" ? $ini = true : $ini = false;

        if (!in_array(strtolower(substr($modename, -4)), [".php",".ini",".htm",".tpl"])) {
            $modename = $modename.".php";
        }

        $aCall = $form.'_'.$mode;
        $eCall = $form.'__'.$mode;

        $loop=false;
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,20);
        foreach ($bt as $func) {
            $aCall==$func["function"] ? $loop=true : null;
            $eCall==$func["function"] ? $loop=true : null;
        }

        if (is_callable($aCall) and $loop == false) {
            $out = $aCall();
        } elseif (is_callable($eCall) and false !== $engine and $loop == false) {
            $out = $eCall();
        }

        if (!isset($out)) {
            $current = '';
            $flag = false;
            $path = ["/forms/{$form}/{$formname}_{$modename}"
                    ,"/forms/{$form}/{$modename}"
                    ,"/forms/common/common_{$modename}"
                ];

            foreach ($path as $form) {
                $current = wbNormalizePath($_ENV['path_app'].$form);
                if (is_file($current)) {
                    break;
                }
                $current = wbNormalizePath($_ENV['path_engine'].$form);
                if (is_file($current)) {
                    break;
                }
                $current = '';
            }

            //unset($form);
            if ('' == $current) {
                strtolower(substr($mode, -4)) == '.php' ? $arg = $modename : $arg = $aCall;
                $out = $error = wbError('func', __FUNCTION__, 1012, [$arg]);
            } else {
                if ($ini) {
                    $out = file_get_contents($current);
                    $out = $this->fromString($out, true);
                } else {
                    $out = $this->fromFile($current);
                }
            }
        }
        if (is_object($out)) {
            $out->path = $current;
        } else {
            $out = $this->fromString('<html>'.$out.'</html>');
        }
        $out->error = $error;
        return $out;
    }

    public function fromString($string)
    {
        $dom = new wbDom($string);
        $dom->app = $this;
        $dom->fetchLang();
        return $dom;
    }

    public function fromFile($file="")
    {
        $res = "";
        $context = null;
        if ($file=="") {
            return null;
        } else {
            //session_write_close(); Нельзя, иначе проблемы с логином
            $url=parse_url($file);
            if (isset($url["scheme"])) {
                $context = stream_context_create(array(
                     'http'=>array(
                             'method'=>"POST",
                             'header'=>	"Accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7\r\n" .
                             "Cache-Control: no-cache\r\n" .
                             'Content-Type:' . " application/x-www-form-urlencoded\r\n" .
                             'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\n" .
                             'Connection: ' . " Close\r\n\r\n",
                             'content' => http_build_query($_POST),
                             'ignore_errors' => true
                     ),
                     "ssl"=>array(
                         "verify_peer"=>false,
                         "verify_peer_name"=>false,
                     )
                 ));
                session_write_close();
                $res=file_get_contents($file, true, $context);

            } else {
                if (!is_file($file)) {
                    $file = str_replace($_ENV["path_app"], "", $file);
                    $file=$_ENV["path_app"].$file;
                    return null;
                } else {
                    $fp = fopen($file, "r");
                    flock($fp, LOCK_SH);
                    $res=file_get_contents($file, false, $context);
                    flock($fp, LOCK_UN);
                    fclose($fp);
                }
            }
            $dom = $this->fromString($res);
            $dom->path = str_replace($_ENV["dir_app"], "", dirname($file, 1));
            return $dom;
        }
    }

    public function getTpl($tpl = null, $path = false)
    {
        $cur = null;
        $out = null;
        if (true == $path) {
            !$cur and is_file($_ENV['path_app']."/{$tpl}") ? $cur = wbNormalizePath($_ENV['path_app']."/{$tpl}") : null;
        } else {
            !$cur and is_file($_ENV['path_tpl']."/{$tpl}") ? $cur = wbNormalizePath($_ENV['path_tpl']."/{$tpl}") : null;
            !$cur and is_file($_ENV['path_engine']."/tpl/{$tpl}") ? $cur = wbNormalizePath($_ENV['path_engine']."/tpl/{$tpl}") : null;
        }
        $cur > "" ? $out = $this->fromFile($cur) : null;
        $_ENV['tpl_realpath'] = dirname($cur);
        $_ENV['tpl_path'] = substr(dirname($cur), strlen($_ENV['path_app']));

        if (!$out) {
            $cur =  $path !== false ? wbNormalizePath($path."/{$tpl}") : wbNormalizePath($_ENV['path_tpl']."/{$tpl}");
            $cur=str_replace($_ENV["path_app"], "", $cur);
            wbError('func', __FUNCTION__, 1011, array($cur));
        } elseif (!$out->is('html')) {
            $out = $out->outer();
            $out = $this->fromString('<html>'.$out.'</html>');
        }
        return $out;
    }

    public function render()
    {
        $render = new wbRender($this);
        return $render->run();
    }
}