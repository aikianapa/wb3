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
        if (!isset($dom->role)) {
            return $dom;
        }
        $dom->is(":root") ? $dom->rootError() : null;

        $app = &$dom->app;
        $this->app = &$app;
        $dom->parent()->attr("id") > "" ? $pid = $dom->parent()->attr("id") : $pid = "fp_" . $dom->app->newId();
        $dom->parent()->attr("id". $pid);

        $idx = 0;
        $ndx = 1;
        $page = $pages = 1;
        $srvpag = false;
        !$dom->app ? $dom->app = new wbApp() : null;
        $empty = $dom->find("wb-empty")[0];
        $dom->find("wb-empty")->remove();
        if ($dom->parent()->is("select[placeholder]")) {
            $this->opt = $dom->find('option', 0)->clone();
            $this->placeholder = $dom->parent()->attr("placeholder");
        }

        $dom->params('tpl') == 'true' ? $dom->addTpl() : null;
        $tpl = $dom->html();
        $dom->html("");
        $dom->parent()->attr("id") > "" ? $tid = $dom->parent()->attr("id") : $tid = "fe_" . $dom->app->newId();
        $list = $parent = $dom->item;
        $options = [];
        $dom->params("form") > "" ? $dom->params->table = $dom->params->form : null;
        $dom->params("table") > "" ? $table = $dom->params->table : $table = "";
        isset($dom->params->field) ? $field = $dom->params->field : $field = null;
       
        $dom->filterStrict();

        $options['filter'] = [];

        if ($app->vars('_post.filter') > '' && $app->vars('_post.target') == '#'.$pid) {
            $this->filter_prepare();
            $options["filter"] = $app->vars('_post.filter');
        }

        if ($dom->params("orm") > "") {
            $options["orm"] = $dom->params->orm;
        }
        if ($dom->params("item") > "") {
            $options["item"] = $dom->params->item;
        }
        if ($dom->params("filter") > "") {
            $options["filter"] = array_merge($dom->params->filter, $options["filter"]);
        }

        if ($dom->params("limit") > "") {
            $options["limit"] = $dom->params->limit;
        }
        if ($dom->params("where") > "") {
            $options["where"] = $dom->params->where;
        }
        if ($dom->params("render") == "client" && $dom->params("table") > "") {
            $dom->params->ajax = '/api/query/' . $dom->params("table") . '/';
            $dom->attr("data-ajax", '{"url":"' . $dom->params("ajax") . '"}');
            $table = null;
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
            !$list ? $list = [] : null;
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

        if ($list && $dom->params("size") > "") {
            $count = count($list);
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
            for ($i = 1; $i <= $count; $i++) {
                $list[] = ["_id" => $i,"_value" => $i, "id" => $i];
            }
        }
        if ($dom->params("rand") == "true") {
            shuffle($list);
        }
        $dom->attr("data-ajax") == "" ? $render = false : $render = true;
        $dom->params("render") > "" ? $render = $dom->params("render") : null;

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
            $params = $dom->params;
            $params->target = '#'.$tid;
            $params = json_encode($params);
            
            $dom->params("from") > "" ? $from = $dom->params->from : $from = 'result';

            $dom->append("<template id = \"{$tid}\" >\n{{#each {$from}}}\n" . $tpl . "\n{{/each}}</template>\n");
            $dom->attr("data-ajax") > '' ? $dom->find("template[id='{$tid}']")->attr('data-ajax', $dom->attr("data-ajax")) : null;
            $params > '' ? $dom->find("template[id='{$tid}']")->attr('data-params', $params) : null;

            $dom->find("template[id=\"{$tid}\"] .pagination")->attr("data-tpl", $tid);
        } else if ($dom->params("size") > "") {
            $size = $dom->params("size");
            !isset($count) ? $count = null : null;
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
            if (!count((array)$list) or $dom->html() == "") {
                $dom->inner($empty->inner());
            }

            $html = $dom->html();
            
            isset($dom->params->pos) ? $pos = $dom->params->pos : $pos = 'bottom';

            if ($srvpag OR ($app->route->controller == 'ajax' AND $app->vars('_post._params') > "")) {
                // При вызове из data-ajax требуется второе условие
                $res = [
                    'html' => $html,
                    'route' => $app->route,
                    'params' => $dom->params,
                    'pag' => $pag->outer(),
                    'pos' => $pos
                ];
                header('Content-Type: charset=utf-8');
                header('Content-Type: application/json');
                echo json_encode($res);
                die;
            }
        } else if (!$dom->children()->length) {
            $dom->inner($empty->inner());
        }



            if (isset($this->placeholder)) {
                if ($this->opt) {
                    $this->opt->attr('value', '');
                    $this->opt->inner($this->placeholder);
                    $this->opt->setAttributes([]);
                    $dom->prepend($this->opt->outer());
                } else {
                    $dom->prepend('<option value="">'.$this->placeholder.'</option>');
                }
            }


        $dom->before($dom->inner());
        $dom->remove();
    }

    function filter_prepare() {
        $filter = $this->app->vars('_post.filter');
        if (!((array)$filter === $filter)) {
            return;
        }
        foreach($filter as $fld => $val) {
            if (substr($fld, -7) == '__range') {
                $range = explode(';', $val);
                unset($filter[$fld]);
                $fld = substr($fld,0,-7);
                if (!isset($range[1])) $range[1] = $range[0];
                $filter[$fld] = ['$gte'=>$range[0] , '$lte'=>$range[1]];

                //$filter[$fld.'_min'] = ['$gte'=>$range[0]];
                //$filter[$fld.'_max'] = ['$lte'=>$range[1]];
            } else if (substr($fld, -8) == '__minmax') {
                $range = explode(';', $val);
                unset($filter[$fld]);
                $fld = substr($fld, 0, -8);
                if (!isset($range[1])) {
                    $range[1] = $range[0];
                }
                $filter[$fld.'_min'] = ['$gte'=>$range[0]];
                $filter[$fld.'_max'] = ['$lte'=>$range[1]];
            }
        }
        $_POST['filter'] = $filter;

    }
}
