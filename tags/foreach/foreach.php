<?php

use Nahid\JsonQ\Jsonq;

class tagForeach
{
    public function __construct($dom)
    {
        return $this->foreach($dom);
    }

    function foreach($dom)
    {
        $app = &$dom->app;
        if ($dom->is(":root")) {
            $dom->rootError();
        }
        $idx = 0;
        $ndx = 1;
        $page = $pages = 1;
        $srvpag = false;
        if (!isset($dom->role)) {
            return $dom;
        }
        if (!$dom->app) {
            $dom->app = new wbApp();
        }

        $empty = $dom->find("wb-empty")[0];
        $dom->find("wb-empty")->remove();
        $tpl = $dom->html();
        $dom->html("");

        $dom->attr("id") > "" ? $tid = $dom->attr("id") : $tid = "fe_" . $dom->app->newId();
        $list = $parent = $dom->item;
        $options = [];
        $dom->params("table") > "" ? $table = $dom->params->table : $table = "";
        isset($dom->params->field) ? $field = $dom->params->field : $field = null;
        if ($dom->params("orm") > "") {
            $options["orm"] = $dom->params->orm;
        }
        if ($dom->params("item") > "") {
            $options["item"] = $dom->params->item;
        }
        if ($dom->params("filter") > "") {
            $options["filter"] = $dom->params->filter;
        }
        if ($dom->params("limit") > "") {
            $options["limit"] = $dom->params->limit;
        }
        if ($dom->params("where") > "") {
            $options["where"] = $dom->params->where;
        }
        if ($dom->params("render") == "client" && $dom->params("table") > "") {
            $dom->attr("data-ajax", '{"url":"/ajax/' . $dom->params("table") . '/list/"}');
            unset($table);
        }
        if ($dom->params("return") > "") {
            $options["return"] = $app->attrToArray($dom->params("return"));
        }
        if ($dom->params("sort") > "") {
            $options["sort"] = $app->attrToArray($dom->params("sort"));
        }
        $dom->options = $options;
        if ($table > "" and $dom->params("call") == "") {
            $res = wbItemList($table, $options);
            $list = $res["list"];
            $count = $res["count"];
        } else if ($table > "" and $dom->params("call") > "") {
            $list = [];
            $formClass = $app->formClass($table);
            $method = $dom->params("call");
            if (method_exists($formClass, $method)) {
                $list = $formClass->$method($dom);
            }
            $count = count($list);
        } else if ($table == "" and $dom->params("call") > "") {
            $list = (array) wbEval($dom->params("call"));
        }

        if ($dom->params('ajax')) {
            $ajax = $dom->params('ajax');
            $url = parse_url($ajax);
            if (!isset($url['scheme'])) {
                if ($app->vars('_sett.api_key_query') == 'on' AND !isset($url['__apikey'])) {
                    strpos($ajax,'?') ? $ajax .= '&' : $ajax .= '?';
                    $ajax .= '__apikey='.$app->vars('_sett.api_key');
                }
                $ajax = $app->vars('_route.host').$ajax;
            }

            $list = json_decode(str_replace("'", '"', wbAuthGetContents($ajax)), true);
            $count = count($list);
        }

        if ($dom->params('json')) {
            $list['json'] = json_decode(str_replace("'",'"',$dom->params("json")),true);
            $dom->params->from = 'json';
        }

        if ($dom->params('from')) {
            if (isset($list[$dom->params->from])) {
                $list = $list[$dom->params->from];
            } else {
                $list = $dom->getField($dom->params->from);
            }
            if (isset($options["sort"]) and (array) $options["sort"] === $options["sort"]) {
                foreach ((array) $options["sort"] as $key => $fld) {
                    if (!((array) $fld === $fld)) {
                        $fld = explode(":", $fld);
                        if (!isset($fld[1])) {
                            $fld[1] = 1;
                        } else if (in_array(strtolower($fld[1]), ['a', 'asc', '1'])) {
                            $fld[1] = '';
                        } else if (in_array(strtolower($fld[1]), ['d', 'desc', '-1'])) {
                            $fld[1] = 'desc';
                        }
                        $params['sort'][$fld[0]] = $fld[1];
                    } else {
                        $params['sort'][$key] = $fld;
                    }
                }
                $json = new Jsonq();
                $json = $json->collect($list);
                if (count($params['sort'])) {
                    foreach ($params['sort'] as $fld => $order) {
                        $json->sortBy($fld, $order);
                    }
                }
                $list = $json->get();
            }
        }

        if ($dom->params("size") > "") {
            $dom->params("page") ? $page = $dom->params->page : $page = 1;
            if ($dom->parent()->attr('id') == '') {
                $dom->parent()->attr('id', 'fe_' . md5($dom->outer()));
            }
            if ($app->vars('_post._route') and $app->vars('_post._params') and $app->vars('_post._tid') == '#' . $dom->parent()->attr('id')) {
                $page = $app->vars('_post._params.page');
                $srvpag = true;
            }
            $list = array_chunk($list, $dom->params->size);
            $dom->params->pages = $pages = ceil($count / $dom->params->size);
            if ($page > $pages or $page <= 0) {
                $list = [];
            }
            if ($pages >= 1 && isset($list[$page - 1])) {
                $list = $list[$page - 1];
            }
            $ndx = ($page - 1) * $dom->params("size") + 1;
        }
        if ($dom->params("count") > "") {
            $item = $list;
            $list = [];
            $count = intval($dom->params->count);
            //            if (count($item)) {
            //                for ( $i=0; $i<$count; $i++ ) {
            //                    foreach($item as $line) $list[] = $line;
            //                }
            //            } else {
            for ($i = 0; $i < $count; $i++) {
                $list[] = ["_id" => $i];
            }
            //            }
        }
        if ($dom->params("rand") == "true") {
            shuffle($list);
        }
        $dom->attr("data-ajax") == "" ? $render = false : $render = true;
        if (!$render) {
            $tpl = "<wb>{$tpl}</wb>";
        }

        foreach ((array) $list as $key => $val) {
            $value = $val;
            $val = (object) $val;
            $val->_idx = $idx;
            $val->_ndx = $ndx;
            $val->_page = $page;
            $val->_pages = $pages;
            $val->_val = $value;
            $val->_parent = &$parent;
            if (!isset($val->_id)) {
                isset($val->id) ? $val->_id = $val->id : $val->_id = $idx;
            }
            if ($table > "") {
                $val = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$table], (array) $val);
            }
            if ($render > "") {
                $list[$key] = (array) $val;
            } else {
                $line = $dom->app->fromString($tpl);
                $line->copy($dom);
                $line->item = (array) $val;
                $line->fetch();
                $dom->append($line->inner());
            }
            $idx++;
            $ndx++;
        }

        if ($render == "client") {
            $dom->append("<template id = \"{$tid}\" data-ajax=\"" . $dom->attr("data-ajax") . "\">\n{{#each result}}\n" . $tpl . "\n{{/each}}</template>\n");
            $dom->find("template[id=\"{$tid}\"] .pagination")->attr("data-tpl", $tid);
        } else if ($dom->params("size") > "") {
            $size = $dom->params("size");
            $dom->parent()->attr(
                "data-pagination",
                json_encode(
                    [
                        'count' => $count,
                        'pages' => $pages,
                        'page' => $page,
                        'size' => $size,
                    ]
                )
            );
            $dom->parent()->attr("data-pages", $pages);
            $dom->parent()->attr("data-page", $pages);
            $dom->params->count = $count;
            $dom->params->tpl = $dom->parent()->attr('id');
            $dom->params->page = $page;
            $pag = $dom->tagPagination($dom);
            $html = $dom->html();
            if (!$dom->params('pos')) {
                $dom->params->pos = 'bottom';
            }
            if ($dom->params->pos == 'bottom') {
                $html .= "\n" . $pag->outer();
            }
            if ($dom->params->pos == 'top') {
                $html = $pag->outer() . "\n" . $html;
            }
            if ($dom->params->pos == 'both') {
                $html = $pag->outer() . "\n" . $html . "\n" . $pag->outer();
            }

            if ($srvpag) {
                $res = [
                    'html' => $html,
                    'route' => $app->route,
                    'params' => $dom->params,
                ];
                header('Content-Type: charset=utf-8');
                header('Content-Type: application/json');
                echo json_encode($res);
                die;
            }
        }
        if (!count((array) $list) or $dom->html() == "") {
            $dom->inner($empty->inner());
        }

        if ($dom->parent()->is("select[placeholder]") && !$dom->find("option[value='']")->length) {
            $dom->prepend("<option value=''>".$dom->parent()->attr('placeholder')."</option>");
        }


        $dom->before($dom->html());
        $dom->remove();
    }
}
