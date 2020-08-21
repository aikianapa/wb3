<?php
use Nahid\JsonQ\Jsonq;
use Adbar\Dot;

class jsonDrv
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function itemRead($form = null, $id = null)
    {
        $file = $this->tableFile($form);
        if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';
        $cid = md5($file.$_SESSION['lang']);
        if (isset($_ENV['cache'][$cid][$id])) {
            $item = $_ENV['cache'][$cid][$id];
        } else {
            $list = $this->itemList($form, ['orm'=>"where('id','{$id}')"])['list'];
            if (isset($list[$id])) {
                $item = $list[$id];
            } else if (isset($list[0])) {
                $item = $list[0];
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

    public function tableCreate($form, $engine=false)
    {
        $file = $this->tablePath($form, $engine);
        if (!is_file($file)) {
            $json = wbJsonEncode(null);
            $res = wbPutContents($file, $json, LOCK_EX);
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
        $file = $this->tablePath($form);
        $res = null;
        $cid = md5($file.$_SESSION['lang']);
        if (!isset($_ENV['cache'][$cid])) {
            $_ENV['cache'][$cid] = array();
        }
        if (!isset($item['id']) or '_new' == $item['id'] or $item['id'] == "") {
            $item['id'] = $item['_id'] = wbNewId();
        } else {
            $item['_id'] = $item['id'];
        }
        $item = wbItemInit($form, $item);
        $_ENV['cache'][$cid][$item['id']] = $item;

        $res = $item;
        if ($flush == true) {
            $this->tableFlush($form);
        }
        return $res;
    }

    public function itemRemove($form = null, $id = null, $flush = true)
    {
        $res = false;
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
                $_ENV['cache'][md5($file.$_SESSION["lang"])][$id] = $item;
            }
            $res = wbItemSave($form, $item, $flush);
        }
        return $res;
    }

    public function itemList($form = 'pages', $options = [])
    {
        if (isset($options['size'])) {
            if (!isset($options['page'])) $options['page'] = 1;
            $page = intval($options['page']);
            $size = intval($options['size']);
            $params['limit'] = $size;
            $params['skip'] = $page - 1;
        } else {
            $page = 1;
        }
        $params['sort'] = [];
        $options = (object)$options;
        $list = [];

        if (isset($options->sort)) {
            foreach((array)$options->sort as $key=> $fld) {
                if (!((array)$fld === $fld)) {
                    $fld = explode(':',$fld);
                    if (!isset($fld[1])) {
                        $fld[1] = 1;
                    } else if (in_array(strtolower($fld[1]),['a','asc','1'])) {
                        $fld[1] = '';
                    } else if (in_array(strtolower($fld[1]),['d','desc','-1'])) {
                        $fld[1] = 'desc';
                    }
                    $params['sort'][$fld[0]] = $fld[1];
                } else {
                    $params['sort'][$key] = $fld;
                }

            }
        }

/*
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
                if (count($params['sort'])) {
                    foreach($params['sort'] as $fld => $order) $list->sortBy($fld,$order);
                }
                $list = $list->get();
            }
        } else {
*/
            $file = $this->tableFile($form);
            if (!is_file($file)) {
                wbError('func', __FUNCTION__, 1001, func_get_args());
                return [];
            }

            try {
                $json = new Jsonq($file);}
            catch(Exception $err) {
              $json = new Jsonq();
              $json = $json->collect([]);
            }
            $json->empty('');
            $list = $json->where('_removed', 'neq', 'on');
            $list = $list->get();

//        }

        $dot = new Dot();
        $iter = new ArrayIterator((array)$list);
        $list = [];
        $flag = true;
        foreach($iter as $key => $item) {
            $dot->setReference($item);
            if (isset($options->filter)) $flag = wbItemFilter($item,$options->filter);
            if (!$flag) {unset($list[$key]); } else {
              if (isset($item['id']) AND !isset($item['_id'])) $item['_id'] = $item['id'];
              $item['_table'] = $item['_form'] = $form;
              if (isset($options->projection)) {
                  $tmp = [
                      '_id'=>$item['_id'],
                  ];
                  foreach($options->projection as $fld => $v) {
                      $v = $dot->get($fld);
                      if (isset($v)) $tmp[$fld] = $v;
                  }
                  $item = $tmp;
              }
              $item = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $item);
              $list[$item["_id"]] = $item;
            }
            if (isset($options->limit) && count($list) == $options->limit) break;
        }

        if (count($params['sort'])) {
            $json = new Jsonq();
            $list = $json->collect($list);
            foreach($params['sort'] as $fld => $order) $list->sortBy($fld,$order);
            $iter = new ArrayIterator($list->get());
            $list = [];
            foreach($iter as $item) {
                $list[$item['_id']] = $item;
            }
        }
        $count = count($list);
        if (!isset($size)) $size = $count;
        if ($size > 0 && $size < $count ) {
          $chunk = array_chunk($list,$size);
          if (isset($chunk[$page -1])) {$chunk = $chunk[$page -1];} else {$chunk = [];}
          $list = [];
          foreach($chunk as $item) {
//              $item['_id'] = $item['id'];
              $list[$item['_id']] = $item;
          }


        }
        return ["list"=>$list,"count"=>$count,"page"=>$page,"size"=>$size];
    }

    public function tableFlush($form)
    {
        // Сброс кэша в общий файл
        $res = false;
        $file = $this->tablePath($form);
        $cid = md5($file.$_SESSION['lang']);
        if (is_file($file) and isset($_ENV['cache'][$cid])) {
            $cache = $_ENV['cache'][$cid];
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
//                    if (wbRole('admin')) {
                        unset($data[$key]);
//                    }
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
            unset($_ENV['cache'][$cid]);
        }
        return $res;
    }

    public function tableFile($form = 'pages', $engine = false)
    {
        $create = false;
        if (strpos($form, ':')) {
            $form=explode(':', $form);
            if ($form[1]=='engine' or $form[1]=='e') {
                $engine=true;
            } elseif ($form[1]=='create' or $form[1]=='c') {
                $create = true;
            }
            $form=$form[1];
        }
        $file = $this->tablePath($form, $engine);
        if (!is_file($file) and ($form > '' or $create == true)) {
            $this->TableCreate($form);
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
