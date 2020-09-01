<?php
class ctrlForm
{
    public function __construct($app)
    {
        $this->app = $app;
        $this->route = $app->route;
        $mode = $this->route->mode;
        $this->$mode();
    }

    public function __call($mode, $params)
    {
        if (!is_callable(@$this->$mode)) {
            header('HTTP/1.1 404 Not Found');
            echo 'Error 404';
            die;
        }
    }

    public function show()
    {
        $app = &$this->app;
        $cache = $app->getCache();
        $item = [];
        if (!$cache) {
            if (isset($this->route->form)) {
                $dom = $app->getForm($this->route->form, $this->route->mode);
            }
            if (isset($this->route->item)) {
                $table = $this->route->form;
                if (isset($this->route->table)) {
                    $table = $this->route->table;
                }
                $item = $app->db->itemRead($table, $this->route->item);
                $item = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$table], $item);
                if (isset($item['template']) and $item['template'] > '' and $item['active'] == 'on') {
                    $dom = $app->getTpl($item['template']);
                } elseif (isset($this->route->tpl)) {
                    $dom = $app->getTpl($this->route->tpl);
                } else {
                    // последняя попытка
                    $this->route->tpl = $table.'-show.php';
                    $dom = $app->getTpl($this->route->tpl);
                    if (!$dom) {
                        $dom = $this->get404();
                    }
                }
                if ($dom) {
                    $dom->item = $item;
                }
            }
            if (!$dom or (isset($item['active']) and $item['active'] !== 'on')) {
                $dom = $this->get404();
            }
            $dom->fetch();
            $dom->setSeo();
            $out = $dom->outer();
            if (!strpos(' '.$out, '<!DOCTYPE html>')) {
                $out = '<!DOCTYPE html>'.$out;
            }
            echo $out;
            $app->setCache($out);
        } else {
            echo $cache;
        }
        die;
    }

    public function get404()
    {
        $app = &$this->app;
        header('HTTP/1.1 404 Not Found');
        $dom = $app->getTpl('404.php');
        if (!$dom) {
            $dom = $app->fromString("<html><head><meta name='viewport' content='width=device-width; initial-scale=1.0; user-scalable=no'></head><center><img src='/engine/modules/cms/tpl/assets/img/virus.svg' width='200'><h3>[404] Page not found</h3></center></html>");
        }
        return $dom;
    }

    public function ajax()
    {
        $app = $this->app;
        $form = $app->vars('_route.params.0');
        $mode = $app->vars('_route.params.1');
        if ($mode == 'list' and $app->vars('_post.render') == 'client') {
            $options = ( object )$_POST;
            if (!isset($options->size)) {
                $options->size = 500;
            }
            if (!isset($options->page)) {
                $options->page = 1;
            }
            if (!isset($options->filter)) {
                $options->filter = [];
            }
            $list = $app->itemList($form, ( array )$options);
            foreach ($list['list'] as &$item) {
                $item = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$form], $item);
            }
            $pages = ceil($list['count'] / $options->size);
            $pagination = wbPagination($options->page, $pages);
            echo json_encode(['result'=>$list['list'], 'pages'=>$pages, 'page'=>$options->page, 'pagination'=>$pagination]);
            die;
        }
    }
}
