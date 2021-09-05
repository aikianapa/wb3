<?php
class ctrlForm
{
    public function __construct($app)
    {
        $this->app = $app;
        $mode = $app->route->mode;
        $this->$mode();
    }

    public function __call($mode, $params)
    {
        if (!is_callable(@$this->$mode)) {
            header('HTTP/1.1 404 Not Found');
            echo  $this->get404();
            die;
        }
    }

    public function show()
    {
        $app = &$this->app;
        $cache = $app->getCache();
        $item = [];
        if ($app->vars('_post._tid') > '' AND $app->vars('_post._tid') == $app->vars('_post.target') AND $app->vars('_post.filter') > '') {
            // признак фильрации в темплейте
            $this->target = $app->vars('_post.target');
            $cache = false;
        }
        if (!$cache) {
            $_ENV["cache_used"] = false;
            isset($app->route->form) ? $dom = $app->getForm($app->route->form, $app->route->mode) : null;
            if ($dom->error) $dom = null;
            if (isset($app->route->item)) {
                $table = $app->route->form;
                isset($app->route->table) ? $table = $app->route->table : null;
                $item = $app->itemRead($table, $app->route->item);
                $item = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$table], $item);
                if ($item['active'] == 'on') {
                    if ((!isset($item['template']) OR $item['template'] == '') AND $app->vars('_route.tpl') > '') {
                        $dom = $app->getTpl($app->vars('_route.tpl'));
                    } elseif (isset($item['template']) and $item['template'] > '') {
                        $dom = $app->getTpl($item['template']);
                    } elseif (isset($app->route->tpl)) {
                        $dom = $app->getTpl($app->route->tpl);
                    } else {
                        // последняя попытка
                        $app->route->tpl = $table.'-show.php';
                        $dom = $app->getTpl($app->route->tpl);
                        !$dom ? $dom = $this->get404() : null;
                    }
                    $dom ? $dom->item = $item : null;
                }
            }
            if (!$dom or (isset($item['active']) and $item['active'] !== 'on')) {
                $dom = $this->get404();
            }
            $dom->fetch();
            $dom->setSeo();
            if (isset($this->target)) {
                $out = $dom->find($this->target)->outer();
                $out = '<div>'.$out.'</div>';
            } else {
                $out = $dom->outer();
            }
            
            !strpos(' '.trim($out), '<!DOCTYPE') ? $out = '<!DOCTYPE html>'.$out : null;
            echo $out;
            $app->setCache($out);
        } else {
            $_ENV["cache_used"] = true;
            echo $cache;
        }
        if ($app->vars('_sett.showstats') == 'on')  echo wbUsageStat();
        die;
    }

    public function get404()
    {
        $app = &$this->app;
        header('Content-type: text/html');
        header('HTTP/1.1 404 Not Found');
        $dom = $app->getTpl('404.php');
        if (!$dom) {
            $dom = $app->fromString("<html><head><meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'></head><center><img src='/engine/modules/cms/tpl/assets/img/virus.svg' width='200'><h3>[404] Page not found</h3></center></html>");
        }
        return $dom;
    }

    public function ajax()
    {
        $app = $this->app;
        $form = $app->vars('_route.params.0');
        $mode = $app->vars('_route.params.1');
        if ($mode == 'list' AND $app->vars('_post.render') == 'client') {
//            $dom = $app->getForm($form, $mode);
//            $dom->fetch();
            $options = ( object )$_POST;
            !isset($options->size) ? $options->size = 500 : 0 ;
            !isset($options->page) ? $options->page = 1 : 0;
            !isset($options->filter) ? $options->filter = [] : 0;
            $list = $app->itemList($form, ( array )$options);
            foreach ($list['list'] as &$item) {
                if (isset($_ENV['locale']) AND isset($_ENV['locale'][$_ENV['lang']])) {
                    $item['_lang'] = &$_ENV['locale'][$_ENV['lang']];
                }
                $item = wbTrigger('form', __FUNCTION__, 'beforeItemShow', [$form], $item);
            }
            $pages = ceil($list['count'] / $options->size);
            $pagination = wbPagination($options->page, $pages);
            echo json_encode(['result'=>$list['list'], 'pages'=>$pages, 'page'=>$options->page, 'pagination'=>$pagination]);
            die;
        }
    }
}
