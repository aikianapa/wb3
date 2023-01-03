<?php

use Adbar\Dot;
use Nahid\JsonQ\Jsonq;

class jsonDrv
{
    public $app;
    public $driver;

    public function __construct(&$app)
    {
        $this->app = &$app;
        $this->driver = 'json';
    }

    public function itemRead($form = null, $id = null)
    {
            $cid = md5($form . $id . $_SESSION['lang']);
            if (isset($_ENV['cache'][$cid])) return $_ENV['cache'][$cid];

            $list = $this->itemList($form, ['orm' => "where('id','{$id}')"]);
            if (isset($list['list'])) {
                $list = $list['list'];
            }
            if (isset($list[$id])) {
                $item = $list[$id];
            } elseif (isset($list[0])) {
                $item = $list[0];
            } else {
                wbError('func', __FUNCTION__, 1006, func_get_args());
                $item = null;
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

    public function tableCreate($form)
    {
        if (!$this->tableExist($form) and $this->app->formExist()) {
            $file = $this->tablePath($form);
            $json = wbJsonEncode(null);
            $res = wbPutContents($file, $json, LOCK_EX);
            if ($res) {
                @chmod($file, 0766);
            }
        } else {
            $res = false;
            wbLog("func", __FUNCTION__, 1012, [$form]);
            wbError('func', __FUNCTION__, 1002, func_get_args());
        }
        return $res;
    }

    public function tableExist($form, $engine = false)
    {
        $file = $this->tablePath($form, $engine);
        return is_file($file);
    }

    public function tableRemove($form, $engine)
    {
        if (wbRole('admin')) {
            false == $engine ? $db = $_ENV['dbac'] : $db = $_ENV['dbec'];
            $cache = $db . '/' . $form;
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
        $cid = md5($file . $_SESSION['lang']);
        if (!isset($_ENV['cache'][$cid])) {
            $_ENV['cache'][$cid] = array();
        }
        if (!isset($item['id']) or '_new' == $item['id'] or $item['id'] == "") {
            $item['id'] = $item['_id'] = wbNewId();
        } else {
            $item['_id'] = $item['id'];
        }
        $_ENV['cache'][$cid][$item['id']] = $item;

        $res = $item;
        $flush == true ? $this->tableFlush($form) : null;
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
            if ($flush == true) {
                $this->tableFlush($form);
            }
        } elseif (is_string($id) or is_numeric($id)) {
            $item = $this->itemRead($form, $id);
            if ($item == null) {
                return $res;
            }
            if (is_array($item)) {
                $item['_removed'] = true;
                $_ENV['cache'][md5($file . $_SESSION["lang"])][$id] = $item;
            }
            $res = wbItemSave($form, $item, $flush);
        }
        return $res;
    }

    public function itemList($form = 'pages', $options = [])
    {
        if (isset($options['size'])) {
            isset($options['page']) ? null : $options['page'] = 1;
            $page = intval($options['page']);
            $size = intval($options['size']);
            $params['limit'] = $size;
            $params['skip'] = $page - 1;
        } else {
            $page = 1;
        }
        $params['sort'] = [];
        $options = (object) $options;
        $list = [];

        if (isset($options->sort)) {
            foreach ((array) $options->sort as $key => $fld) {
                if (!((array)$fld === $fld)) {
                    if (is_numeric($fld)) {
                        $fld = [$key,$fld];
                    } else {
                        $fld = explode(':', $fld);
                    }
                    isset($fld[1]) ? $fld[1] = strtolower($fld[1]) : $fld[1] = 1;
                    if (in_array($fld[1], ['a', 'asc', '1'])) {
                        $fld[1] = '';
                    } elseif (in_array($fld[1], ['d', 'desc', '-1'])) {
                        $fld[1] = 'desc';
                    }
                    $params['sort'][$fld[0]] = $fld[1];
                } else {
                    $params['sort'][$key] = $fld;
                }
            }
        }

        if (isset($options->orm)) {
            $orm = $options->orm;
            $tmp = explode("->", $orm);
            $re = '/^(.*)\((.*)\)/m';
            preg_match('/^(.*)\((.*)\)/m', $tmp[0], $match);
            if ($match[1] == "table") {
                array_shift($tmp);
                $form = $match[2];
                $form = str_replace(["'", '"'], "", $form);
                $file = $this->tableFile($form);
                if (!is_file($file)) {
                    wbError('func', __FUNCTION__, 1001, func_get_args());
                    return [];
                }
                $json = new Jsonq($file);
            } elseif ($match[1] == "field") {
                array_shift($tmp);
                $field = $match[2];
                $field = str_replace(["'", '"'], "", $field);
                if (!isset($list[$field])) {
                    $list[$field] = [];
                }
                $json = new Jsonq();
                $json = $json->collect($list[$field]);
            } elseif (is_string($form)) {
                $file = $this->tableFile($form);
                if (!is_file($file)) {
                    wbError('func', __FUNCTION__, 1001, $form);
                    return [];
                }
                try {
                    $json = new Jsonq($file);
                } catch (Exception $err) {
                    $json = new Jsonq();
                    $json = $json->collect([]);
                }
            } elseif ((array) $form === $form) {
                $json = new Jsonq();
                $json = $json->collect($form);
            }
            $json->empty("");
            $orm = implode("->", $tmp);
            eval('$list = $json->where("_removed","neq","on")->' . $orm . ';');
            if (is_object($list)) {
                if (count($params['sort'])) {
                    foreach ($params['sort'] as $fld => $order) {
                        $list->sortBy($fld, $order);
                    }
                }
                $list = $list->get();
            }
        } else {
            $file = $this->tableFile($form);
            if (!is_file($file)) {
                wbError('func', __FUNCTION__, 1001, func_get_args());
                return [];
            }

            try {
                $json = new Jsonq($file);
            } catch (Exception $err) {
                $json = new Jsonq();
                $json = $json->collect([]);
            }
            $json->empty('');
            $list = $json->where('_removed', 'neq', 'on');
            $list = $list->get();
        }

        $dot = new Dot();
        $iter = new ArrayIterator((array) $list);
        $list = [];
        foreach ($iter as $key => $item) {
                    $flag = true;

            $item = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $item);
            if ($item == null) {
                unset($list[$key]);
            } else {
                isset($item['_id']) ? null : $item['_id'] = &$item['id'];
                if (isset($options->trigger)) {
                    $item = wbTrigger('form', __FUNCTION__, $options->trigger, func_get_args(), $item);
                }
                $dot->setReference($item);
                if ($flag && isset($options->filter) or isset($options->context)) {
                    $flag = wbItemFilter($item, $options);
                }

                if (!$flag) {
                    unset($list[$key]);
                } else {
                    if (isset($item['id']) and !isset($item['_id'])) {
                        $item['_id'] = $item['id'];
                    } else {
                        $item['_id'] = $item['id'] = $key;
                    }

                    $item['_table'] = $item['_form'] = $form;
                    if (isset($options->projection)) {
                        $tmp = [
                        '_id' => $item['_id'],
                    ];
                        foreach ($options->projection as $fld) {
                            $val = $dot->get($fld);
                            isset($val) ? $tmp[$fld] = $val : $tmp[$fld] = null;
                        }
                        $item = $tmp;
                    }
                    $list[$item["_id"]] = $item;
                }
                if (!isset($options->sort) && isset($options->limit) && count($list) == $options->limit) {
                    break;
                }

                $cid = md5($form . $item['_id'] . $_SESSION['lang']);
                $_ENV['cache'][$cid] = $item;
            }
        }

        if (count($params['sort'])) {
            $json = new Jsonq();
            $list = $json->collect($list);
            foreach ($params['sort'] as $fld => $order) {
                $list->sortBy($fld, $order);
            }
            $list = $list->get();
            $list = array_column($list, null, '_id');
            /*
            $iter = new ArrayIterator($list->get());
            $list = [];
            foreach ($iter as $item) {
                $list[$item['_id']] = $item;
            }
            */
        }

        if (isset($options->return)) {
            $return = wbAttrToArray($options->return);
            $return = array_fill_keys($return, true);
            array_walk($list,function(&$item,$key,$return){
                $item = array_intersect_key($item,$return);
            },$return);
        }


        if (isset($options->limit) && count($list)) {
            $list = array_chunk($list, $options->limit);
            $list = $list[0];
        }

        $count = count($list);
        isset($size) ? null : $size = $count;
        if ($size > 0 && $size < $count) {
            $chunk = array_chunk($list, $size);
            $chunk = isset($chunk[$page - 1]) ? $chunk[$page - 1] : [];
            $list = array_column($chunk, null, '_id');
/*
            foreach ($chunk as $item) {
//              $item['_id'] = $item['id'];
                $list[$item['_id']] = $item;
            }
*/
            // для api нужно сделать отдельную обработку опции size, для выдачи разбитого массива полностью
        }
        return ["list" => $list, "count" => $count, "page" => $page, "size" => $size];
    }

    public function tableFlush($form)
    {
        // Сброс кэша в общий файл
        $res = false;
        $file = $this->tablePath($form);
        $cid = md5($file . $_SESSION['lang']);
        if (is_file($file) and isset($_ENV['cache'][$cid])) {
            $cache = $_ENV['cache'][$cid];
            $fp = fopen($file, 'rb');
            flock($fp, LOCK_SH);
            $data = file_get_contents($file);
            $data = json_decode($data, true);
            $flag = false;
            foreach ($cache as $key => $item) {
                $item['_table'] = $form;
                $data[$key] = (isset($data[$key])) ? array_merge($data[$key], $item) : $item;
                $flag = true;
                if (isset($item['_removed']) and true == $item['_removed']) {
                    unset($data[$key]);
                }
            }
            $data = wbJsonEncode($data);
            flock($fp, LOCK_UN);
            fclose($fp);
            $res = ($flag) ? file_put_contents($file, $data, LOCK_EX) : null;
            unset($_ENV['cache'][$cid]);
        }
        return $res;
    }

    public function tableFile($form = 'pages', $engine = false)
    {
        $cid = md5($form.$engine);
        if (isset($_ENV['cache'][$cid])) {
            return $_ENV['cache'][$cid];
        } else {
            $create = false;
            if (strpos($form, ':')) {
                $form = explode(':', $form);
                if ($form[1] == 'engine' or $form[1] == 'e') {
                    $engine = true;
                } elseif ($form[1] == 'create' or $form[1] == 'c') {
                    $create = true;
                }
                $form = $form[1];
            }
            $file = $this->tablePath($form, $engine);
            if (!is_file($file) and $create == true) {
                $this->TableCreate($form);
            }
            if (!is_file($file)) {
                wbError('func', __FUNCTION__, 1001, func_get_args());
                $form = null;
            } else {
                $_ENV[$form]['name'] = $form;
            }
        }
        $_ENV['cache'][$cid] = $file;
        return $file;
    }

    public function tableList($engine = false)
    {
        (false == $engine) ? $db = $_ENV['dba'] : $db = $_ENV['dbe'];
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
