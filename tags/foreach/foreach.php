<?php

use Nahid\JsonQ\Jsonq;

class tagForeach
{
    public function __construct($dom)
    {
        if (!isset($dom->role)) {
            return;
        }
        unset($dom->role);
        $dom->is(":root") ? $dom->rootError() : null;
        $this->foreach($dom);
    }

    public function foreach(&$dom)
    {
        !$dom->app ? $dom->app = new wbApp() : null;
        $this->app = &$dom->app;
        $this->dom = &$dom;
        $this->empty = $dom->find("wb-empty")[0];
        $dom->find("wb-empty")->remove();
        $dom->tpl = "<html>".$dom->inner()."</html>";
        if ($dom->parent()->is("select[placeholder]")) {
            $this->opt = $dom->find('option', 0)->clone();
            $this->placeholder = $dom->parent()->attr("placeholder");
        }

        $dom->html("");
        $dom->outer = $dom->outer();
        !isset($this->tid) && $dom->attr("id") > "" ? $this->tid = $dom->attr("id") : null;
        !isset($this->tid) && $dom->parent()->attr("id") > "" ? $this->tid = $dom->parent()->attr("id") : null;
        !isset($this->tid) ? $this->tid = "fe_" . $this->app->newId() : null;

        $dom->tid = $this->tid;
        if (!$dom->params('length') && $dom->attr('data-ajax') > '') {
            $dom->params = (object)wbAttrToValue($dom->attr('data-ajax'));
        }


        $dom->params('target') == '' ? $dom->params->target = '#'.$this->tid : null;
        $dom->params("render") == "client" ? $render = $dom->params("render") : $render = "server";
//        $dom->params->render = $render;
        $dom->render = $render;
        $this->$render($dom);
        $dom->rendered = true;
    }


    private function client(&$dom)
    {
        $empty = &$this->empty;
        $render = 'client';
        $parent = $dom->item;

        $idx = 0;
        $ndx = 1;
        $srvpag = false;
        unset($dom->params->tpl);

        if ($dom->params("table") > "") {
            if ($dom->params("size") == '') {
                $dom->params->size = 999999999;
            }
            $ajax = '/api/query/' . $dom->params("table") . '/';
            $ajax .= '?&__options=size='.$dom->params("size");
            $dom->params('limit') > '' ? $ajax .= ';limit='.$dom->params('limit') : null;
            $dom->params->ajax = $ajax;
            $dom->attr("data-ajax", '{"url":"' . $dom->params("ajax") . '"}');
            $table = null;
        } elseif ($dom->params("ajax") > "") {
            $dom->attr("data-ajax", '{"url":"' . $dom->params("ajax") . '"}');
        }

        $dom->attr("data-ajax") > "" ? $ajax = $dom->attr("data-ajax") : $ajax = false;
        
        list($list, $count, $pages, $page, $srvpag, $options) = $this->list();
        $even = false;
        foreach ((array) $list as $key => $val) {
            $value = $val;
            $val = (object) $val;
            $val->_idx = $idx;
            $val->_ndx = $ndx;
            $val->_page = $page;
            $val->_pages = $pages;
            $val->_val = $value;
            $val->_parent = &$parent;
            $val->_key = $key;

            if ($even) {
                $val->_even = $even = false;
                $val->_odd = true;
            } else {
                $val->_even = $even = true;
                $val->_odd = false;
            }

            if (!isset($val->_id)) {
                isset($val->id) ? $val->_id = $val->id : $val->_id = $idx;
            }
            if ($dom->params('table') > "") {
                $val = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$dom->params->table], (array) $val);
            }
            if ($ajax !== false) {
                $list[$key] = (array)$val;
            } else {
                $line = $this->app->fromString($dom->tpl, true);
                $line->copy($dom);
                $line->item = (array)$val;
                $line->fetch();
                $dom->append($line->inner());
            }
            $idx++;
            $ndx++;
        }
        // create template
        $params = $dom->params;
        $params->target = '#'.$this->tid;
        $dom->params("from") > "" ? $from = $dom->params->from : $from = $dom->params->from = 'result';
        $params = json_encode($params);
        $dom->append("<template id = \"{$this->tid}\" >\n{{#each {$from}}}\n" . $dom->tpl . "\n{{/each}}</template>\n");
        $ajax !== false ? $dom->find("template[id='{$this->tid}']")->attr('data-ajax', $dom->attr("data-ajax")) : null;
        $params > '' ? $dom->find("template[id='{$this->tid}']")->attr('data-params', $params) : null;
        $dom->find("template[id=\"{$this->tid}\"] .pagination")->attr("data-tpl", $this->tid);
        //

        if ($dom->params("size") > "" and $ajax == false) {
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
            $dom->params->tpl = $dom->tid;
            $dom->params->page = $page;
            //$pag = $dom->tagPagination($dom);
            !count((array)$list) or $dom->html() == "" ? $dom->inner($empty->inner()) : null;
            isset($dom->params->pos) ? $pos = $dom->params->pos : $pos = 'bottom';

            if ($srvpag or ($this->app->route->controller == 'ajax' and $this->app->vars('_post._params') > "")) {
                // При вызове из data-ajax требуется второе условие
                $res = [
                    'html' => $dom->inner(),
                    'route' => $this->app->route,
                    'params' => $dom->params,
                    'pag' => $pag->outer(),
                    'pos' => $pos
                ];
                header('Content-Type: charset=utf-8');
                header('Content-Type: application/json');
                echo json_encode($res);
                die;
            }
        } elseif (!$dom->children()->length) {
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

    private function server(&$dom)
    {
        $empty = &$this->empty;
        $render = "server";
        $idx = 0;
        $ndx = 1;
        $parent = $dom->item;
        ($dom->params('tpl') == 'false') ? $ttt = false : $ttt  = true;
        //$this->app->vars('_post.route') == '' and $dom->params('tpl') == 'true' ? $dom->addTpl() : null;

        list($list, $count, $pages, $page, $srvpag, $options) = $this->list();

        $dom->attr("data-ajax") > "" ? $ajax = $dom->attr("data-ajax") : $ajax = false;
        $even = false;
        foreach ((array) $list as $key => $val) {
            $value = $val;
            $val = (object) $val;
            $val->_key = $key;
            if (!isset($val->__total)) {
                $table = $dom->params('table');
                $val->_page = $page;
                $val->_pages = $pages;
                $val->_idx = $idx;
                $val->_ndx = $ndx;
                $val->_val = $value;
                $val->_parent = &$parent;
                isset($val->_table) && $table == '' ? $table = $val->_table : null;
                !isset($val->_id) and isset($val->id) ? $val->_id = $val->id : $val->_id = $idx;
                $table > "" ? $val = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$table], (array) $val) : null;
            }

            if ($even) {
                $val->_even = $even = false;
                $val->_odd = true;
            } else {
                $val->_even = $even = true;
                $val->_odd = false;
            }


            if ($ajax !== false) {
                $list[$key] = (array) $val;
            } else {
                if (isset($val->__total)) {
                    foreach ((array)$val->sum as $k =>$v) {
                        $val->$k = $v;
                    }
                }


                $line = $this->app->fromString($dom->tpl, true);
                $line->copy($dom);
                $line->item = (array) $val;
                $line->fetch();
                $dom->append($line->inner());
            }
            if (!isset($val->__total)) {
                $idx++;
                $ndx++;
            }
        }

        $params = $dom->params;

        if ($this->app->vars('_post.route') > '' && $this->app->vars('_post._tid') !== '') {
            $tpl = false;
        } else {
            $tpl = true;
        }
        if ($tpl && $ttt !== false) {
            if ($ajax !== false or $dom->params('table') > '' or $dom->params('from') > '') {
                $params->target = '#'.$this->tid;
                $locale = $dom->locale;
                if (isset($locale[$dom->app->lang])) {
                    $locale = $locale[$dom->app->lang];
                    $params->locale = $locale;
                }
                $params->route = $dom->app->route;

                $dom->params("from") > "" ? $from = $dom->params->from : $from = 'result';
                if ($ajax !== false) {
                    $dom->append("<template id = \"{$this->tid}\" >\n{{#each {$from}}}\n" . $dom->tpl . "\n{{/each}}</template>\n");
                    $params->render = 'client';
                } elseif (!$dom->app->vars('_post.update') AND $this->app->vars('_post._params.tpl') !== 'false' ) {
                    $tpl = $dom->app->fromString($dom->tpl);
                    $dom->append("<template id = \"{$this->tid}\" >\n" . $tpl->outer() . "\n</template>\n");
                }
            
                $ajax !== false ? $dom->find("template[id='{$this->tid}']")->attr('data-ajax', $dom->attr("data-ajax")) : null;
                $params = json_encode($params);
                $params > '' ? $dom->find("template[id='{$this->tid}']")->addParams($params) : null;
                $dom->find("template[id=\"{$this->tid}\"] .pagination")->attr("data-tpl", $this->tid);
            }
        }
        if ($dom->params("size") > "" OR $this->app->vars('_post._params.tpl') == 'true') {
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
            $dom->parent()->attr("data-page", $page);
            $dom->params->count = $count;
            $dom->params->tpl = $dom->parent()->attr('id');
            $dom->params->page = $page;
            $pag = $dom->tagPagination($dom);

            !count((array)$list) or $dom->html() == "" ? $dom->inner($empty->inner()) : null;
            isset($dom->params->pos) ? $pos = $dom->params->pos : $pos = 'bottom';

            if (($srvpag
                    or ($this->app->route->controller == 'ajax' and $this->app->vars('_post._params') > "")
                    or ($this->app->route->mode == 'ajax' and $this->app->vars('_post._params') > "")
                ) and  !($this->app->vars('_post._params.tpl') == 'true' && $size == '' ) 
            ) {
                if (!count((array)$list) or $dom->html() == "") {
                    $html = $empty->inner();
                    $pag = "";
                } else {
                    $html = $dom->html();
                    $pag = $pag->outer();
                }
                $res = [
                    'html' => $html,
                    'route' => $this->app->route,
                    'params' => $dom->params,
                    'pag' => $pag,
                    'pos' => $pos
                ];
                header('Content-Type: charset=utf-8');
                header('Content-Type: application/json');
                echo json_encode($res);
                die;
            }
        } elseif (!$dom->children()->length) {
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
        !count((array)$list) ? $dom->inner($empty->inner()) : null;

        $dom->before($dom->inner());
        $dom->remove();
    }

    private function list()
    {
        $dom = &$this->dom;

        $dom->filterStrict();
        $options = $this->options();

        $count = 0;
        $page = $pages = 1;
        $srvpag = false;
        $call = $dom->params("call");
        $list = [];
        $listTable = $parent = $dom->item;
        $dom->params('render') == 'server' && $dom->params('bind') > '' ? $dom->params->tpl = 'true' : null;
        //$this->app->vars('_post.route') == '' and $dom->params('tpl') == 'true' ? $dom->addTpl() : null;

        $dom->params("form") > "" ? $dom->params->table = $dom->params->form : null;
        $dom->params("table") > "" ? $table = $dom->params->table : $table = "";

        isset($dom->params->field) ? $field = $dom->params->field : $field = null;
        $filtered = false;
        if ($table > "" and $call == "") {
            $res = wbItemList($table, $options);
            $list = $res["list"];
            $count = $res["count"];
            $filtered = true;
        } elseif ($table > "" and $call > "") {
            $list = [];
            $formClass = $this->app->formClass($table);
            $method = $call;
            if (method_exists($formClass, $method)) {
                $list = $formClass->$method($dom);
            }
            $count = count($list);
            $filtered = true;
        } elseif ($table == "" and $call > "") {
            substr($call, -1) == ')' ? null : $call.='()';
            $list = (array)wbEval($call);
        }

        if ($dom->params('ajax') and $dom->params('render') == 'server') {
            $ajax = $dom->params('ajax');
            if ($dom->params("size") == '') {
                $dom->params->size = 999999999;
            }
            $url = parse_url($ajax);
            if (!isset($url['scheme'])) {
                if ($this->app->vars('_sett.api_key_query') == 'on' and !isset($url['__token'])) {
                    strpos($ajax, '?') ? $ajax .= '&' : $ajax .= '?';
                    $ajax .= '__token='.$this->app->vars('_sess.token');
                }
            }
            $ajax = $this->app->vars('_route.host').$ajax;
            $list = json_decode(str_replace("'", '"', wbAuthPostContents($ajax)), true);
            !$list ? $list = [] : null;
            $count = count($list);
        }

        if ($dom->params('json')) {
            $list['json'] = json_decode(str_replace("'", '"', $dom->params("json")), true);
            $dom->params->from = 'json';
        }

        if ($dom->params('from')) {
            if ($dom->app->vars($dom->params->from) > '') {
                $list = $dom->app->vars($dom->params->from);
            } elseif (isset($list[$dom->params->from])) {
                $list = $list[$dom->params->from];
            } else {
                $list = $dom->getField($dom->params->from);
            }
        }
        //$dom->params('filter') ? $list = $dom->app->arrayFilter($list, $dom->params('filter')) : null;

        $this->options = $options;
        $this->sort($list);
        if ($dom->params('count') > "") {
            isset($list) ? $$item = $list : $item = [];
            $list = [];
            $start = 1;
            $count = $dom->params->count;
            if (strpos($count, ';')) {
                $count = explode(';', $count);
                $start = intval($count[0]);
                $count = intval($count[1]);
            } else {
                $count = intval($count);
            }

            if ($start <= $count) {
                for ($i = $start; $i <= $count; $i++) {
                    $list[] = ["_id" => $i,"_value" => $i, "id" => $i];
                }
            } else {
                for ($i = $start; $i >= $count; $i--) {
                    $list[] = ["_id" => $i,"_value" => $i, "id" => $i];
                }
            }
        }

        !isset($list) ? $list = [] : null;

        if (isset($options['filter']) && (array)$list === $list && $filtered == false) {
            foreach ($list as $key => $item) {
                if (!wbItemFilter((array)$item, $options['filter'])) {
                    unset($list[$key]);
                }
            }
        }

        if ($dom->params('minimal') > '0') {
            $min = $dom->params('minimal')*1;
            $lmt = $dom->params('limit')*1;
            $cnt = count($list);
            if ($cnt > 0) {
                if ($cnt < $min) {
                    $last = array_pop($list);
                    $add = $min - $cnt;
                    for ($i=0;$i<=$add;$i++) {
                        $list[]=$last;
                    }
                }
                if ($lmt < $min) {
                    $dom->params->limit = $min;
                    $options['limit'] = $min;
                }
            }
        }

        if ($dom->params('group') or $dom->params('total') or $dom->params('avg') or $dom->params('min') or $dom->params('max')) {
            $this->avg = wbAttrToArray($this->dom->params('avg'));
            $this->min = wbAttrToArray($this->dom->params('min'));
            $this->max = wbAttrToArray($this->dom->params('max'));
            $this->sum = wbAttrToArray($this->dom->params('total'));
            
            $this->flds = array_keys(array_flip(array_merge($this->avg, $this->min, $this->max, $this->sum)));

            if ($dom->params('size') == '') $dom->params->size = '';
            if ($dom->params('group') > '') {
                $list = $this->group($list);
            } else {
                $total = $this->total($list);
                array_push($list, $total);
            }
        }

        $dom->params("rand") == "true" ? shuffle($list) : null;

        if ($list && $dom->params("size") > "") {
            $count = count($list);
            $dom->params("page") ? $page = $dom->params->page : $page = 1;
            if ($dom->parent()->attr('id') == '') {
                $dom->parent()->attr('id', 'fe_' . md5($dom->outer()));
            }

            if ($this->app->vars('_post._route') and $this->app->vars('_post._params') and $this->app->vars('_post._tid') == '#' . $dom->parent()->attr('id')) {
                $page = $this->app->vars('_post._params.page');
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

        if (isset($options['limit'])) {
            $list = array_chunk($list, intval($options['limit']));
            $list = $list[0];
        }
        return [$list, $count, $pages, $page, $srvpag, $options];
    }


    private function sort(&$list) {
        $options = $this->options;
        if (isset($options["sort"]) and (array) $options["sort"] === $options["sort"]) {
            foreach ((array) $options["sort"] as $key => $fld) {
                if (!((array) $fld === $fld)) {
                    $fld = explode(":", $fld);
                    if (!isset($fld[1])) {
                        $fld[1] = 1;
                    } elseif (in_array(strtolower($fld[1]), ['a', 'asc', '1'])) {
                        $fld[1] = '';
                    } elseif (in_array(strtolower($fld[1]), ['d', 'desc', '-1'])) {
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

    private function group($list, $flds = null)
    {
        $jsonq = $this->app->json($list);
        $flds == null ? $flds = wbAttrToArray($this->dom->params('group')) : null;
        $total = $this->total($list);
        $list = [];
        if (count($flds)) {
            $fld = array_shift($flds);
            $grps = $jsonq->groupBy($fld)->get();
            foreach ($grps as $key => $grp) {
                $this->sort($grp);
                count($flds) > 0 ? $grp = $this->group($grp, $flds) : null;
                $grtot = $this->total($grp, $key);
                !$this->dom->params('supress') == 'true' ? $list = array_merge($list, $grp) : null;
                $grtot['_value'] = $key;
                if ($flds == null) {
                    array_push($list, $grtot);
                }
            }
        }
        array_push($list, $total);
        return $list;
    }


    private function total($list, $grp=null)
    {
        $jsonq = $this->app->json($list);
        $total = ['__total' => 'true'];
        if ($grp) {
            $total['_total'] = $grp.'';
            $total['_class'] = 'group-total';
        } else {
            $total['_total'] = '_total';
            $total['_class'] = 'grand-total';
        }
        $total['_count'] = count($list);
        $total['__total'] = 'true'; // признак для отключения нумерации в foreach
        $total['sum'] = $total['avg'] = $total['min'] = $total['max'] = [];
        foreach ($this->flds as $fld) {
            in_array($fld, $this->sum) ? $total['sum'][$fld] = $jsonq->sum($fld) : null;
            in_array($fld, $this->avg) ? $total['avg'][$fld] = $jsonq->avg($fld) : null;
            in_array($fld, $this->min) ? $total['min'][$fld] = $jsonq->min($fld) : null;
            in_array($fld, $this->max) ? $total['max'][$fld] = $jsonq->max($fld) : null;
        }
        return $total;
    }

    private function options()
    {
        $dom = &$this->dom;
        $options = [];
        $options['filter'] = [];
        
        // Нужно для работы фильтра
        $dom->parent()->attr("id") > "" ? $pid = $dom->parent()->attr("id") : $pid = "fp_" . $this->app->newId();
        $dom->parent()->attr("id", $pid);

        if ($this->app->vars('_post.filter') > '' && $this->app->vars('_post.target') == '#'.$pid) {
            $this->filter_prepare();
            $options["filter"] = $this->app->vars('_post.filter');
        }
        $dom->params("orm") > "" ? $options["orm"] = $dom->params->orm : null;
        $dom->params("item") > "" ? $options["item"] = $dom->params->item : null;
        $dom->params("filter") > "" ? $options["filter"] = array_merge($dom->params->filter, $options["filter"]) : null;
        $dom->params("limit") > "" ? $options["limit"] = $dom->params->limit : null;
        $dom->params("where") > "" ? $options["where"] = $dom->params->where : null;
        $dom->params("return") > "" ? $options["return"] = $this->app->attrToArray($dom->params("return")) : null;
        $dom->params("sort") > "" ? $options["sort"] = $this->app->attrToArray($dom->params("sort")) : null;
        return $options;
    }

    public function filter_prepare()
    {
        $filter = $this->app->vars('_post.filter');
        if (!((array)$filter === $filter)) {
            return;
        }
        foreach ($filter as $fld => $val) {
            if (substr($fld, -7) == '__range') {
                $range = explode(';', $val);
                unset($filter[$fld]);
                $fld = substr($fld, 0, -7);
                if (!isset($range[1])) {
                    $range[1] = $range[0];
                }
                $filter[$fld] = ['$gte'=>$range[0] , '$lte'=>$range[1]];

            //$filter[$fld.'_min'] = ['$gte'=>$range[0]];
                //$filter[$fld.'_max'] = ['$lte'=>$range[1]];
            } elseif (substr($fld, -8) == '__minmax') {
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
