<?php

require_once __DIR__."/lib/weprocessor/weprocessor.php";
require_once __DIR__."/lib/weprocessor/weparser.class";

use DQ\DomQuery;
use Adbar\Dot;

class wbDom extends DomQuery
{
    public $app;
    public $root;
    public $item;
    public $head;
    public $fetched;
    public $strict;
    public $role;
    public $func;
    public $funca;
    public $atrs;
    public $params;
    public $disallow;
    public $locale;
    public $path;
    public $parent;

    public function __call($name, $arguments)
    {
        if (method_exists($this->getFirstElmNode(), $name)) {
            return \call_user_func_array(array($this->getFirstElmNode(), $name), $arguments);
        } elseif (substr($name, 0, 3) == 'tag') {
            $class = $name;
            $fname = strtolower(substr($name, 3));
            $file_e = $this->app->vars("_env.path_engine")."/tags/{$fname}/{$fname}.php";
            $file_a = $this->app->vars("_env.path_app")."/tags/{$fname}/{$fname}.php";
            if (is_file($file_a)) {
                require_once $file_a;
                if (class_exists($class)) {
                    return new $class($arguments[0]);
                }
            } elseif (is_file($file_e)) {
                require_once $file_e;
                if (class_exists($class)) {
                    return new $class($arguments[0]);
                }
            } else {
                unset($this->role);
            }
        }
        throw new \Exception('Unknown call '.$name);
    }

    public function outer()
    {
        return $this->getouterHtml();
    }

    public function inner($html=null)
    {
        if ($html == null) {
            return $this->getinnerHtml();
        }

        if (!is_string($html) && !is_object($html)) {
            $html='';
        }

        $is_tag = preg_match("/<[^<]+>/", $html, $res);

        $this->head ? $esc = "head" : $esc = "wb";

        $html = "<{$esc}>{$html}</{$esc}>"; // magick
        $this->html($html);         // magick
        $this->find("{$esc}")->unwrap("{$esc}"); // magick
        if (!$is_tag) {
            $this->text($html);
        }

        //$this->find("{$esc}")->unwrap("{$esc}");
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

    public function attrsCopy(&$dom)
    {
        $attrs = $this->attributes();

        foreach ($attrs as $attr => $value) {
            if (substr($attr, 0, 3) !== 'wb-' && $attr !== 'wb') {
                if ($attr !== 'class' && $attr !== 'name') {
                    $dom->attr($attr, $value);
                } elseif ($attr == 'class') {
                    $dom->addClass($value);
                } elseif ($attr == 'name') {
                    $dom->attr('name', $value);
                }
            }
        }

        return $this;
    }

    public function rootError()
    {
        if ($this->is(':root')) {
            $code = trim(htmlentities($this->outer()));
            $code = explode('&gt;', $code);
            $code = $code[0]."&gt";
            die("WB tag can't be a :root element!<br>".$code."<br>...");
        }
    }

    public function parents($tag = ":root")
    {
        $res = false;
        if (isset($this->parent)) {
            if ($this->parent->is($tag)) {
                $res = $this->parent;
            } else {
                $res = $this->parent->parents($tag);
            }
        } else {
            $res = $this;
        }
        return $res;
    }

    public function getField($fld)
    {
        $fields = $this->app->dot($this->item);
        return $fields->get($fld);
    }

    public function setField($fld, $data = [])
    {
        $fields = $this->app->dot($this->item);
        return $fields->set($fld, $data);
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
        if (!$this->attributes) return false;
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
        if (isset($item) && $item == null) {
            $item = $this->item;
        }
    }

    public function fetch($item = null)
    {
        $this->app ? null : $this->app = &$_ENV["app"];
        $tmp = $this->app->vars('_env.locale');
        isset($this->root) ? null : $this->root = $this->parents(':root')[0];
        if ($this->is('wb-off')) {
            return;
        }
        $this->fetchStrict();
        $this->fetchLang();
        if ($this->strict or isset($this->fetched)) {
            return;
        }
        if (!isset($_ENV['wb_steps'])) {
            $_ENV['wb_steps'] = 1;
        } else {
            $_ENV['wb_steps']++;
        }
        if ($item == null) {
            $item = $this->item;
        }
        if ($this->tagName == "head") {
            $this->head = $this;
        }
        $this->item = $item;
        $this->fetchParams();
        if ($this->is(":root")) {
            if ($this->func or $this->funca) {
                $this->fetchFunc();
            } // так нужно для рутовых тэгов
        }
        $childrens = $this->children();
        foreach ($childrens as $wb) {
            $wb->copy($this);
            $wb->app = &$this->app;
            $wb->root = $this->root;
            $wb->fetchNode();
        }

        $this->setValues();
        $this->app->vars("_env.locale", $tmp);
        if ($this->app->vars('_sett.devmode') == 'on' && $this->is('[rel=preload]')) {
            $href = $this->attr('href');
            if (!strpos('?', $href) && isset($_COOKIE['devmode'])) {
                $this->attr('href', $href.'?'.$_COOKIE['devmode']);
            }
        }
        if ($this->find('.nav-pagination')->length) {
            $this->fixPagination();
        }
        return $this;
    }
    public function fetchNode()
    {
        $outer = ' '.$this->outer();
        if (!strpos($outer, 'wb') && !strpos($outer, '}}') && !strpos($outer, '%7D%7D')) {
            return;
        }
        $this->fetchStrict();
        if ($this->strict or isset($this->fetched)) {
            return;
        }

        $this->fetchLang();
        $this->fetchParams();

        if ($this->role and ($this->func or $this->funca)) {
            $params = (array)$this->params;
            $this->app->vars('_env.tagparams', $params);
            $this->fetchFunc();
            $this->app->vars('_env.tagparams', $params);
        }
        if (!isset($_ENV['wb_steps'])) {
            $_ENV['wb_steps'] = 1;
        } else {
            $_ENV['wb_steps']++;
        }

        $outer = ' '.$this->outer();
        if (!strpos($outer, 'wb') && !strpos($outer, '}}') && !strpos($outer, '%7D%7D')) {
            return;
        }

        $childrens = $this->children();
        foreach ($childrens as $wb) {
            $wb->copy($this);
            $wb->root = $this->root;
            $wb->fetchNode();
        }
        $this->setValues();
        if ($this->find('.nav-pagination')->length) {
            $this->fixPagination();
        }
        $this->fetched = true;
    }

    public function fixPagination()
    {
        if ($this->find('.nav-pagination[data-tpl]:not(.fixed)')->length) {
            $pags = $this->find('.nav-pagination[data-tpl]');
            foreach ($pags as $pag) {
                $pid = $pag->attr('data-tpl');
                if ($this->find($pid.':not(template)')->length && $pag->parent($pid)->length) {
                    $pag->removeClass('nav-pagination');
                    if ($pag->hasClass('pos-top')) {
                        $this->find($pid.':not(template)')->parent()->prepend($pag->outer());
                    } else {
                        $this->find($pid.':not(template)')->parent()->append($pag->outer());
                    }
                    $pag->remove();
                }
            }
        }
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
        foreach ($this->funca as $func) {
            new $func($this);
        }
        if ($this->func > "") {
            $func = $this->func;
            new $func($this);
        }
        $this->removeAttr("wb");
        return $this;
    }

    public function filterStrict()
    {
        if ($this->params('filter') > '' and $this->params('strict') == 'false') {
            $tmpfl = (array)$this->params('filter');
            foreach ($tmpfl as $key => $val) {
                $val = preg_replace('/^\%(.*)\%$/', "", $val);
                if ($val == '' or $val == null) {
                    unset($tmpfl[$key]);
                }
            }

            $this->params->filter = $tmpfl;
        }
    }

    public function fetchStrict()
    {
        if ($this->hasAttr('wb-off')) {

            $this->strict = true;
            $wbon = $this->find('[wb-on]');
            foreach ($wbon as $wb) {
                $wb->copy($this);
                $wb->strict = false;
                $wb->root = $this->root;
                $wb->fetchNode();
            }
            return;
        }
        if ($this->hasAttr('wb-off') OR in_array($this->tagName, ['template', 'code','textarea','pre'])) {
            if ($this->tagName == 'textarea' && $this->is('[wb-module]')) {
                return;
            }
            $this->strict = true;
            // set locale for template
            if (strpos($this->outer(), '_lang.') !== 0) {
                $locale = $this->app->vars('_env.locale');
                if (isset($locale[$_SESSION["lang"]])) {
                    $locale = $locale[$_SESSION["lang"]];
                }
                $this->addParams(['locale'=>$locale]);
            }
            //isset($_ENV["locales"][$_SESSION["lang"]]) ? $data = ["_lang" => $_ENV["locales"][$_SESSION["lang"]]] : $data = [];
        }
    }

    public function addParams($data)
    {
        $add = $data;
        if (!is_array($data)) {
            $add = json_decode($data, true);
            if (!$add) {
                parse_str($data, $add);
            }
        }
        $params = json_decode($this->attr('data-params'), true);
        if (!$params) {
            parse_str($this->attr('data-params'), $params);
        }
        !$params ? $params = [] : null;
        $params = array_merge($params, $add);
        $this->attr('data-params', json_encode($params));
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
        $this->funca = [];
        $this->atrs = (object)[];
        $this->params = (object)[];

        $params = [];
        if (substr($this->tagName, 0, 3) == "wb-") {
            $this->role = substr($this->tagName, 3);
        }
        $attrs = $this->attributes();

        if (count($attrs)) {
            $prms = [];
            foreach ($attrs as $atname => $atval) {
                if ($atname == "wb" or substr($atname, 0, 3) == "wb-") {
                    $name = $atname;
                    $name !== "wb" ? $name = substr($atname, 3) : null;
                    if (in_array($name, ['if','where','change','ajax','save','off','api'])) {
                        $prms = [$name => $atval];
                        $this->atrs->$name = $atval;
                    } else {
                        $prms = wbAttrToValue($atval);
                        if ($name !== "wb") {
                            $prms = [$name => $prms];
                            $this->atrs->$name = $atval;
                            // если видим wb-name, но этот тэг внутри другого именованного тэга, то wb-name не удаляем
                            $name == 'name' && $this->parents('[name]')->length ? null : $this->removeAttr($atname);
                        }
                        is_string($prms) ? $prms = ["wb"=>$prms] : null;
                    }
                }
                $params = array_merge($params, $prms);
            }
            $this->params = (object)$params;
        }


        if (isset($this->params->module)) {
            $this->role = "module";
        }
        $this->fetchAllows();
        if ($this->role) {
            $func="tag".ucfirst($this->role);
            $file = $this->app->vars("_env.path_app") . "/tags/{$this->role}/{$this->role}.php";
            is_file($file) ? null : $file = $this->app->vars("_env.path_engine")."/tags/{$this->role}/{$this->role}.php";
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
                $file = $this->app->vars("_env.path_app") . "/attrs/{$attr}/{$attr}.php";
                is_file($file) ? null : $file = $this->app->vars("_env.path_engine")."/attrs/{$attr}/{$attr}.php";
                if (is_file($file)) {
                    require_once $file;
                }
            }
            if (class_exists($func)) {
                $this->funca[] = $func;
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
            if (trim($this->params('allow')) == "*") {
                $this->params->allow = true;
            } elseif ($allow && !in_array($this->app->vars("_sess.user.role"), $allow)) {
                $this->params->allow = false;
                $this->remove();
            } else {
                $this->params->allow = true;
            }
        }
        if ($this->params('disallow') > "") {
            $disallow = wbArrayAttr($this->params->disallow);
            if (trim($this->params('disallow')) == "*") {
                $this->params->allow = false;
                $this->remove();
            } elseif ($disallow && !in_array($this->app->vars("_sess.user.role"), $disallow)) {
                $this->params->allow = true;
            } else {
                $this->params->allow = false;
                $this->remove();
            }
            $this->disallow = $disallow;
        }
        if ($this->params('disabled') > "") {
            $disabled = wbArrayAttr($this->params->disabled);
            if ($disabled && in_array($this->app->vars("_sess.user.role"), $disabled) or trim($this->params('disabled')) == '*') {
                $this->attr("disabled", true);
            }
        }
        if ($this->params('enabled') > "") {
            $enabled = wbArrayAttr($this->params->enabled);
            if ($enabled && !in_array($this->app->vars("_sess.user.role"), $enabled) or trim($this->params('enabled')) == '*') {
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
        isset($this->locale[$this->app->lang]) ? $this->params->locale = $this->locale[$this->app->lang] : $this->params->locale = [];
        $params = json_encode($this->params);
        $this->attr('data-params', $params);
        if ($this->attr("id") > '') {
            $tplId = $this->attr("id");
        } elseif (substr($this->tagName, 0, 3) == 'wb-' and $this->parent()->attr("id") > '') {
            $tplId = $this->parent()->attr("id");
        } else {
            $tplId = "tp_".md5($params);
        }
        $this->params->tpl = $tplId;
        if ($real) {
            $tpl = $this->outer();
            $this->after("\n
                  <template id='{$tplId}' data-params='{$params}'>
                      $tpl
                  </template>\n");
            $this->attr("data-wb-tpl", $tplId);
        }
        return $tplId;
    }

    public function setAttributes($Item=null)
    {
        if (!$this->attributes) {
            return $this;
        }
        $Item == null ? $Item = $this->item : null;
        is_object($Item) ? $Item=wbObjToArray($Item) : null;

        foreach ($this->attributes as $at) {
            $atname = $at->name;
            $atval = $at->value;
            if (substr($atname, 0, 1) == "_" && strpos($atname, ".")) {
                $this->removeAttr($atname);
                $atname = $this->app->vars($atname);
                if ($atname == '') {
                    break;
                }
            }
            $atval = str_replace(['%7B%7B', '%7D%7D'], ['{{', '}}'], $atval);
            if (strpos($atname, "}}")) {
                unset($this->attributes[$atname]);
                $atname = wbSetValuesStr($atname, $Item);
            }
            $atval = strpos($atval, "}}") ? $atval = wbSetValuesStr($atval, $Item) : null;
            $this->attr($atname, $atval);
        }
        return $this;
    }

    public function copy(&$parent)
    {
        isset($parent->locale) ? $this->locale = $parent->locale : $this->locale = [];
        isset($parent->head) ? $this->head = $parent->head : $this->head = false;
        isset($parent->strict) ? $this->strict = $parent->strict : $this->strict = false;
        isset($parent->path) && !isset($this->path) ? $this->path = $parent->path : null;

        $this->app = $parent->app;
        $this->item = $parent->item;
        $this->parent = $parent;
        $this->item["_var"] = &$_ENV["variables"];
    }

    public function setSeo()
    {
        $descr = $this->find("meta[name=description]")->attr('content');
        $keywords = $this->find("meta[name=keywords]")->attr('content');
        $header = $this->find("title")->text();
        $this->find("title")->remove();
        $this->find("meta[name=description]")->remove();
        $this->find("meta[name=keywords]")->remove();
        $seo = $this->app->ItemRead('_settings', 'seo');
        $data = $this->app->dot($this->item);
        isset($this->item['header']) ? $header = $this->item['header'] : $header = $this->app->vars('_sett.header');
        // для блока Yonger - seo
        foreach ((array)$data->get('blocks') as $block) {
            if ($block === (array)$block && $block['name'] == 'seo' && $block['active'] == 'on') {
                $lang = $block['lang'][$_SESSION['lang']];
                $header = $lang['title'] > '' ? $lang['title'] : $header;
                $keywords = $lang['keywords'] > '' ? $lang['keywords'] : $keywords;
                $descr = $lang['descr'] > '' ? $lang['descr'] : $descr;
            }
        }
        if ($data->get('seo') == 'on') {
            $data->get('meta_title') ? $header = $data->get('meta_title') : null;
            $data->get('meta_keywords') ? $keywords = $data->get('meta_keywords') : null;
            $data->get('meta_description') ? $descr = $data->get('meta_description') : null;
        } elseif (isset($seo['seo']) and $seo['seo'] == 'on') {
            $seo['title'] > '' ? $header = $seo['title'] : null;
            $seo['meta_keywords'] > '' ? $keywords = $seo['meta_keywords'] : null;
            $seo['meta_description'] > '' ? $descr = $seo['meta_description'] : null;
        } elseif ($this->app->vars('_var.title_prepend')) {
            $header = trim($this->app->vars('_var.title_prepend') . ' ' . $header);
        }
        if (is_array($header)) $header = $header[$this->app->lang];
        $this->find('head')
            ->prepend("<meta name='description' content='{$descr}' />")
            ->prepend("<meta name='keywords' content='{$keywords}' />")
            ->prepend("<title>{$header}</title>");
    }

    public function setValues()
    {
        if ($this->strict) {
            return;
        }

        isset($this->item) ? null : $this->item = [];
        $fields = $this->app->dot($this->item);
        $inputs = $this->find("[name]:not([done])");

        foreach ($inputs as $inp) {
            if (!$inp->closest("template")->length) {
                $inp->copy($this);
                $inp->fetchParams();
                $name = $inp->attr("name");
                $value = $fields->get($name);
                ((array)$value === $value and $inp->tagName !== "select") ? $value = wb_json_encode($value) : null;
                if ((string)$value === $value &&  $value > '') {
                    $value = str_replace('&amp;quot;', '"', $value);
                } // борьба с ковычками в атрибутах тэгов
                if (in_array($inp->tagName, ["input","textarea","select"])) {
                    if ($inp->tagName == "textarea") {
                        if ($inp->params('oconv') > '') {
                            $oconv = $inp->params('oconv');
                            $inp->inner(@$oconv($value));
                        } elseif ($inp->attr('type') == 'json') {
                            $inp->inner($value);
                        } else {
                            if ($inp->attr('value') > '') {
                                $value = wbSetValuesStr($inp->attr('value'), $this->item);
                            }
                            $inp->inner(htmlentities($value));
                        }
                        $inp->params('oconv') > '' ? $inp->attr('data-oconv', $inp->params('oconv')) : null;
                        $inp->params('iconv') > '' ? $inp->attr('data-iconv', $inp->params('iconv')) : null;
                        $inp->attr("done", "");
                    } elseif ($inp->tagName == "select") {
                        if ((array)$value === $value) {
                            foreach ($value as $val) {
                                $tmp = $inp->find('[value]');
                                foreach ($tmp as $v) {
                                    if ($v->attr('value') == $val) {
                                        $v->attr('selected', true);
                                    }
                                }
                                $val > "" ? $inp->find("[value='{$val}']")->attr("selected", true) : null;
                            }
                        } elseif ($value > "") {
                            $tmp = $inp->find('[value]');
                            foreach ($tmp as $v) {
                                if ($v->attr('value') == $value) {
                                    $v->attr('selected', true);
                                }
                            }
                        }
                        $inp->attr("done", "");
                    } elseif ($inp->tagName == "input") {
                        if ($inp->attr("type") == "radio") {
                            $inp->attr("value") == $value and $value > '' ? $inp->attr('checked', 'checked') : null;
                            $inp->attr("done", "");
                        } else {
                            $inp->attr("value", $value);
                            if ($inp->attr("type") == "checkbox") {
                                if ($value == "on" or $value == "true"  or $value == "1") {
                                    $inp->attr("checked", true);
                                    $inp->removeAttr("value");
                                }
                            }
                            $inp->attr("done", "");
                        }
                    }
                } elseif ($inp->hasAttr('type') && !$inp->hasAttr("done")) {
                    $inp->attr("value", $value);
                    $inp->attr("done", "");
                }
            }
        }
        $unset = $this->find("template,textarea,code,pre,[wb-off]");
        foreach ($unset as $t) {
            $t->inner(str_replace(['{{','}}'], ['_(_(_','_)_)_'], $t->inner()));
        }
        if (strpos($this, "{{")) {
            $render = new wbRender($this);
            $html = $render->exec();
            if ($this->tagName == 'title') {
                $this->text($html);
            } else {
                $this->inner($html);
            }
        }
        $unset = $this->find("template,textarea,code,pre,[wb-off]");
        foreach ($unset as $t) {
            $t->inner(str_replace(['_(_(_','_)_)_'], ['{{','}}'], $t->inner()));
        }
        $wbon = $this->find('[wb-on]');
        foreach ($wbon as $wb) {
            $wb->removeAttr('wb-on');
            $wbt = $this->app->fromString('<html>'. $wb->outer().'</html>');
            $wbt->fetch();
            $wb->replaceWith($wbt->html());
        }
        return $this;
    }
}


class wbRender extends WEProcessor
{
    public $vars;
    public $inner;
    public $item;

    public function __construct($dom)
    {
        $this->vars = new Dot();
        $this->inner = $dom->inner();
        $this->parser = new parse_engine(new weparser($this));
        $this->context = $dom->item;
        isset($dom->app->vars) ? $vars = $dom->app->vars : $vars = [];
        $vars = (array)$vars;
        $this->vars->setReference($vars);
    }

    public function exec($item = null)
    {
        !isset($this->item) ? $this->item = [] : null;
        $item == null ? $item = $this->item : null;
        return $this->substitute($this->inner);
    }
}

?>