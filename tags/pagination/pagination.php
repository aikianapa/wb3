<?php
class tagPagination
{
    public function __construct(&$dom)
    {
        return $this->pagination($dom);
    }

    public function pagination(&$dom)
    {
        if ($dom->hasClass('.pagination')) {
            return;
        }

        ini_set('max_execution_time', 900);
        ini_set('memory_limit', '1024M');
        $pages = intval(ceil($dom->params->count/$dom->params->size));
        $page = $dom->params->page;

        !$page ? $page = 1 : null;
        !is_numeric($pages) ? $pages = 0 : null;

        $dom->pages = $pages;

        if (!isset($dom->params->tpl)) {
            if ($dom->parent()->attr('id') == '') {
                $dom->parent()->attr('id', 'fe_'.md5($dom->outer()));
            }
        }
        $tplId = $dom->parent()->attr('id');

        $foreach = (object)[];
        $foreach->_route = $dom->app->vars('_route');
        $foreach->_params = (array)$dom->params;

        $foreach->url = $foreach->_route['uri'];
        $foreach->target = '#'.$tplId;

        $tpl = $dom->tpl;

        /*
        if ( is_object( $dom->parent( 'table' ) ) && $dom->parent( 'table' )->find( 'thead th [data-wb-sort]' )->length ) {
            $dom->parent( 'table' )->find( 'thead' )->attr( 'data-wb-size', $size );
            $dom->parent( 'table' )->find( 'thead' )->attr( 'data-wb', $tplId );
        }
        */
        $pag = $dom->app->getTpl('pagination_ui.php');
        if (!$pag) $pag = $dom->app->fromFile(__DIR__ . '/pagination_ui.php');
        if ($pages > 0 or $dom->params('sort') > '') {
            //$pag->wrapInner( '<div></div>' );
            $step = 1;
            $flag = floor($page/10);
            if ($flag <= 1) {
                $flag = 0;
            } else {
                $flag *= 10;
            }
            //$inner = '';
            if (!$dom->params('filter')) {
                $dom->params->filter = [];
            }
            $pagination = array( 'id'=>$tplId, 'size'=>$dom->params->size, 'count'=>$dom->params->count, 'filter'=>$dom->params->filter, 'pages'=>array() );
            if (!isset($_ENV['route']['params']['form']) or $_ENV['route']['params']['form'] == '') {
                $form = $tplId;
            } else {
                $form = $_ENV['route']['params']['form'];
            }

            $pagarr = $this->_tagPaginationArr($page, $pages);

            foreach ($pagarr as $i => $p) {
                $pn = $p;
                if ($p == '...' and $pagarr[$i-1]<$page) {
                    $pn = intval(($pagarr[$i+1] + $pagarr[$i-1]) /2);
                }
                if ($p == '...' and $pagarr[$i-1] >= $page) {
                    //$pn = intval($page+( $pages-$page )/2);
                    $pn = intval(($pagarr[$i+1] + $pagarr[$i-1]) /2);
                }

                $href = $_ENV['route']['controller'].'/'.$_ENV['route']['mode'].'/'.$form.'/'.$pn;
                $pagination['pages'][$i] = array(
                    'label'=>$p,
                    'page'=>$pn,
                    'href'=>$href,
                    'flag'=>$flag,
                    'data'=>"{$tplId}-{$pn}"
                );
            }

            if (!isset($dom->params->more)) {
                $dom->params->more = '';
            }
            $more = explode(':', $dom->params->more);

            $pag->item = $pagination ;
            $pag->setAttributes();
            $pag->fetch();
            $pag->find("[data-page={$page}]")->addClass('active');
            $pag->find('ul')->attr('data-tpl', '#'.$tplId);

            if (intval($page) < intval($pages)) {
                $pag->find('[data-page=next] .page-link')->attr('data-page', $page + 1);
            } else {
                $pag->find('[data-page=next]')->attr('disabled', true);
            }

            if (intval($page) > 1) {
                $pag->find('[data-page=prev] .page-link')->attr('data-page', $page - 1);
            } else {
                $pag->find('[data-page=prev]')->attr('disabled', true);
            }


            $pag->find(".page-link[data-page={$page}]")->parent('.page-item')->addClass('active');

            if ($more[0] > '') {
                $pag->find("[data-page='next']")->remove();
                $pag->find("[data-page='prev']")->remove();
                $pag->find("[data-page!='more']")->css('display', 'none');
                if (isset($more[1]) && $more[1]>' ') {
                    $pag->find("[data-page='more'] .page-link")->html($more[1]);
                }
                if ($more[0] !== 'true') {
                    $pag->find("[data-page='more']")->attr('data-trigger', $more[0])->css('display', 'none');
                }
            } else {
                $pag->find("[data-page='more']")->remove();
            }

            if ($pages < 2) {
                $style = $pag->find('ul')->attr('style');
                $pag->find('ul')->attr('style', $style.';display:none;');
            }

            
            if ($dom->is('table, tbody') or $dom->parent()->is('table, tbody')) {
                $target = $dom->closest('table');
            } else {
                $target = &$dom;
            }

            if ($dom->params("more") == '' and ($dom->params("pos") == 'top' or $dom->params("pos") == 'both')) {
                $target->parent()->parent()->length ? $target->parent()->before($pag) : $target->before($pag);
            }
            if ($dom->params("pos") !== 'top') {
                $target->parent()->parent()->length ? $target->parent()->after($pag) : $target->after($pag);
            }
        }
        $dom->find("[data-page='{$page}']")->addClass('active');
        $dom->removeAttr('data-wb');
        $dom->append(
            "
        <template id='{$tplId}' data-params='".json_encode($foreach)."'>
            $tpl
        </template>
        "
        );
        $this->tid = $foreach->target;
        $this->pag = $pag;
    }

    public function outer()
    {
        return $this->pag->outer();
    }

    public function inner()
    {
        return $this->pag->inner();
    }

    public function html()
    {
        return $this->pag->html();
    }

    public function _tagPaginationArr($c, $m)
    {
        $current = $c;
        $last = $m;
        $delta = 4;
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $range = array();
        $rangeWithDots = array();
        $l = -1;

        for ($i = 1; $i <= $last; $i++) {
            if ($i == 1 || $i == $last || $i >= $left && $i < $right) {
                array_push($range, $i);
            }
        }

        for ($i = 0; $i<count($range);
        $i++) {
            if ($l != -1) {
                if ($range[$i] - $l === 2) {
                    array_push($rangeWithDots, $l + 1);
                } elseif ($range[$i] - $l !== 1) {
                    array_push($rangeWithDots, '...');
                }
            }

            array_push($rangeWithDots, $range[$i]);
            $l = $range[$i];
        }

        return $rangeWithDots;
    }

    public function ajax__pagination()
    {
        $app = new wbApp();
        if ($app->vars('_post.params.route')) {
            $app->vars('_route', $app->vars('_post.params.route'));
        }

        $res = array();
        foreach ($_POST as $key =>$val) {
            $$key = $val;
        }

        if (!isset($page)) {
            $page = 1;
        }
        if (!isset($find)) {
            $find = '';
        }

        $tpl = $app->FromString($tpl, true);
        $fe = $tpl->children('.wb-html')->children(':first-child');
        $fe->attr('data-wb-page', $page);
        $tpl->fetch();
        $tpl->find('.pagination[id]')->attr('id', 'ajax-'.$tplid);
        $fe = $tpl->children('.wb-html')->children(':first-child');
        $res['data'] = $fe->html();
        $res['pages'] = $pages;
        return json_encode($res);
    }
}
