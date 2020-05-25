<?php

require __DIR__ . '/vendor/autoload.php';

function mongoItemRead($connect, $table, $id)
{
    $res = false;
    if (is_string($connect)) $connect = new MongoDB\Driver\Manager($connect);
    $query = new MongoDB\Driver\Query(['_id' => $id], []);
    $rows = $connect->executeQuery($table, $query);
    foreach ($rows as $row) {
        $res = (array)$row;
        break;
    }
    return $res;
}

function mongoItemSave($connect, $table, $item)
{
    $res = false;
    if (!isset($item["_id"]) AND isset($item["id"])) $item["_id"] = $item["id"];
    if (!isset($item["_id"]) OR $item["_id"] == "") return $res;
    if (is_string($connect)) $connect = new MongoDB\Driver\Manager($connect);
    $prev = mongoItemRead($connect, $table, $item["_id"]);
    if (!$prev) {
        $prev = ["_id" => $item["_id"]];
    }
    $bulk = new MongoDB\Driver\BulkWrite;
    if (!isset($item["_created"])) $item["_created"] = date("Y-m-d H:i:s");
    $item["_updated"] = date("Y-m-d H:i:s");
    $item = array_merge($prev, $item);
    ksort($item);
    $option = array("upsert" => true);
    $bulk->update(["_id" => $item["_id"]], $item, $option);
    $res = $connect->executeBulkWrite($table, $bulk);
    return $res;
}

function mongoItemRemove($connect, $table, $id)
{
    $res = false;
    if (is_string($connect)) $connect = new MongoDB\Driver\Manager($connect);
    $bulk = new MongoDB\Driver\BulkWrite;
    if (is_string($id)) {
        $bulk->delete(['_id' => $id], ['limit' => 1]);
    } else if (is_array($id)) {
        $bulk->delete($id);
    }
    $res = $connect->executeBulkWrite($table, $bulk);
    return $res;
}

function mongoItemList($connect, $table, $filter = [], $options = [])
{
    $res = false;
    if (is_string($connect)) $connect = new MongoDB\Driver\Manager($connect);
    $query = new MongoDB\Driver\Query($filter, $options);
    $rows = $connect->executeQuery($table, $query);
    $arr = [];
    foreach ($rows as $row) {
        $row = (array)$row;
        $arr[$row["_id"]->__toString()] = $row;
    }
    if (count($arr)) $res = $arr;
    return $res;
}

function mongoItemListSQLSelect($connect, $sql)
{
    $parser = new PHPSQLParser\PHPSQLParser($sql, true);

    function swap3(&$x, &$y)
    {
        $tmp = $x;
        $x = $y;
        $y = $tmp;
    }

    function equotion($l, $o, $r)
    {
        if (isset($r["expr_type"]) && $r["expr_type"] == "colref" && isset($l["mongo"]))
            swap3($r, $l);

        if (isset($l["expr_type"]) && $l["expr_type"] == "colref" && isset($r["mongo"]))
            return ["mongo" => [
                $l["base_expr"] => [$o => $r["mongo"]]
            ]];

        if (isset($l["mongo"]) && isset($r["mongo"])) {
            return ["mongo" => [$o => [$l["mongo"], $r["mongo"]]]];
        }

        throw new Exception("Something unsupported in equation");
    }

    function expression_parser($q)
    {
        // В соответствии с приоритетом - объединяем лево и право через оператор

        $priority = [
            0 => [
                "=" => '$eq',
                ">" => '$gt',
                "<" => '$lt',
                ">=" => '$gte',
                "<=" => '$lte',
                "<>" => '$ne',
                "!=" => '$ne',
                "!>" => '$lte',
                "!<" => '$gte'
            ],
            1 => ["NOT" => '$not'],
            2 => ["AND" => '$and'],
            3 => [
                "IN" => '$in',
                "OR" => '$or'
            ]
        ];
        // Но сперва скобки
        for ($i = 0; $i < count($q); ++$i) {
            if (isset($q[$i]["expr_type"])) {
                if ($q[$i]["expr_type"] == "bracket_expression")
                    $q[$i] = expression_parser($q[$i]["sub_tree"]);
            }
        }

        // Затем константы
        for ($i = 0; $i < count($q); ++$i) {
            if (isset($q[$i]["expr_type"])) {
                if ($q[$i]["expr_type"] == "const")
                    $q[$i] = ["mongo" => $q[$i]["base_expr"]];
            }
        }
        // Ну а теперь операторы. Тут скобок уже нет, по этому - берём три подряд элемента и применяем оператор. Унарные и тернарные операторы не поддерживаются!
        for ($p = 0; $p < 4; ++$p) {
            $i = 0;
            while ($i < count($q)) {
                if (isset($q[$i]["expr_type"])) {
                    if ($q[$i]["expr_type"] == "operator") {
                    	$op = strtoupper($q[$i]["base_expr"]);
                        if (array_key_exists($op, $priority[$p])) {
                            $eq = equotion($q[$i - 1], $priority[$p][$op], $q[$i + 1]);
                            array_splice($q, $i - 1, 3, [$eq]);
                            $i = 0;
                            continue;
                        }
                    }
                }
                ++$i;
            }
        }

        assert(count($q) == 1);

        return $q[0];
    }

    $base_name = $parser->parsed["FROM"][0]["base_expr"];
    // $fields = array_combine(array_map(function ($el) { return $el["base_expr"]; }, $parser->parsed["SELECT"]), array_map(function ($el) { return 1;	}, $parser->parsed["SELECT"]));
    $filter = isset($parser->parsed["WHERE"]) ? expression_parser($parser->parsed["WHERE"])["mongo"] : [];

    return mongoItemList($connect, $base_name, $filter);
}
