<?php

function wbSetDb($form) {
    $app = &$_ENV["app"];
    if (isset($app->settings->driver_tables[$form])) {
          $driver = $app->settings->driver_tables[$form];
    } else {
          $driver = $app->settings->driver;
    }
    $path = "/drivers/{$driver}/init.php";
    if (is_file($app->route->path_app . $path)) {
        include_once $app->route->path_app . $path;
    } elseif (is_file($app->route->path_engine.$path)) {
        include_once $app->route->path_engine.$path;
    }
    $class = $driver."Drv";
    $app->db = new $class($app);
    $app->_db = new jsonDrv($app);
    return $app->db;
}

function wbItemRead($form = null, $id = null)
{
    if ($form == null OR $id == null) return null;
    wbTrigger('form', __FUNCTION__, 'beforeItemRead', func_get_args(), array());
    $db = wbSetDb($form);
    if ($form == "_settings") $db = $_ENV["app"]->_db;
    $item = $db->itemRead($form, $id);
    if (null !== $item) {
        if (isset($item['_removed']) && 'remove' == $item['_removed']) {
            $item = null;
            $item = wbTrigger('form', __FUNCTION__, 'emptyItemRead', func_get_args(), $item);
        } // если стоит флаг удаления, то возвращаем null
        else {
          $item["_form"] = $item["_table"] = $form;
          if (isset($item['images']) && $_ENV["route"]["mode"]!=="edit") {
              $item = wbImagesToText($item);
          }
        }
        $item = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $item);
    } else {
        $item = wbTrigger('form', __FUNCTION__, 'emptyItemRead', func_get_args(), $item);
    }
    return $item;
}

function wbItemList($form = 'pages', $options=[])
{
    $db = wbSetDb($form);
    if ($form == "_settings") $db = $_ENV["app"]->_db;
    ini_set('max_execution_time', 900);
    ini_set('memory_limit', '1024M');
    $list = $db->ItemList($form, $options);
    foreach ($list["list"] as &$item) {
      $item = wbTrigger('form', __FUNCTION__, 'afterItemRead', func_get_args(), $item);
    }
    return $list;
}

function wbItemRemove($form = null, $id = null, $flush = true)
{
    $db = wbSetDb($form);
    if ($form == "_settings") $db = $_ENV["app"]->_db;
    $res = $db->itemRemove($form, $id, $flush);
    if ($res !== false) $res["_removed"] = true;
    wbTrigger('form', __FUNCTION__, 'afterItemRemove', func_get_args(), $res);
    return $res;
}

function wbItemRename($form = null, $old = null, $new = null, $flush = true)
{
    if ($new == null or $new == "") $new = wbNewId();
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

function wbItemSave($table, $item = null, $flush = true)
{
    $item = wbItemInit($table, $item);
    $item = wbTrigger('form', __FUNCTION__, 'beforeItemSave', func_get_args(), $item);
    $db = wbSetDb($form);
    if ($table == "_settings") $db = $_ENV["app"]->_db;
    $item = $db->itemSave($table, $item, $flush);
    $item = wbTrigger('form', __FUNCTION__, 'afterItemSave', func_get_args(), $item);
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
    $drv=wbCallDriver(__FUNCTION__, func_get_args());

    if ($drv !== false) {
        $res = $drv["result"];
    } else {
        $res = jsonTableFlush($form);
    }
    wbTrigger('form', __FUNCTION__, 'afterTableFlush', func_get_args(), $form);
    return $res;
}




function wbTableCreate($form = 'pages', $engine = false)
{
    $drv=wbCallDriver(__FUNCTION__, func_get_args());
    if ($drv !== false) {
        $form = $drv["result"];
    } else {
        $form = jsonTableCreate($form, $engine);
    }
    return $form;
}

function wbTableRemove($form = null, $engine = false)
{
    $res = false;
    $drv=wbCallDriver(__FUNCTION__, func_get_args());
    if ($drv !== false) {
        $form = $drv["result"];
    } else {
        $form = jsonTableRemove($form, $engine);
    }
    return $res;
}

function wbTableExist($form)
{
    if (is_file($_ENV['dba'].'/'.$form.'.json')) {
        return true;
    }
    return false;
}


function wbTableList($engine = false)
{
    $drv=wbCallDriver(__FUNCTION__, func_get_args());
    if ($drv !== false) {
        $list = $drv["result"];
    } else {
        $list = jsonTableList($engine);
    }
    return $list;
}
