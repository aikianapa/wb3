<?php

//require 'vendor/autoload.php';

class mongodbDrv
{
    public function __construct(&$app)
    {
        $this->app = &$app;
        $this->driver = 'mongodb';
        $this->db = $this->connect();
    }

    public function connect() //ok
    {
        if (isset($_ENV["mongodb_connection"])) {
            return $_ENV["mongodb_connection"];
        }
        $ini = $this->app->settings->driver_options["mongodb"];
        $dbname = '';
        isset($ini['dbname']) ? $dbname = $ini['dbname'] : null;
        $dbname == '' and isset($ini['dbnull']) ? $dbname = $ini['dbnull'] : null;
        $dbname == '' ? $dbname = str_replace('.', '_', $this->app->route->domain) : null;
        try {
            $mongo = new MongoDB\Driver\Manager("mongodb://{$ini['user']}:{$ini['password']}@{$ini['host']}:{$ini['port']}");
            if (!$mongo) {
                throw new Exception('mongoErr');
            }
            //$connection = $mongo->$dbname;
            $_ENV["mongodb_connection"] = $mongo;
        } catch (Exception $err) {
            echo "Mongo DB connection error!";
            echo $err->getMessage();
            die;
        }
        $mongo->dbname = $dbname;
        $mongo->bulk = null;
        return $mongo;
    }

    public function toArray(&$item, $root = true) //ok
    {
        if ($item == null) {
            return;
        }
        (object)$item === $item ? $item = json_decode(MongoDB\BSON\toRelaxedExtendedJSON(MongoDB\BSON\fromPHP($item)), true) : null;


        foreach ($item as $key => &$val) {
            (array)$val === $val ? $this->toArray($val, false) : null;
            if (substr($key, 0, 1) == '$') {
                switch ($key) {
                    case '$oid':
                        $item = $val;
                        return;
                    case '$date':
                        $val = date('Y-m-d H:i:s', strtotime($val));
                        $item = $val;
                        return;
                    case '$array':
                        $item = (array)$val;
                        return;
                    case '$string':
                        $item = (string)$val;
                        return;
                    case '$numberInt':
                        $item = intval($val);
                        return;
                    case '$numberLong':
                        $item = intval($val);
                        // no break
                    case '$numberDecimal':
                        $item = number_format($val, 2, '.', '') * 1;
                        return;
                }
            }
        }
    }


    public function ItemRead($form = null, $id = null) //ok
    {
        $item = null;
        if ($id !== null && $id !== "_new") {
            try {
                    $filter = ['_id' => $id];
                    $query = new MongoDB\Driver\Query($filter, ['limit'=>1]);
                    $rows = $this->db->executeQuery("{$this->db->dbname}.{$form}", $query);
                    $rows = $rows->toArray();
            } catch (Exception $err) {
                try {
                    $filter = ['_id' => $this->init_id($id)];
                    $query = new MongoDB\Driver\Query($filter, ['limit'=>1]);
                    $rows = $this->db->executeQuery("{$this->db->dbname}.{$form}", $query);
                    $rows = $rows->toArray();
                } catch (Exception $err) {
                    $item = null;
                }
            }
            $item = isset($rows[0]) ? $rows[0] : null;
        }
        $item == null ? null : $this->toArray($item);
        return $item;
    }

    public function ItemList($form = 'pages', $options = []) //ok
    {
        $list = array();
        $filter = [];
        $params = [];
        if (isset($options["filter"])) {
            $filter = $options["filter"];
        }
        if (isset($options["size"])) {
            isset($options['page']) ? null : $options['page'] = 1;
            $page = intval($options["page"]);
            $size = intval($options["size"]);
            $params["limit"] = intval($size);
            $params["skip"] = ($page - 1) * $size;
        } else {
            $page = 1;
            if (isset($options["limit"])) {
                $params["limit"] = intval($options["limit"]);
            }
        }

        if (isset($options["projection"])) {
            $params["projection"] = $options["projection"];
        }
        if (isset($options["return"])) {
            if (! ((array)$options["return"] === $options["return"])) {
                $options["return"] = $this->app->ArrayAttr($options["return"]);
            }
            $params["projection"] = [];
            foreach ((array)$options["return"] as $fld) {
                $params["projection"][$fld] = 1;
            }
        }

        if (isset($options['sort'])) {
            $params['sort'] = [];
            foreach ((array)$options['sort'] as $key=> $fld) {
                if (!strpos(':', $key) and is_numeric($fld) and in_array($fld*1, [1,-1])) {
                    $params['sort'][$key] = $fld;
                } elseif (!((array)$fld === $fld)) {
                    $fld = explode(":", $fld);
                    if (!isset($fld[1])) {
                        $fld[1] = 1;
                    } elseif (in_array(strtolower($fld[1]), ['a','asc','1'])) {
                        $fld[1] = 1;
                    } elseif (in_array(strtolower($fld[1]), ['d','desc','-1'])) {
                        $fld[1] = -1;
                    }
                    $params['sort'][$fld[0]] = $fld[1];
                } else {
                    $params['sort'][$key] = $fld;
                }
            }
        }
        $filter = $this->filterPrepare($filter);
        $query = new MongoDB\Driver\Query($filter, $params);
        $rows = $this->db->executeQuery("{$this->db->dbname}.{$form}", $query);
        $find = $rows->toArray();
        $list = [];
        foreach ($find as $doc) {
            $this->toArray($doc);
            if ((object)$doc['_id'] === $doc['_id']) {
                $doc['_id'] = (array)$doc['_id'];
            }
            if ((array)$doc['_id'] === $doc['_id']) {
                $doc['_id'] = $doc['_id']['oid'];
            }
            $doc_id = $doc['_id'];
            $doc = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $doc);
            isset($params["projection"]) && count($params["projection"]) ? $doc = array_intersect_key($doc, $params["projection"]) : null;
            $doc == null ? null : $list[$doc_id] = $doc;
        }
        $count = count($list);
        isset($size) ? null : $size = $count;
        return ["list"=>$list,"count"=>$count,"page"=>$page,"size"=>$size];
    }

    public function filterPrepare($filter) // ok
    {
        $filter = (array)$filter;
        if (in_array('$like', array_keys($filter))) {
            if (isset($filter['$like'])) {
                $filter = ['$regex' => '(?i)'.$filter['$like']];
                return $filter;
            }
        }
        foreach ($filter as $key => $node) {
            (array)$node === $node ? $node = $this->filterPrepare($node) : null;
            $filter[$key] = $node;
        }
        return $filter;
    }

    public function ItemRemove($form = null, $id = null, $flush = true) // ok
    {
        if (!$id OR !$form) {
            return null;
        }
        $app = &$this->app;
        $res = false;
        if ((array)$id ===  $id) {
            foreach ($id as $iid) {
                $res = $app->itemRemove($form, $iid, $flush);
            }
        } else {
            $sid = $id;
            $id = $this->init_id($sid);
            
            ($this->db->bulk == null) ? $bulk = new MongoDB\Driver\BulkWrite(['ordered' => true]) : $bulk = $this->db->bulk['bulk'];

            $bulk->delete(['_id'=>$id]);
            if ($flush) {
                $res = $this->db->executeBulkWrite("{$this->db->dbname}.{$form}", $bulk);
                $this->db->bulk = null;
            } else {
                $this->db->bulk = ['bulk'=>&$bulk,'form'=>$form];

            }
        }
        return $res->nRemoved;
    }

    public function init_id($id) // ok
    {
        try {
            $id = new \MongoDB\BSON\ObjectID($id);
        } catch (Exception $err) {
            $id = $id;
        }
        return $id;
    }

    public function ItemPrepare(&$item) //ok
    {
        if (!((array)$item === $item)) {
            return;
        }
        foreach ($item as $key => &$val) {
            if (is_object($val)) {
                $val = (array)$val;
            }
            if ($key == "_id" && (array)$val === $val && array_keys($val) == ['$oid']) {
                $val = $this->init_id($val['$oid']);
            }
            if ((array)$val === $val) {
                $this->ItemPrepare($val);
            }
        }
    }

    public function ItemSave($form, $item = null, $flush = true) // ok
    {
        $res = null;
        if (!$form) {
            return null;
        }
        $multi = ($flush == true) ? false : true;
        $item = json_decode(json_encode($item), true);
        $this->toArray($item);
        $item = wbItemInit($form, $item);
        $id = $sid = $item["id"];
        $item["_id"] = $this->init_id($sid);
        ($this->db->bulk == null) ? $bulk = new MongoDB\Driver\BulkWrite() : $bulk = $this->db->bulk['bulk'];
        $bulk->update(['_id'=>$item["_id"]],['$set' => $item],['multi'=>true,'upsert'=>true]);
        if ($flush == true) {
            $res = $this->db->executeBulkWrite("{$this->db->dbname}.{$form}", $bulk);
            $this->db->bulk = null;
        } else {
            $this->db->bulk = ['bulk'=>&$bulk,'form'=>$form];
        }
        return $item;
    }

    public function TableFlush($form)
    {
        $res = false;
        // Сброс кэша в общий файл
        if ($this->db->bulk !== null) {
            $bulk = &$this->db->bulk['bulk'];
            $form = &$this->db->bulk['form'];
            try {
                $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
                $result = $this->db->executeBulkWrite("{$this->db->dbname}.{$form}", $bulk, $writeConcern);
                $res = true;
            } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
                $result = $e->getWriteResult();
            }
            $this->bulk = null;
        }
        return $res;
    }

    public function tableExist($form, $engine = false) // ok
    {
        $res = false;
        if (!$this->db->dbname) {
            return false;
        }
        if (!isset($this->db->tables)) {
            $this->db->tables = [];

            $command = new MongoDB\Driver\Command(["listCollections" => 1]);
            $cursor = $this->db->executeCommand($this->db->dbname, $command);
            $collections = $cursor->toArray();
            foreach ($collections as $collectionInfo) {
                $this->db->tables[] = $collectionInfo->name;
            }

        }
        return in_array($form,$this->db->tables);
    }

    public function TableCreate($form, $engine = false)
    {
        if ($form == "_settings") {
            return false;
        }
        if ($engine) {
            return jsonTableCreate($form, $engine);
        }
        if ($form) {
            $this->db->createCollection($form);
        } else {
            wbError('func', __FUNCTION__, 1002, func_get_args());
        }
    }

    public function TableRemove($form, $engine)
    {
        if ($form == "_settings") {
            return false;
        }
        if (wbRole('admin')) {
            $form = $this->db->dropCollection($form);
            /*
                        if (!$form) { // не удалилось
                            wbError('func', __FUNCTION__, 1003, func_get_args());
                        }
                        if (!$form) { // не существует
                            wbError('func', __FUNCTION__, 1001, func_get_args());
                        }
            */
        }
        return $res;
    }

    public function TableList($engine = false) // ok
    {
        if ($engine) {
            return jsonTableList(true);
        }
        $list = [];
        $command = new MongoDB\Driver\Command(["listCollections" => 1]);
        $cursor = $this->db->executeCommand($this->db->dbname, $command);
        $collections = $cursor->toArray();
        foreach ($collections as $collectionInfo) {
            $list[] =  $collectionInfo->name;
        }
        return $list;
    }
}
