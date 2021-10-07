<?php
require_once __DIR__. '/vendor/autoload.php';
use Nahid\JsonQ\Jsonq;

class mysqlDrv
{
    public function __construct(&$app)
    {
        $this->app = &$app;
        $this->db = $this->connect();
        if (!isset($app->connect)) {
            $app->connect = (object)[];
        }
        $ini = $app->settings->driver_options["mysql"];
        $this->dbname = $ini["dbname"];
        if (!isset($app->connect->mysql)) {
            $app->connect->mysql = (object)[
                'fields' => (object)[],
                'keys' => (object)[]
            ];
        }
        $this->fields = &$app->connect->mysql->fields;
        $this->keys = &$app->connect->mysql->keys;
        $_ENV["mysql_connection"] = &$this->db;
    }

    public function _call($method, $params)
    {
        //print_r($method);
    }

    public function connect()
    {
        if (isset($_ENV["mysql_connection"])) {
            return $_ENV["mysql_connection"];
        }
        $ini = $this->app->settings->driver_options["mysql"];
        try {
            $connection = new MysqliDb(array(
                'host' => $ini["host"],
                'username' => $ini["user"],
                'password' => $ini["password"],
                'db'=> $ini["dbname"],
                'port' => intval($ini['port']),
                'prefix' => '',
                'charset' => 'utf8'));

            if (!$connection) {
                throw new Exception('mysqlErr');
            }
            $_ENV["mysql_connection"] = $connection;
        } catch (Exception $err) {
            echo "Mysql DB connection error!";
            echo $err->getMessage();
            die;
        }
        return $connection;
    }

    public function ItemOconv(&$item)
    {
        $form = $item['_form'];
        if (!isset($item['_json'])) {
            $json = [];
        } else {
            $json = json_decode($item['_json'], true);
        }
        if (!$json) {
            $json = [];
        }
        $item['_json'] = $json;
        $item = array_merge($json, $item);
        if (isset($this->keys->$form) and isset($item[$this->keys->$form])) {
            $item['_id'] = $item['id'] = $item[$this->keys->$form];
        }
    }

    public function ItemRead($form = 'pages', $id = null)
    {
        $this->GetProp($form);
        $item = null;
        if ($id !== null && $id !== "_new") {
            $this->db->where($this->keys->$form, $id);
            $item = $this->db->getOne($form);
            if ($item) {
                $item = $this->app->objToArray($item);
            }
            $item = $this->app->ItemInit($form, $item);
            $this->ItemOconv($item);
        }
        return $item;
    }

    public function ItemRemove($form = 'pages', $id = null, $flush = null)
    {
        if (!$form) {
            return null;
        }
        if (!$id) {
            return null;
        }
        $this->GetProp($form);
        $app = &$this->app;
        $res = false;
        if ((array)$id ===  $id) {
            foreach ($id as $iid) {
                $res = $app->itemRemove($form, $iid);
            }
        } else {
            $item = $this->itemRead($form, $id);
            if ($item) {
                $this->db->where($this->keys->$form, $id);
                if ($this->db->delete($form)) {
                    $res = $item;
                } else {
                    $res = false;
                }
            }
        }
        return $res;
    }

    public function ItemSave($form, $item = null, $flush = true)
    {
        $this->GetProp($form);
        if (isset($item[$this->keys->$form])) {
            $item['_id'] = $item['id'] = $item[$this->keys->$form];
        }
        $item = $this->app->ItemInit($form, $item);
        $id = $item['_id'];
        $check = $this->itemExist($form, $id);
        if ($check) {
            $json = $this->ItemJsonData($form, $id);
        } else {
            $json = [];
            $item[$this->keys->$form] = $id;
        }

        $fields = array_column($this->fields->$form, 'name');
        foreach ($item as $fld => $val) {
            if (!in_array($fld, $fields) and !in_array($fld, ['id','_id'])) {
                $json[$fld] = $val;
                unset($item[$fld]);
            }
        }

        if (!in_array('id', $fields)) {
            unset($item['id']);
        }
        $item['_json'] = $this->app->jsonEncode($json);
        if ($check) {
            $this->db->where($this->keys->$form, $id);
            $this->db->update($form, $item);
        } else {
            $newid = $this->db->insert($form, (array)$item);
            if (!isset($item[$this->keys->$form])) {
                $id = $newid;
            }
        }

        if ($this->db->count) {
            $item['id'] = $item['_id'] = $id;
            $item['_json'] = $json;
            return $item;
        } else {
            return null;
        }
    }

    public function itemExist($form, $id)
    {
        $this->db->where($this->keys->$form, $id);
        $this->db->getOne($form, $this->keys->$form);
        return $this->db->count;
    }

    public function tableExist($form, $engine = false) {
        $engine == false ? null : $form = '_'.$form;
        return $this->db->rawQueryValue("SELECT count(*) FROM information_schema.TABLES WHERE TABLE_NAME = '{$form}' AND TABLE_SCHEMA in (SELECT DATABASE());");
    }

    public function tableCreate($form, $engine = false) {
        $engine == false ? null : $form = '_'.$form;
        $this->db->rawQueryValue("CREATE TABLE {$form}");
        $this->fieldsList($form);
    }

    public function ItemJsonData($form, $id)
    {
        $this->db->where($this->keys->$form, $id);
        $json = $this->db->getOne($form, '_json');
        if (!isset($json['_json']) or $json['_json'] == '' or !$json['_json']) {
            $json = [];
        } else {
            $json = json_decode($json['_json'], true);
        }
        return $json;
    }

    public function ItemList($form = 'pages', $options = [])
    {
        $this->GetProp($form);
        $find = $this->db->get($form);
        if (isset($options['size'])) {
            if (!isset($options['page'])) {
                $options['page'] = 1;
            }
            $page = intval($options['page']);
            $size = intval($options['size']);
            $params['limit'] = $size;
            $params['skip'] = $page - 1;
        } else {
            $page = 1;
        }

        $params['sort'] = [];
        $options = (object) $options;

        if (isset($options->sort)) {
            // нужно проверять чтобы сортировка работала и а функциях и в api
            foreach ((array) $options->sort as $key => $fld) {
                if (!is_array($fld) AND !is_string($key)) {
                    $fld = explode(':', $fld);
                    if (!isset($fld[1])) {
                        $fld[1] = '';
                    } elseif (in_array(strtolower($fld[1]), ['a', 'asc', '1'])) {
                        $fld[1] = '';
                    } elseif (in_array(strtolower($fld[1]), ['d', 'desc', '-1'])) {
                        $fld[1] = 'desc';
                    }
                    $params['sort'][$fld[0]] = $fld[1];
                } else {
                    if ($fld == '-1') {
                        $fld = 'desc';
                    }
                    $params['sort'][$key] = $fld;
                }
            }
        }

        $list = [];
        $iter = new ArrayIterator($find);

        foreach ($iter as $doc) {
            //if (!isset($doc['_id'])) $doc['_id'] = $doc[$this->keys->$form];
            if (isset($this->keys->$form) and isset($doc[$this->keys->$form])) {
                $doc['_id'] = $doc['id'] = $doc[$this->keys->$form];
            }
            $doc = $this->app->ItemInit($form, $doc);
            $this->ItemOconv($doc);
            $doc = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $doc);
            $res = true;
            if (isset($options->filter) or isset($options->context)) {
                $res = wbItemFilter($doc, $options);
            }
            $res ? $list[] = $doc : null;
        }
        
        if (count($params['sort'])) {
            $json = new Jsonq();
            $list = $json->collect($list);
            foreach ($params['sort'] as $fld => $order) {
                $list->sortBy($fld, $order);
            }
            $list = $list->get();
        }

        if (isset($options->limit) && count($list)) {
            $list = array_chunk($list, $options->limit);
            $list = $list[0];
        }

        $count = count($list);
        if (!isset($size)) {
            $size = $count;
        }

        if ($size > 0 && $size < $count) {
            $chunk = array_chunk($list, $size);
            if (isset($chunk[$page - 1])) {
                $chunk = $chunk[$page - 1];
            } else {
                $chunk = [];
            }
            $list = $chunk;
        }

        $iter = new ArrayIterator($list);
        $list = [];
        foreach ($iter as $item) {
            $list[''.$item['_id']] = $item;
        }

        if (count($params['sort']) AND ($size > 0 && $size < $count)) {
            // ещё раз сортируем - требуется оптимизировать алгоритм построения списка
            $json = new Jsonq();
            $list = $json->collect($list);
            foreach ($params['sort'] as $fld => $order) {
                $list->sortBy($fld, $order);
            }
            $list = $list->get();
        }



        return ["list" => $list, "count" => $count, "page" => $page, "size" => $size];
    }

    public function FieldsList($form)
    {
        $fields = $this->db->rawQuery("SELECT COLUMN_KEY as prop, COLUMN_NAME as name,COLUMN_TYPE as type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=? AND TABLE_SCHEMA=?;", [$form,$this->dbname]);
        $names = array_column($fields, 'name');
        $check = false;
        $table = $this->dbname . '.' . $form;

        if (!in_array('_id', $names)) {
            $this->db->rawQuery("ALTER TABLE {$table} ADD COLUMN `_id` VARCHAR(45), ADD UNIQUE INDEX `_id_UNIQUE` (`_id` ASC) ;");
            $check = true;
        }

        if (!in_array('_json', $names)) {
            $this->db->rawQuery("ALTER TABLE {$table} ADD COLUMN `_json` LONGTEXT NOT NULL;");
            $check = true;
        }
        if ($check) {
            $fields = $this->db->rawQuery("SELECT COLUMN_KEY as prop, COLUMN_NAME as name,COLUMN_TYPE as type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=? AND TABLE_SCHEMA=?;", [$form,$this->dbname]);
        }

        return $fields;
    }

    public function PrimaryKey($form)
    {
        $field = $this->db->rawQuery("SELECT COLUMN_KEY as prop, COLUMN_NAME as name FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_KEY = 'PRI' AND TABLE_NAME=? AND TABLE_SCHEMA=?;", [$form,$this->dbname]);
        if (count($field)) {
            return $field[0]['name'];
        }
    }

    public function GetProp($form)
    {
        if (!isset($this->app->connect->mysql->fields->$form)) {
            $this->app->connect->mysql->fields->$form = $this->FieldsList($form);
        }
        if (!isset($this->app->connect->mysql->keys->$form)) {
            $this->app->connect->mysql->keys->$form = $this->PrimaryKey($form);
        }
 
        $this->keys->$form = $this->app->connect->mysql->keys->$form;
        $this->fields->$form = $this->app->connect->mysql->fields->$form;
    }
}
