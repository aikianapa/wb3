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
                    $this->ItemPrepare($item);
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
            if (isset($options["limit"])) $params["limit"] = $options["limit"];
        }

        if (isset($options["projection"])) {
            $params["projection"] = $options["projection"];
        }
        if (isset($options["return"])) {
            if (! ((array)$options["return"] === $options["return"])) {
                $options["return"] = $this->app->ArrayAttr($options["return"]);
            }
            $params["projection"] = [];
            foreach((array)$options["return"] as $fld) {
                $params["projection"][$fld] = 1;
            }
        }

        if (isset($options['sort'])) {
            $params['sort'] = [];
            foreach((array)$options['sort'] as $key=> $fld) {
                if (!strpos(':',$key) AND is_numeric($fld) AND in_array($fld*1,[1,-1]) ) {
                    $params['sort'][$key] = $fld;
                } else if (!((array)$fld === $fld)) {
                    $fld = explode(":",$fld);
                    if (!isset($fld[1])) {
                        $fld[1] = 1;
                    } else if (in_array(strtolower($fld[1]),['a','asc','1'])) {
                        $fld[1] = 1;
                    } else if (in_array(strtolower($fld[1]),['d','desc','-1'])) {
                        $fld[1] = -1;
                    }
                    $params['sort'][$fld[0]] = $fld[1];
                } else {
                    $params['sort'][$key] = $fld;
                }

            }
        }

        $filter = $this->filterPrepare($filter);
        $count = $this->db->$form->count($filter);
        if (!isset($size)) $size = $count;

        $find = $this->db->$form->find($filter, $params);
        $list = [];
        $iter = new IteratorIterator($find);
        foreach ($iter as $doc) {
            $doc = $doc->getArrayCopy();
            if ((object)$doc['_id'] === $doc['_id']) $doc['_id'] = (array)$doc['_id'];
            if ((array)$doc['_id'] === $doc['_id']) $doc['_id'] = $doc['_id']['oid'];
            $doc["_form"] = $doc["_table"] = $form;
            $doc = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $doc);
            $list[$doc['_id']] = $doc;
        }
        return ["list"=>$list,"count"=>$count,"page"=>$page,"size"=>$size];
    }

    function filterPrepare($filter) {
        $filter = (array)$filter;
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

    public function ItemPrepare(&$item) {
        if (!( (array)$item === $item  )) return;
        foreach($item as $key => &$val) {
            if (is_object($val)) $val = (array)$val;
            if ($key == "_id" && (array)$val === $val && array_keys($val) == ['$oid']) {
                $val = $this->init_id($val['$oid']);
            }
            if ((array)$val === $val) $this->ItemPrepare($val);
        }
    }

    public function ItemSave($form, $item = null, $flush = true)
    {
        $res = null;
        if (!$form) return null;
        $this->ItemPrepare($item);
        $item = wbItemInit($form, $item);
        $id = $sid = $item["id"];
        $item["_id"] = $this->init_id($sid);
        try {
            try {
                $this->db->$form->insertOne($item);
            } catch(Exception $err) {
                $this->db->$form->updateOne(["_id" => $item["_id"]],['$set' => $item]);
            }
        } catch(Exception $err) {
            echo "Error: ".$err; die;
        }
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
