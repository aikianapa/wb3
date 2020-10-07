<?php

require 'vendor/autoload.php';

class mysqlDrv
{
    public function __construct(&$app)
    {
        $this->app = &$app;
        $this->db = $this->connect();
        if (!isset($app->connect)) $app->connect = (object)[];
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
            $connection = new MysqliDb (Array (
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

    public function ItemOconv(&$item) {
        $form = $item['_form'];

        if (isset($this->keys->$form) AND isset($item[$this->keys->$form])) $item['_id'] = $item['id'] = $item[$this->keys->$form];
        if (!isset($item['_json'])) {
            $json = [];
        } else {
            $json = json_decode($item['_json'],true);
        }
        if (!$json) $json = [];
        $item['_json'] = $json;
        $item = array_merge($json,$item);
    }

    public function ItemRead($form = 'pages', $id = null)
    {
        $this->GetProp($form);
        $item = null;
        if ($id !== null && $id !== "_new") {
            $this->db->where($this->keys->$form, $id);
            $item = $this->db->getOne($form);
            if ($item) $item = $this->app->objToArray($item);
            $item = $this->app->ItemInit($form, $item);
            $this->ItemOconv($item);
        }
        return $item;
    }

    public function ItemRemove($form = 'pages', $id = null, $flush = null) {

        if (!$form) return null;
        if (!$id) return null;
        $this->GetProp($form);
        $app = &$this->app;
        $res = false;
        if ((array)$id ===  $id) {
            foreach ($id as $iid) {
                $res = $app->itemRemove($form, $iid);
            }
        } else {
            $item = $this->itemRead($form,$id);
            if ($item) {
                $this->db->where('id', $id);
                if ($this->db->delete($form)) {
                    $res = $item;
                } else {
                    $res = false;
                }
            }
        }
        return $res;
    }

    public function ItemSave($form, $item = null, $flush = true) {
        print_r($item);

    }

    public function ItemList($form = 'pages', $options = [])
    {
        $this->GetProp($form);
        $find = $this->db->get($form);
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
        $list = [];
        $iter = new ArrayIterator($find);
        foreach ($iter as $doc) {
            if (!isset($doc['_id'])) $doc['_id'] = $doc[$this->keys->$form];

            $doc = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $doc);
            $res = true;
            if (isset($options['filter'])) {
                $res = wbItemFilter($doc, $options['filter']);
            } 
            if ($res) $list[''.$doc['_id'].''] = $doc;
        }
        if (count($params['sort'])) {
            $json = new Jsonq();
            $list = $json->collect($list);
            foreach ($params['sort'] as $fld => $order) {
                $list->sortBy($fld, $order);
            }

            $iter = new ArrayIterator($list->get());
            $list = [];
            foreach ($iter as $item) {
                $list[$item['_id']] = $item;
            }
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
            if (isset($chunk[$page - 1])) {$chunk = $chunk[$page - 1];} else { $chunk = [];}
            $list = $chunk;

        }
        return ["list" => $list, "count" => $count, "page" => $page, "size" => $size];
        
    }

    public function FieldsList($form) {
        $fields = $this->db->rawQuery("SELECT COLUMN_KEY as prop, COLUMN_NAME as name,COLUMN_TYPE as type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=? AND TABLE_SCHEMA=?;", [$form,$this->dbname]);
        $names = array_column($fields,'name');
        $check = false;
        $table = $this->dbname . '.' . $form;

        if (!in_array('_id',$names)) {
            $this->db->rawQuery("ALTER TABLE {$table} ADD COLUMN `_id` VARCHAR(45) NOT NULL, ADD UNIQUE INDEX `_id_UNIQUE` (`_id` ASC) ;");
            $check = true;
        }

        if (!in_array('_json',$names)) {
            $this->db->rawQuery("ALTER TABLE {$table} ADD COLUMN `_json` LONGTEXT NOT NULL;");
            $check = true;
        }
        if ($check) {
            $fields = $this->db->rawQuery("SELECT COLUMN_KEY as prop, COLUMN_NAME as name,COLUMN_TYPE as type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=? AND TABLE_SCHEMA=?;", [$form,$this->dbname]);            
        }

        return $fields;
    }

    public function PrimaryKey($form) {
        $field = $this->db->rawQuery("SELECT COLUMN_KEY as prop, COLUMN_NAME as name FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_KEY = 'PRI' AND TABLE_NAME=? AND TABLE_SCHEMA=?;", [$form,$this->dbname]);
        if (count($field)) return $field[0]['name'];
    }

    public function GetProp($form) {
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
?>