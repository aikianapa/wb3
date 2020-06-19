<?php
use Nahid\JsonQ\Jsonq;
use Adbar\Dot;
use DQ\DomQuery;

class wbDom extends DomQuery
{
    public function outer()
    {
        return $this->getouterHtml();
    }

    public function inner($html=null)
    {
        if ($html == null) return $this->getinnerHtml();
            $esc = "wb";
            if ($this->head) $esc = "head";
            $html = "<{$esc}>{$html}</{$esc}>"; // magick
            $this->html($html);         // magick
            $this->children("{$esc}")->unwrap("{$esc}"); // magick
            if ($html > "" and $this->html() == "") $this->text($html);
            $this->find("{$esc}")->unwrap("{$esc}");
        return $this;
    }

    public function tag()
    {
        return $this->tagName;
    }

    public function attributes()
    {
        $attributes = [];
        $attrs = $this->attributes;
        foreach ((object)$attrs as $attr) {
            $attributes[$attr->nodeName] = $attr->nodeValue;
        }
        return $attributes;
    }

    public function rootError()
    {
        if ($this->is(":root")) {
            $code = trim(htmlentities($this->outer()));
            $code = explode("&gt;", $code);
            $code = $code[0]."&gt";
            die("WB tag can't be a :root element!<br>".$code."<br>...");
        }
    }

    public function parents($tag = ":root")
    {
        $res = false;
        if (isset($this->parent)) {
            echo $this->tagName;
            echo " | ";
            echo $this->parent->tagName;
            echo "<br />";
            if ($this->parent->tagName == $tag) {
                return true;
            } else {
                $res = $this->parent->parents($tag);
            }
        }
        return $res;
    }

    public function where($Item=null)
    {
        $res = true;
        if (!$this->params("where")) {
            return $res;
        }
        if ($this->params("where") == "") {
            return $res;
        }
        if ($Item == null) {
            $Item=$this->data;
        }
        $res = wbWhereItem($Item, $this->params->where);
        return $res;
    }

    public function hasRole($role=null)
    {
        if ($role == null && $this->role) {
            return $this->role;
        } elseif ($role !== null and $role == $this->role) {
            return true;
        } else {
            return false;
        }
    }

    public function hasAttr($attr)
    {
        $attrs = [];
        foreach ($this->attributes as $k => $v) {
            $attrs[] = $v->name;
        }
        return in_array($attr, $attrs);
    }

    public function head($head = null)
    {
        if (!isset($this->head)) {
            return false;
        }
        if ($head == null) {
            return $this->head;
        }
        //$this->head->html($head);
    }

    public function fetch($item = null)
    {
        $this->fetchStrict();
        $this->fetchLang();
        if ($this->strict) return;
        if (isset($this->fetched)) return;
        if (!$this->app) $this->app = $_ENV["app"];
        if ($item == null) $item = $this->item;
        if ($this->tagName == "head") $this->head = $this;
        $this->item = $item;
        $this->fetchParams();
        if ($this->is(":root") and ($this->func or $this->funca)) $this->fetchFunc();
        $childrens = $this->children();
        foreach ($childrens as $wb) {
            $wb->copy($this);
            $wb->fetchNode();
        }
        $this->setValues();
        return $this;
    }

    public function fetchNode()
    {
        $this->fetchStrict();
        if ($this->strict) {
            return;
        }
        if (isset($this->fetched)) {
            return;
        }
        $this->fetchParams();
        if ($this->role and ($this->func or $this->funca)) {
            $this->fetchFunc();
        }
        $this->fetch();
        $this->fetched = true;
        return $this;
    }

    public function fetchLang()
    {
        $langs = $this->children("wb-lang");
        if ($langs->length) {
            foreach ($langs as $wb) {
                $wb->copy($this);
                $wb->fetchNode();
            }
        }
    }

    public function fetchFunc()
    {
        if ($this->funca > "") {
            $func = $this->funca;
            new $func($this);
        }
        if ($this->func > "") {
            $func = $this->func;
            new $func($this);
        }
        $this->removeAttr("wb");
        return $this;
    }

    public function fetchStrict()
    {
        if ($this->tagName == "template" or $this->closest("template")->length) {
            $this->strict = true;
        }
        if ($this->tagName == "template") {
            // set locale for template
            $tpl = $this->inner();
            isset($_ENV["locales"][$_SESSION["lang"]]) ? $data = ["_lang" => $_ENV["locales"][$_SESSION["lang"]]] : $data = [];
            $tpl = wbSetValuesStr($tpl, $data);
            $this->inner($tpl);
        }
    }

    public function params($name = null)
    {
        $res = null;
        if ($name == null) {
            if (isset($this->params)) {
                $res = $this->params;
            }
        } else {
            if (isset($this->params->$name)) {
                $res = $this->params->$name;
            }
        }
        return $res;
    }


    public function fetchParams()
    {
        if (isset($this->params)) {
            return $this;
        }
        $this->setAttributes();
        $this->role = false;
        $this->func = false;
        $this->funca = false;
        $this->atrs = (object)[];
        $params = [];
        if (substr($this->tagName, 0, 3) == "wb-") {
            $this->role = substr($this->tagName, 3);
        }
        $attrs = $this->attributes();
        if (count($attrs)) {
            foreach ($attrs as $atname => $atval) {
                if ($atname == "wb" or substr($atname, 0, 3) == "wb-") {
                    $name = $atname;
                    if (!isset($params)) {
                        $params = [];
                    }
                    if ($name !== "wb") {
                        $name = substr($atname, 3);
                    }
                    if (in_array($name, ["if","where"])) {
                        $prms = [$name => $atval];
                        $this->atrs->$name = $atval;
                    } else {
                        $prms = wbAttrToValue($atval);
                        if ($name !== "wb") {
                            $prms = [$name => $prms];
                            $this->atrs->$name = $atval;
                            $this->removeAttr($atname);
                        }
                        if (is_string($prms)) {
                            $prms = ["wb"=>$prms];
                        }
                    }
                    $params = array_merge($params, $prms);
                }
            }
            $this->params = (object)$params;
        }
        $this->fetchAllows();
        if ($this->role) {
            $func="tag".ucfirst($this->role);
            $file = $this->app->vars("_env.path_engine")."/tags/{$this->role}/{$this->role}.php";
            if (is_file($file)) {
                require_once $file;
                if (class_exists($func)) {
                    $this->func = $func;
                }
            } else {
                unset($this->role);
            }
        }
        foreach ($this->atrs as $attr => $value) {
            $func="attr".ucfirst($attr);
            if (!class_exists($func)) {
                $file = $this->app->vars("_env.path_engine")."/attrs/{$name}/{$name}.php";
                if (is_file($file)) {
                    require_once $file;
                }
            }
            if (class_exists($func)) {
                $this->funca = $func;
                if (!$this->role) {
                    $this->role = "attr";
                }
            }
        }
    }

    public function fetchAllows()
    {
        if ($this->params('allow') > "") {
            $allow = wbArrayAttr($this->params->allow);
            if ($allow && !in_array($this->app->vars("_sess.user.role"), $allow)) {
                $this->params->allow = false;
                $this->remove();
            } else {
                $this->params->allow = true;
            }
        }
        if ($this->params('disallow') > "") {
            $disallow = wbArrayAttr($this->params->disallow);
            if ($disallow && !in_array($this->app->vars("_sess.user.role"), $disallow)) {
                $this->params->allow = true;
            } else {
                $this->params->allow = false;
                $this->remove();
            }
        }
        if ($this->params('disabled') > "") {
            $disabled = wbArrayAttr($this->params->disabled);
            if ($disabled && in_array($this->app->vars("_sess.user.role"), $disabled)) {
                $this->attr("disabled", true);
            }
        }
        if ($this->params('enabled') > "") {
            $enabled = wbArrayAttr($this->params->enabled);
            if ($enabled && !in_array($this->app->vars("_sess.user.role"), $enabled)) {
                $this->attr("disabled", true);
            }
        }
    }



    public function addTpl($real = true)
    {
        if (!$this->params("tpl")) {
            return;
        }
        $this->params->route = $this->app->vars("_route");
        $params = json_encode($this->params);
        $tplId = "tp_".md5($params);
        if ($real) {
            $tpl = $this->outerHtml();
            $this->after("
                  <template id='{$tplId}' data-params='{$params}'>
                      $tpl
                  </template>");
            $this->attr("data-wb-tpl", $tplId);
        }
        return $tplId;
    }

    public function setAttributes($Item=null)
    {
        if (!$this->attributes) {
            return $this;
        }
        if ($Item == null) {
            $Item = $this->item;
        }
        if (is_object($Item)) {
            $Item=wbObjToArray($Item);
        }
        foreach ($this->attributes as $at) {
            $atname = $at->name;
            $atval = $at->value;
            if (strpos($atname, "}}")) {
                unset($this->attributes[$atname]);
                $atname = wbSetValuesStr($atname, $Item);
            }
            $atval = str_replace("%7B%7B", "{{", $atval);
            $atval = str_replace("%7D%7D", "}}", $atval);
            if (strpos($atval, "}}")) {
                $atval = wbSetValuesStr($atval, $Item);
            }
            $this->attr($atname, $atval);
        }
        return $this;
    }

    public function copy($parent)
    {
        isset($parent->locale) ? $this->locale = $parent->locale : $this->locale = [];
        if (isset($parent->head)) {
            $this->head = $parent->head;
        } else {
            $this->head = false;
        }
        if (isset($parent->strict)) {
            $this->strict = $parent->strict;
        } else {
            $this->strict = false;
        }

        $this->app = $parent->app;
        $this->item = $parent->item;
        $this->parent = $parent;
        $this->item["_var"] = &$_ENV["variables"];
    }

    public function setValues()
    {
        if ($this->strict) return;
        if (!isset($this->item)) $this->item = [];
        $fields = new Dot();
        $fields->setReference($this->item);
        $inputs = $this->find("[name]");
        foreach ($inputs as $inp) {
            if (in_array($inp->tagName, ["input","textarea","select"]) && !$inp->hasAttr("done") && !$inp->closest("template")->length) {
                $name = $inp->attr("name");
                $value = $fields->get($name);
                if ((array)$value === $value) $value = wb_json_encode($value);
                if ($inp->tag() == "textarea") {
                    $inp->text($value);
                } elseif ($inp->tag() == "select") {
                    if ((array)$value === $value) {
                        foreach ($value as $val) {
                            $inp->find("[value='{$val}']")->attr("selected", true);
                        }
                    } else {
                        $inp->find("[value='{$value}']")->attr("selected", true);
                    }
                } elseif ($inp->tag() == "input") {
                    $inp->attr("value", $value);
                    if ($inp->attr("type") == "checkbox" and $value == "on") $inp->attr("checked", true);
                }
                $inp->attr("done", "");
            }
        }
        foreach ($this->find("template") as $t) {
            $t->inner(str_replace("}}", "_}_}_", $t->inner()));
        }
        if (strpos($this, "}}")) {
            $render = new wbRender($this);
            $html = $render->exec();
            $this->inner($html);
        }
        foreach ($this->find("template") as $t) {
            $t->inner(str_replace("_}_}_", "}}", $t->inner()));
        }
        return $this;
    }
}

class wbApp
{
    public $settings;
    public $route;
    public $item;
    public $out;
    public $template;
    public $router;
    public $render;

    public function __construct($settings=[])
    {
        $this->settings = new stdClass();

        foreach ($settings as $key => $val) {
            $this->settings->$key = $val;
        }

        if (!isset($this->settings->driver)) {
            $this->settings->driver = null;
        }

        $this->router = new wbRouter();
        $this->vars = new Dot();
        $vars = [
          "_env"  => &$_ENV,
          "_get"  => &$_GET,
          "_srv"  => &$_SERVER,
          "_post" => &$_POST,
          "_req"  => &$_REQUEST,
          "_route"=> &$_ENV["route"],
          "_sett" => &$_ENV["settings"],
          "_var"  => &$_ENV["variables"],
          "_sess" => &$_SESSION,
          "_cookie"=>&$_COOKIE,
          "_cook"  =>&$_COOKIE,
          "_mode" => &$_ENV["route"]["mode"],
          "_form" => &$_ENV["route"]["form"],
          "_item" => &$_ENV["route"]["item"],
          "_param"=> &$_ENV["route"]["param"],
          "_locale"=> &$_ENV["locale"]
      ];
        $this->vars->setReference($vars);
        $this->initApp();
    }

    public function __call($func, $params)
    {
        $wbfunc="wb".$func;
        $_ENV["app"] = &$this;
        if (is_callable($wbfunc)) {
            $prms = [];
            foreach ($params as $k => $i) {
                $prms[] = '$params['.$k.']';
            }
            eval('$res = $wbfunc('.implode(",", $prms).');');
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

    public function route()
    {
        $route = $this->router->getRoute();
        $_ENV["route"] = $route;
        $this->route = wbArrayToObj($route);
        return $this->route;
    }

    public function initApp()
    {
        $this->InitEnviroment();
        $this->ErrorList();
        $this->RouterAdd();
        $this->route();
        $this->driver();
        $this->InitSettings($this);
        $this->controller();
    }

    public function driver()
    {
        if ($this->settings->driver === null) {
            $this->settings->driver = 'json';
            if (is_file($this->route->path_app."/driver.ini")) {
                $drv = parse_ini_file($this->route->path_app."/driver.ini", true);
                foreach ($drv as $driver => $options) {
                    $this->settings->driver = $driver;
                    $this->settings->driver_options = $options;
                    break;
                }
            }
        }

        include_once $this->route->path_engine . "/drivers/json/init.php";
        $path = "/drivers/{$this->settings->driver}/init.php";
        if (is_file($this->route->path_app . $path)) {
            include_once $this->route->path_app . $path;
        } elseif (is_file($this->route->path_engine.$path)) {
            include_once $this->route->path_engine.$path;
        }
        include_once $this->route->path_engine."/drivers/init.php";
    }

    public function controller()
    {
        if ($this->route->controller) {
            $path = "/controllers/{$this->route->controller}.php";
            if (is_file($this->route->path_app . $path)) {
                require_once $this->route->path_app . $path;
            } elseif (is_file($this->route->path_engine.$path)) {
                require_once $this->route->path_engine.$path;
            }
            $class = "ctrl".ucfirst($this->route->controller);
            if (!class_exists($class)) {
                echo "Controller not found: {$this->route->controller}";
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
        if ((array)$dict == $dict) $dict = wbArrayToObj($dict);
        $this->dict = $dict;
        $this->item = $data["data"];
        $this->tpl = $this->getForm('snippets', $dict->type);
        if (!is_object($this->tpl)) {
            $this->tpl = $this->fromString("<b>Snippet {$dict->type} not found</b>");
        }
        $this->tpl->dict = $this->dict;
        $this->tpl->item = $this->item;
        $this->tpl->setAttributes($dict);
        $this->tpl->find("input")->attr("name", $this->dict->name);
        if (isset($this->dict->prop) and $this->dict->prop->style > "") {
            $this->tpl->find("[style]")->attr("style", $this->dict->prop->style);
        } else {
            $this->tpl->find("[style]")->removeAttr("style");
        }
        $func = __FUNCTION__ . "_". $dict->type;
        if (!method_exists($this, $func)) $func = __FUNCTION__ . "_". "common";
        return $this->$func();
    }

    public function fieldBuild_multiinput()
    {
        $mult = $this->tpl;
        $mult->item = $this->item;
        $mult->dict = $this->dict;
        $mult->fetch();
        return $mult;
    }

    public function fieldBuild_forms()
    {
        $form = $this->tpl;
        $form->item = $this->item;
        $form->dict = $this->dict;
        $form->find("meta")->setAttributes($form->dict->prop);
        $form->fetch();
        return $form;
    }

    public function fieldBuild_common()
    {
        //$this->tpl->setValues();
        return $this->tpl->fetch();
    }

    public function addEvent($name, $params=[])
    {
        $evens = json_decode(base64_decode($this->vars("_cookie.events")), true);
        $events[$name] = $params;
        $events = base64_encode(json_encode($events));
        setcookie("events", $events, time()+3600, "/"); // срок действия сутки
    }


    public function fieldBuild_enum()
    {
        $lines=[];
        if ($this->dict->prop->enum > "") {
            $arr=explode(",", $this->dict->prop->enum);
            foreach ($arr as $i => $line) {
                $lines[$line] = ['id' => $line, 'name' => $line];
            }
        }
        $res = $this->tpl->fetch(["enum" => $lines]);
        $value = $this->data[$this->dict->name];
        $res->find("option[value='{$value}']")->attr("selected", true);
        return $res;
    }


    public function fieldBuild_module()
    {
        $this->tpl->setAttributes($this->dict);
        $this->tpl->fetch();
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
        $dir = substr($dir, strlen($_SERVER["DOCUMENT_ROOT"]));

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
        $dot = new Dot();
        $dot->setReference($array);
        return $dot;
    }

    public function settings()
    {
        $this->settings=$_ENV["settings"];
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
        $this->route = $_ENV["route"];
        return $this->route;
    }

    public function variable($name, $value="__wbVarNotAssigned__")
    {
        if ($value=="__wbVarNotAssigned__") {
            return $this->data[$name];
        }
        $this->data[$name]=$value;
        return $this->data;
    }

    public function ___data($data="__wbVarNotAssigned__")
    {
        if ($data=="__wbVarNotAssigned__") {
            return $this->data;
        }
        $this->data=$data;
        return $this->data;
    }

    public function template($name="default.php")
    {
        $this->template=wbGetTpl($name);
        $this->dom = clone $this->template;
        return $this->dom;
    }

    public function getForm($form = null, $mode = null, $engine = null)
    {
        $_ENV['error'][__FUNCTION__] = '';
        if (null == $form) {
            $form = $this->vars->get("_route.form");
        }
        if (null == $mode) {
            $mode = $this->vars->get("_route.mode");
        }
        $modename = $mode;
        if (strtolower(substr($modename, -4)) == ".ini") {
            $ini = true;
        } else {
            $ini = false;
        }
        if (!in_array(strtolower(substr($modename, -4)), [".php",".ini",".htm",".tpl"])) {
            $modename = $modename.".php";
        }

        $aCall = $form.'_'.$mode;
        $eCall = $form.'__'.$mode;

        $loop=false;
        foreach (debug_backtrace() as $func) {
            if ($aCall==$func["function"]) {
                $loop=true;
            }
            if ($eCall==$func["function"]) {
                $loop=true;
            }
        }

        if (is_callable($aCall) and $loop == false) {
            $out = $aCall();
        } elseif (is_callable($eCall) and false !== $engine and $loop == false) {
            $out = $eCall();
        }

        if (!isset($out)) {
            $current = '';
            $flag = false;
            $path = array("/forms/{$form}_{$modename}", "/forms/{$form}/{$form}_{$modename}", "/forms/{$form}/{$modename}");
            foreach ($path as $form) {
                if (false == $flag) {
                    if (is_file($_ENV['path_engine'].$form)) {
                        $current = $_ENV['path_engine'].$form;
                        $flag = $engine;
                    }
                    if (is_file($_ENV['path_app'].$form) && false == $flag) {
                        $current = $_ENV['path_app'].$form;
                        $flag = true;
                    }
                }
            }
            //unset($form);
            if ('' == $current) {
                $out=null;
                $current = "{$_ENV['path_engine']}/forms/common/common_{$modename}";
                if (is_file($current)) {
                    $out = $this->fromFile($current, true);
                }
                $current = "{$_ENV['path_app']}/forms/common/common_{$modename}";
                if (is_file($current)) {
                    $out = $this->fromFile($current, true);
                }
                if ($out == null) {
                    $current = wbNormalizePath("/forms/{$form}_{$modename}");
                    $out = wbErrorOut(wbError('func', __FUNCTION__, 1012, array($current)), true);
                }
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
        }
        return $out;
    }

    public function fromString($string)
    {
        $dom = new wbDom($string);
        $dom->app = $this;
        return $dom;
    }

    public function fromFile($file="")
    {
        $res = "";
        $context = null;
        if ($file=="") {
            return $this->fromString("", $isDocument);
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
                             'content' => http_build_query($_POST)
                     ),
                     "ssl"=>array(
                         "verify_peer"=>false,
                         "verify_peer_name"=>false,
                     )
                 ));
                session_write_close();
                $res=@file_get_contents($file, true, $context);
                session_start();
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
            if (!$cur and is_file($_ENV['path_app']."/{$tpl}")) {
                $cur = wbNormalizePath($_ENV['path_app']."/{$tpl}");
            }
        } else {
            if (!$cur and is_file($_ENV['path_tpl']."/{$tpl}")) {
                $cur = wbNormalizePath($_ENV['path_tpl']."/{$tpl}");
            }
            if (!$cur and is_file($_ENV['path_engine']."/tpl/{$tpl}")) {
                $cur = wbNormalizePath($_ENV['path_engine']."/tpl/{$tpl}");
            }
        }
        if ($cur > "") {
            $out = $this->fromFile($cur);
        }

        if (!$out) {
            if ($path !== false) {
                $cur = wbNormalizePath($path."/{$tpl}");
            } else {
                $cur = wbNormalizePath($_ENV['path_tpl']."/{$tpl}");
            }
            $cur=str_replace($_ENV["path_app"], "", $cur);
            wbError('func', __FUNCTION__, 1011, array($cur));
        }

        return $out;
    }

    public function render()
    {
        $render = new wbRender($this);
        return $render->run();
    }
}

class wbRender extends WEProcessor
{
    public function __construct($dom)
    {
        $this->vars = new Dot();
        $this->inner = $dom->inner();
        $this->parser = new parse_engine(new weparser($this));
        $this->context = $dom->item;
        $vars = $dom->app->vars;
        $vars = (array)$vars;
        $this->vars->setReference($vars);
    }

    public function exec($item = null)
    {
        if (!isset($this->item)) {
            $this->item = [];
        }
        if ($item == null) {
            $item = $this->item;
        }
        return $this->substitute($this->inner);
    }
}
