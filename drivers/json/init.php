<?php
use Nahid\JsonQ\Jsonq;

class jsonDrv
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function itemRead($form = null, $id = null)
    {
        $file = $this->tableFile($form);
        if (isset($_ENV['cache'][md5($file.$_SESSION["lang"])][$id])) {
            $item = $_ENV['cache'][md5($file.$_SESSION["lang"])][$id];
        } else {
            $list = $this->itemList($form, ["orm"=>"where('id','{$id}')"])["list"];
            if (isset($list[$id])) {
                $item = $list[$id];
            } else {
                wbError('func', __FUNCTION__, 1006, func_get_args());
                $item = null;
            }
        }
        return $item;
    }

    public function tablePath($form = 'pages', $engine = false)
    {
        $db = $_ENV['dbe'];
        if (false == $engine) {
            $db = $_ENV['dba'];
        }
        return "{$db}/{$form}.json";
    }

    public function tableCreate($form, $engine)
    {
        $file = $this->tablePath($form, $engine);
        if (!is_file($file)) {
            $json = wbJsonEncode(null);
            $res = file_put_contents($form, $json, LOCK_EX);
            if ($res) {
                @chmod($file, 0766);
            }
        } else {
            wbError('func', __FUNCTION__, 1002, func_get_args());
        }
        return $res;
    }

    public function tableRemove($form, $engine)
    {
        if (wbRole('admin')) {
            $db = $_ENV['dbec'];
            if (false == $engine) {
                $db = $_ENV['dbac'];
            }
            $cache = $db.'/'.$form;
            $file = $this->tablePath($form, $engine);
            wbRecurseDelete($cache);
            if (is_file($file)) {
                wbRecurseDelete($cache);
                unlink($file);
                if (is_file($file)) { // не удалилось
                    wbError('func', __FUNCTION__, 1003, func_get_args());
                }
                $res = $file;
            } else { // не существует
                wbError('func', __FUNCTION__, 1001, func_get_args());
                $res = false;
            }
        }
        return $res;
    }

    public function itemSave($form, $item = null, $flush = true)
    {
        $item = wbItemInit($form, $item);
        $file = $this->tablePath($form);
        $res = null;
        if (!is_file($file)) {
            wbError('func', __FUNCTION__, 1001, func_get_args());
            return null;
        }

        if (!isset($_ENV['cache'][md5($file.$_SESSION["lang"])])) {
            $_ENV['cache'][md5($file.$_SESSION["lang"])] = array();
        }
        if (!isset($item['id']) or '_new' == $item['id'] or $item['id'] == "") {
            $item['id'] = wbNewId();
        }

        $_ENV['cache'][md5($file.$_SESSION["lang"])][$item['id']] = $item;
        wbTrigger('form', __FUNCTION__, 'AfterItemSave', func_get_args(), $item);
        $res = $item;
        if ($flush == true) {
            $this->tableFlush($form);
        }
        return $res;
    }

    public function itemRemove($form = null, $id = null, $flush = true)
    {
        $file = $this->tableFile($form);
        if (!is_file($file)) {
            wbError('func', __FUNCTION__, 1001, func_get_args());
            return null;
        }
        if (is_array($id)) {
            foreach ($id as $iid) {
                $res = $this->itemRemove($form, $iid, false);
            }
            if ($flush==true) {
                $this->tableFlush($form);
            }
        } elseif (is_string($id) or is_numeric($id)) {
            $item = $this->itemRead($form, $id);
            if ($item == null) {
                return $res;
            }
            if (is_array($item)) {
                $item['_removed'] = true;
                $item=wbTrigger('form', __FUNCTION__, 'BeforeItemRemove', func_get_args(), $item);
                $_ENV['cache'][md5($file.$_SESSION["lang"])][$id] = $item;
            }
            $res = wbItemSave($form, $item, $flush);
        }
        return $res;
    }

    public function itemList($form = 'pages', $options = [])
    {
        if (isset($options["_page"])) {
            $page = intval($options["_page"]);
            $size = intval($options["_size"]);
            $params["limit"] = $size;
            $params["skip"] = $page - 1;
        } else {
            $page = 1;
        }
        $options = (object)$options;
        $list = [];
        if (isset($options->orm)) {
            $orm = $options->orm;
            $tmp = explode("->", $orm);
            $re = '/^(.*)\((.*)\)/m';
            preg_match('/^(.*)\((.*)\)/m', $tmp[0], $match);
            if ($match[1] == "table") {
                array_shift($tmp);
                $form = $match[2];
                $form = str_replace(["'",'"'], "", $form);
                $file = $this->tableFile($form);
                if (!is_file($file)) {
                    wbError('func', __FUNCTION__, 1001, func_get_args());
                    return array();
                }
                $json = new Jsonq($file);
            } elseif ($match[1] == "field") {
                array_shift($tmp);
                $field = $match[2];
                $field = str_replace(["'",'"'], "", $field);
                if (!isset($list[$field])) {
                    $list[$field] = [];
                }
                $json = new Jsonq();
                $json = $json->collect($list[$field]);
            } elseif (is_string($form)) {
                $file = $this->tableFile($form);
                if (!is_file($file)) {
                    wbError('func', __FUNCTION__, 1001, func_get_args());
                    return array();
                }
                try {$json = new Jsonq($file);}
                catch(Exception $err) {
                  $json = new Jsonq();
                  $json = $json->collect([]);
                }
            } elseif ((array)$form === $form) {
                $json = new Jsonq();
                $json = $json->collect($form);
            }
            $json->empty("");
            $orm = implode("->", $tmp);
            eval('$list = $json->where("_removed","neq","on")->'.$orm.';');
            if (is_object($list)) {
                $list = $list->get();
            }
        } else {
            $file = $this->tableFile($form);
            if (!is_file($file)) {
                wbError('func', __FUNCTION__, 1001, func_get_args());
                return array();
            }
            $json = new Jsonq($file);
            $json->empty("");
            $list = $json->where("_removed", "neq", "on")->get();
        }
        if (!((array)$list === $list)) {$list = (array)$list;}
        if (isset($options->filter)) {
            foreach($list as $key => $item) {
                $flag = wbItemFilter($item,$options->filter);
                if (!$flag) {unset($list[$key]); } else {
                  if (isset($options["return"])) {
                      $tmp = [];
                      foreach((array)$options["return"] as $fld) {
                          if (isset($item[$fld])) $tmp[$fld] = $item["fld"];
                      }
                      $item = $tmp;
                  }
                  $item = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $item);
                  $list[$key] = $item;
                }
            }
        }
        $count = count($list);
        if (!isset($size)) $size = $count;
        return ["list"=>$list,"count"=>$count,"page"=>$page,"size"=>$size];
    }

    public function tableFlush($form)
    {
        // Сброс кэша в общий файл
        $res = false;
        $file = $this->tablePath($form);
        $cache = $_ENV['cache'][md5($file.$_SESSION["lang"])];
        if (is_file($file) and isset($_ENV['cache'][md5($file.$_SESSION["lang"])])) {
            $fp = fopen($file, 'rb');
            flock($fp, LOCK_SH);
            $data = file_get_contents($file);
            $data = json_decode($data, true);
            $flag = false;
            foreach ($cache as $key => $item) {
                $item['_table'] = $form;
                if (isset($data[$key])) {
                    $data[$key]=array_merge($data[$key], $item);
                } else {
                    $data[$key]=$item;
                }
                $flag = true;
                if (isset($item['_removed']) and true == $item['_removed']) {
                    if (wbRole('admin')) {
                        unset($data[$key]);
                    }
                }
            }
            $data = wbJsonEncode($data);
            flock($fp, LOCK_UN);
            fclose($fp);
            if ($flag) {
                $res = file_put_contents($file, $data, LOCK_EX);
            } else {
                $res = null;
            }
            unset($_ENV['cache'][md5($file.$_SESSION["lang"])]);
        }
        return $res;
    }

    public function tableFile($form = 'pages', $engine = false)
    {
        $create = false;
        if (strpos($form, ":")) {
            $form=explode(":", $form);
            if ($form[1]=="engine" or $form[1]=="e") {
                $engine=true;
            } elseif ($form[1]=="create" or $form[1]=="c") {
                $create = true;
            }
            $form=$form[1];
        }
        $file = $this->tablePath($form, $engine);
        if (!is_file($file) and ($form > '' or $create == true)) {
            wbTableCreate($tname);
        }
        if (!is_file($file)) {
            wbError('func', __FUNCTION__, 1001, func_get_args());
            $form = null;
        } else {
            $_ENV[$form]['name'] = $form;
        }
        return $file;
    }

    public function tableList($engine = false)
    {
        $db = $_ENV['dbe'];
        if (false == $engine) {
            $db = $_ENV['dba'];
        }
        $list = wbListFiles($db);
        foreach ($list as $i => $form) {
            $tmp = explode('.', $form);
            if ('json' !== array_pop($tmp)) {
                unset($list[$i]);
            } else {
                $list[$i] = substr($form, 0, -5);
            }
        }
        return $list;
    }
}
