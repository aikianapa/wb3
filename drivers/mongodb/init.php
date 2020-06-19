<?php
require 'vendor/autoload.php';

class mongodbDrv
{
    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $this->connect();
    }

    public function _call($method, $params)
    {
        //print_r($method);
    }

    public function connect()
    {
        if (isset($_ENV["mongodb_connection"])) {
            return $_ENV["mongodb_connection"];
        }
        $ini = $this->app->settings->driver_options["mongodb"];
        $dbname = $ini['dbname'];
        try {
            $mongo = new MongoDB\Client("mongodb://{$ini['user']}:{$ini['password']}@{$ini['host']}:{$ini['port']}");
            $connection = $mongo->$dbname;
            if (!$mongo) {
                throw new Exception('mongoErr');
            }
            $_ENV["mongodb_connection"] = $connection;
        } catch (Exception $err) {
            echo "Mongo DB connection error!";
            echo $err->getMessage();
            die;
        }
        return $connection;
    }

    public function ItemRead($form = null, $id = null)
    {
        $item = null;
        if ($id !== null && $id !== "_new") {
            try {
                $item = $this->db->$form->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
                $item = $this->app->objToArray($item);
                if ((array)$item['_id'] === $item['_id'] AND count($item)) $item['_id'] = $item['_id']['$oid'];
            } catch (Exception $err) {
                try {
                    $item = $this->db->$form->findOne(['_id' => $id]);
                    $item = $this->app->objToArray($item);
                    if ((array)$item['_id'] === $item['_id'] AND count($item)) $item['_id'] = $item['_id']['$oid'];
                }
                catch(Exception $err) {
                    $item = null;
                }
            }
        }
        return $item;
    }

    public function ItemList($form = 'pages', $options = [])
    {
        $list = array();
        $filter = [];
        $params = [];
        if (isset($options["filter"])) $filter = $options["filter"];
        if (isset($options["page"])) {
            $page = intval($options["page"]);
            $size = intval($options["size"]);
            $params["limit"] = $size;
            $params["skip"] = ($page - 1) * $size;
        } else {
            $page = 1;
        }
        $filter = $this->filterPrepare($filter);

        $count = $find = $this->db->$form->count($filter);
        if (!isset($size)) $size = $count;
        $find = $this->db->$form->find($filter, $params);
        $find = json_decode(json_encode($find->toArray()), true);
        $list = [];
        foreach ($find as &$doc) {
            $oid = '$oid';
            if ((array)$doc['_id'] === $doc['_id']) $doc['_id'] = $doc['_id'][$oid];
            $doc["_form"] = $doc["_table"] = $form;
            $list[$doc['_id']] = $doc;
        }
        return ["list"=>$list,"count"=>$count,"page"=>$page,"size"=>$size];
    }

    function filterPrepare($filter) {
        if (in_array('$like',array_keys($filter))) {
            if (isset($filter['$like'])) {
              $filter = ['$regex' => '(?i)'.$filter['$like']];
              return $filter;
            }
        }


        foreach($filter as $key => $node) {
            if ((array)$node === $node) $node = $this->filterPrepare($node);
            $filter[$key] = $node;
        }
        return $filter;
    }

    public function ItemRemove($form = null, $id = null, $flush = true)
    {
        if (!$form) return null;
        if (!$id) return null;
        $app = $this->app;
        $res = false;
        if ((array)$id ===  $id) {
            foreach ($id as $iid) {
                $res = $app->itemRemove($form, $iid);
            }
        } else {
            $sid = $id;
            $id = $this->init_id($sid);
            $item = $this->itemRead($form,$id);
            if ($item) {
              try {
                  $this->db->$form->deleteOne(["_id" => $id ]);
                  $res = $item;
              } catch(Exception $err) {
                  $res = false;
              }
            }
        }
        return $res;
    }

    function init_id($id) {
      try {
          $id = new \MongoDB\BSON\ObjectID($id);
      }
      catch(Exception $err) {
          $id = $id;
      }
      return $id;
    }

    public function ItemSave($form, $item = null, $flush = true)
    {
        $res = null;
        if (!$form) return null;
        $item = wbItemInit($form, $item);
        $tmp = $this->app->itemRead($form,$item["_id"]);
        $sid = $item["_id"];
        $id = $item["_id"] = $this->init_id($sid);
        try {
            if (!$tmp) {
                $this->db->$form->insertOne($item);
            } else {
                $this->db->$form->updateOne(["_id" => $id],['$set' => $item]);
            }
        } catch(Exception $err) {
          echo $err;
            echo "Error"; die;
        }
        wbTrigger('form', __FUNCTION__, 'AfterItemSave', func_get_args(), $item);
        $item["_id"] = $sid;
        return $item;
    }

    public function TableFlush($form)
    {
        // Сброс кэша в общий файл
        return true;
    }

    public function TableCreate($form, $engine)
    {
        if ($form == "admin") {
            return false;
        }
        if ($engine) {
            return jsonTableCreate($form, $engine);
        }
        if ($form) {
        } else {
            wbError('func', __FUNCTION__, 1002, func_get_args());
        }
    }

    public function TableRemove($form, $engine)
    {
        if ($form == "admin") {
            return false;
        }
        if (wbRole('admin')) {
            if (!$form) { // не удалилось
                wbError('func', __FUNCTION__, 1003, func_get_args());
            }
            $res = $form;
            if (!$form) { // не существует
                wbError('func', __FUNCTION__, 1001, func_get_args());
            }
        }
    }

    public function TableList($engine = false)
    {
        if ($engine) {
            return jsonTableList(true);
        }
        $list = [];
        return $list;
    }
}
