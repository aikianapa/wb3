<?php

function wbItemRead($form = null, $id = null)
{
    if ($form == null OR $id == null) return null;
    wbTrigger('form', __FUNCTION__, 'BeforeItemRead', func_get_args(), array());
    $db = $_ENV["app"]->db;
    if ($form == "_settings") $db = $_ENV["app"]->_db;
    $item = $db->itemRead($form, $id);
    if (null !== $item) {
        if (isset($item['_removed']) && 'remove' == $item['_removed']) {
            $item = null;
            $item = wbTrigger('form', __FUNCTION__, 'EmptyItemRead', func_get_args(), $item);
        } // если стоит флаг удаления, то возвращаем null
        else {
          $item["_form"] = $item["_table"] = $form;
          if (isset($item['images']) && $_ENV["route"]["mode"]!=="edit") {
              $item = wbImagesToText($item);
          }
        }
        $item = wbTrigger('form', __FUNCTION__, 'AfterItemRead', func_get_args(), $item);
    } else {
        $item = wbTrigger('form', __FUNCTION__, 'EmptyItemRead', func_get_args(), $item);
    }
    return $item;
}

function wbItemList($form = 'pages', $options=[])
{
    $db = $_ENV["app"]->db;
    if ($form == "_settings") $db = $_ENV["app"]->_db;
    ini_set('max_execution_time', 900);
    ini_set('memory_limit', '1024M');
    $list = $db->ItemList($form, $options);
    return $list;
}

function wbItemRemove($form = null, $id = null, $flush = true)
{
    $db = $_ENV["app"]->db;
    if ($form == "_settings") $db = $_ENV["app"]->_db;
    $res = $db->itemRemove($form, $id, $flush);
    if ($res !== false) $res["_removed"] = true;
    wbTrigger('form', __FUNCTION__, 'AfterItemRemove', func_get_args(), $res);
    return $res;
}

function wbItemRename($form = null, $old = null, $new = null, $flush = true)
{
    if ($new == null or $new == "") $new = wbNewId();
    $item = wbItemRead($form, $old);
    if ($item) {
        $item=wbTrigger('form', __FUNCTION__, 'BeforeItemRename', func_get_args(), $item);
        $item["id"] = $new;
        $item["_removed"] = false;
        wbItemSave($form, $item, $flush);
        $path = "{$_ENV["path_app"]}/uploads/{$form}";
        if (is_dir("{$path}/{$old}")) {
            rename("{$path}/{$old}", "{$path}/{$new}");
        }
        wbItemRemove($form, $old, $flush);
        $item=wbTrigger('form', __FUNCTION__, 'AfterItemRename', func_get_args(), $item);
        return $item;
    }
    return false;
}

function wbItemSave($table, $item = null, $flush = true)
{
    $item = wbItemInit($table, $item);
    $item = wbTrigger('form', __FUNCTION__, 'BeforeItemSave', func_get_args(), $item);
    $db = $_ENV["app"]->db;
    if ($table == "_settings") $db = $_ENV["app"]->_db;
    $item = $db->itemSave($table, $item, $flush);
    return $item;
}

function wbFlushDatabase()
{
    wbTrigger('func', __FUNCTION__, 'before');
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
    wbTrigger('form', __FUNCTION__, 'BeforeTableFlush', func_get_args(), $form);
    $drv=wbCallDriver(__FUNCTION__, func_get_args());

    if ($drv !== false) {
        $res = $drv["result"];
    } else {
        $res = jsonTableFlush($form);
    }
    wbTrigger('form', __FUNCTION__, 'AfterTableFlush', func_get_args(), $form);
    return $res;
}




function wbTableCreate($form = 'pages', $engine = false)
{
    wbTrigger('func', __FUNCTION__, 'before');
    $drv=wbCallDriver(__FUNCTION__, func_get_args());
    if ($drv !== false) {
        $form = $drv["result"];
    } else {
        $form = jsonTableCreate($form, $engine);
    }

    wbTrigger('func', __FUNCTION__, 'after', func_get_args(), $form);

    return $form;
}

function wbTableRemove($form = null, $engine = false)
{
    wbTrigger('func', __FUNCTION__, 'before');
    $res = false;
    $drv=wbCallDriver(__FUNCTION__, func_get_args());
    if ($drv !== false) {
        $form = $drv["result"];
    } else {
        $form = jsonTableRemove($form, $engine);
    }
    wbTrigger('func', __FUNCTION__, 'after', func_get_args(), $form);
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
    wbTrigger('func', __FUNCTION__, 'before');
    $drv=wbCallDriver(__FUNCTION__, func_get_args());
    if ($drv !== false) {
        $list = $drv["result"];
    } else {
        $list = jsonTableList($engine);
    }
    wbTrigger('func', __FUNCTION__, 'after', func_get_args(), $list);
    return $list;
}
