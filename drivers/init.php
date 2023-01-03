<?php

function wbItemList($form = 'pages', $options=[])
{
    !is_array($options) ? $options = json_decode($options, true) : null;
    $db = wbSetDb($form);
    if (!$db) {
        return [
            'error' => true,
            'msg'=> 'Database not found',
            'list' => []
        ];
    }
    ini_set('max_execution_time', 900);
    ini_set('memory_limit', '1024M');
    $res = $db->ItemList($form, $options);
    if (isset($options['chunk']) && $options['chunk'] > 0) {
        $res['list'] = array_chunk($res['list'], intval($options['chunk']));
        $res['size'] = intval($options['chunk']);
        $res['pages'] = count($res['list']);
    }
    return $res;
}

function wbSetDb($form)
{
    $app = &$_ENV["app"];
    isset($app->drivers) ? null : $app->drivers = (object)[];
    isset($app->settings->driver_tables[$form]) ? $driver = $app->settings->driver_tables[$form] : $driver = $app->settings->_driver;
    $form == '_settings' ? $driver = 'json' : null;
    $path = "/drivers/{$driver}/init.php";
    if (is_file($app->route->path_app . $path)) {
        include_once $app->route->path_app . $path;
    } elseif (is_file($app->route->path_engine.$path)) {
        include_once $app->route->path_engine.$path;
    }
    $class = $driver.'Drv';
    $app->db = new $class($app);
    $app->_db = new jsonDrv($app);

    (substr($form, 0, 1) == '_') ? $app->drivers->$form = &$app->_db : $app->drivers->$form = &$app->db;
    $loop=false;
    foreach (debug_backtrace() as $func) {
        'wbTableCreate'==$func["function"] ? $loop=true : null;
    }
    $exists = $app->drivers->$form->tableExist($form);
    if (!$exists && !$loop && $app->vars('_sess.user.role')=='admin') {
        if (in_array($form,$app->listForms()) OR $app->vars('_route.mode') == 'save') {
            if (!$app->tableCreate($form)) {
                $app->_db->tableCreate($form);
                 $app->drivers->$form = &$app->_db;
            }
            $exists = $app->drivers->$form->tableExist($form);
        }
    }

    if (!$exists) {
        //echo json_encode(["error"=>true,"msg"=>"Fatal error! {$form} not found !"]);
        error_log("Fatal error! Form {$form} not found !"." [{$_SERVER['REQUEST_URI']} from {$_SERVER['REMOTE_ADDR']}]");
        $app->drivers->$form = null;
    } else {
        $file = $app->vars('_env.dba')."/{$form}.json";
        is_file($file) ? null : $app->putContents($file, '');
    }

    return $app->drivers->$form;
}

function wbTreeRead($name)
{
    wbTrigger('form', __FUNCTION__, 'BeforeTreeRead', func_get_args(), array());
    $tree = wbItemRead('catalogs', $name);
    $tree = wbTrigger('form', __FUNCTION__, 'AfterTreeRead', func_get_args(), $tree);
    return $tree;
}

function wbItemRead($form = null, $id = null)
{
    if ($form == null or $id == null) {
        return null;
    }
    $db = wbSetDb($form);
    if ($db == null) return null;
    wbTrigger('form', __FUNCTION__, 'beforeItemRead', func_get_args(), array());
    !isset($_SESSION['lang']) ? $_SESSION['lang'] = 'en' : null;
    $cid = md5($form . $id . $_SESSION['lang']);
    if (isset($_ENV['cache'][$cid])) {
        $item = $_ENV['cache'][$cid];
    } else {
        $item = $db->itemRead($form, $id);
        if (null !== $item) {
            if (isset($item['_removed']) && 'remove' == $item['_removed']) {
                $item = null;
                $item = wbTrigger('form', __FUNCTION__, 'emptyItemRead', func_get_args(), $item);
            // если стоит флаг удаления, то возвращаем null
            } else {
                $item["_form"] = $item["_table"] = $form;
            }
        } else {
            $item = wbTrigger('form', __FUNCTION__, 'emptyItemRead', func_get_args(), $item);
        }
        $item = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $item);
    }
    $_ENV['cache'][$cid] = $item;
    if (isset($item['__token'])) unset($item['__token']);
    return $item;
}

function wbItemRemove($form = null, $id = null, $flush = true)
{
    $res = false;
    $db = wbSetDb($form);
    $item = $db->itemRead($form, $id);
    $item ? null : $item = ['id'=>$id, '_id' => $id, '_form' => $form, '_table' => $form];
    wbTrigger('form', __FUNCTION__, 'beforeItemRemove', func_get_args(), $item);
    $res = $db->itemRemove($item['_form'], $item['_id'], $flush);
    $res !== false ? $res["_removed"] = true : null;
    wbTrigger('form', __FUNCTION__, 'afterItemRemove', func_get_args(), $res);
    return $res;
}

function wbItemRename($form = null, $old = null, $new = null, $flush = true)
{
    if ($new == null or $new == "") {
        $new = wbNewId();
    }
    $item = wbItemRead($form, $old);
    if ($item) {
        $item=wbTrigger('form', __FUNCTION__, 'beforeItemRename', func_get_args(), $item);
        $item["id"] = $new;
        $item["_removed"] = false;
        wbItemSave($form, $item, $flush);
        $path = "{$_ENV["path_app"]}/uploads/{$form}";
        if (is_dir("{$path}/{$old}")) {
            rename("{$path}/{$old}", "{$path}/{$new}");
        }
        wbItemRemove($form, $old, $flush);
        $item=wbTrigger('form', __FUNCTION__, 'afterItemRename', func_get_args(), $item);
        return $item;
    }
    return false;
}


function wbItemCopy($form = null, $old = null, $new = null, $flush = true)
{
    if ($new == null or $new == "") {
        $new = wbNewId();
    }
    $item = wbItemRead($form, $old);
    if ($item) {
        $item=wbTrigger('form', __FUNCTION__, 'beforeItemCopy', func_get_args(), $item);
        $item["id"] = $item["_id"] = $new;
        $item = wbItemSave($form, $item, $flush);
        if ($item) {
        //    $path = "{$_ENV["path_app"]}/uploads/{$form}";
        //    if (is_dir("{$path}/{$old}")) {
        //        rename("{$path}/{$old}", "{$path}/{$new}");
        }
        $item=wbTrigger('form', __FUNCTION__, 'afterItemCopy', func_get_args(), $item);
        return $item;
    }
    return false;
}


function wbItemSave($form, $item = null, $flush = true)
{
    $db = wbSetDb($form);
    $item = wbTrigger('form', __FUNCTION__, 'beforeItemSave', func_get_args(), $item);
    if (!isset($item['id'])) {
        if (isset($item['_id']) && $item['_id'] > '') {
            $item['id'] = $item['_id']; 
        } else if ($_ENV['app']->vars('_route.mode') == 'save' AND $_ENV['app']->vars('_route.item') > '') {
            $item['id'] = $_ENV['app']->vars('_route.item');
        }
    } 
    if ($item) {
        // читаем всю запись, иначе возвращаются не все поля
        isset($item["id"]) ? $src = $db->itemRead($form, $item["id"]) : $src = null;
        $src ? $item = array_merge($src, $item) : null;
    }
    $item = wbItemInit($form, $item);
    $item = wbDotFix($item);
    if (isset($item['__token'])) unset($item['__token']);
    try {
        $item = $db->itemSave($form, $item, $flush);
        $item = wbTrigger('form', __FUNCTION__, 'afterItemSave', func_get_args(), $item);
    } catch (\Throwable $th) {
        $item = null;
    }
    
    return $item;
}

function wbFlushDatabase()
{
    $etables = wbTableList(true);
    $atables = wbTableList();
    foreach ($etables as $key) {
        wbTableFlush($key);
    }
    foreach ($atables as $key) {
        wbTableFlush($key);
    }
}

function wbTableFlush($form)
{
    // Сброс кэша в общий файл
    $res = false;
    wbTrigger('form', __FUNCTION__, 'beforeTableFlush', func_get_args(), $form);
    $db = wbSetDb($form);
    $res = $db->tableFlush($form);
    wbTrigger('form', __FUNCTION__, 'afterTableFlush', func_get_args(), $form);
    return $res;
}




function wbTableCreate($form = 'pages', $engine = false)
{
    $app = &$_ENV['app'];
    $db = wbSetDb($form);
    wbTrigger('form', __FUNCTION__, 'beforeTableCreate', func_get_args(), array());
    $db ? $res = $db->tableCreate($form, $engine) : null;
    $file = $app->vars('_env.dba')."/{$form}.json";
    is_file($file) ? null : $app->putContents($file,'');
    return $res;
}

function wbTableRemove($form = null, $engine = false)
{
    $res = false;
    $db = wbSetDb($form);
    $res = $db->tableRemove($form, $engine);
    return $res;
}

function wbTableExist($form)
{
    if (is_file($_ENV['dba'].'/'.$form.'.json')) {
        return true;
    }
    return false;
}


function wbListTables($engine = false) {
    return wbTableList($engine);
}

function wbTableList($engine = false)
{
    $app = &$_ENV['app'];
    $list = glob($_ENV['path_app'].'/database/*.json');
    $res = [];
    foreach($list as $file) {
        $res[] = substr(basename($file),0,-5);
    }
    return $res;
}
