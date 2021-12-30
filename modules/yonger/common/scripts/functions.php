<?php

    function customRoute($route = [])
    {
        $app = &$_ENV['app'];

        if ($app->vars('_route.controller') == 'form' && $app->vars('_route.mode') == 'show') {
            $uri = $app->route->uri;
            $path = explode('/', $uri);
            $name = array_pop($path);
            $path = implode('/', $path);

            $uri == '/' && $name == '' ? $name = 'home' : null;
            if (isset($app->route->item) && $app->route->item !== $name) {
                $app->route->alias = $name;
                $name = $app->route->item;
                $uri = $path.'/'.$name;
            };
            $pages = $app->itemList('pages', ['filter'=>[
            '_site' => [
                '$in'=> [null,'{{_sett.site}}']
            ],
            'name'=>$name,
            'active'=>'on',
            'path' => $path
        ]]);
            foreach ($pages['list'] as $page) {
                if ($page['url'] == $uri) {
                    $app->route->controller = 'form';
                    $app->route->mode = 'show';
                    $app->route->table = 'pages';
                    $app->route->item = $page['_id'];
                    $app->route->tpl = "page.php";
                    $app->vars('_route', $app->objToArray($app->route));
                    break;
                }
            }
        }
    }

    function yongerSiteMenu($path = '')
    {
        $app = &$_ENV['app'];
        $list = $app->itemList('pages', ['filter'=>[
            'active'=>'on'
            ,'path' => $path
            ,'_site' => [
                '$in'=> [null,'{{_sett.site}}']
            ]
        ]]);
        $list = $list['list'];
        foreach ($list as &$item) {
            $path = $item['path'];
            $name = $item['name'];
            $path.'/'.$name == '/' ? $path = '/home' : $path .= '/'.$name;
            $item['children'] = siteMenu($path);
            $path == '/home' ? $path =  '/' : null;
            $item['path'] = $path;
            if (count($item['children'])) {
                $self = $item;
                $self['divider'] = 'divider-after';
                unset($self['children']);
                array_unshift($item['children'], $self);
            }
        }
        return $list;
    }

    function yongerIsPage($link)
    {
        $app = &$_ENV['app'];
        if (substr($link,-1) == '/') $link = substr($link,0,-1);
        if (!$app->vars('_env.yonger.pages')) {
            $list = $app->itemList('pages', ['filter'=>[
            'active'=>'on'
            ,'_site' => [
                '$in'=> [null,'{{_sett.site}}']
            ]
            , 'id'=> [
                '$nin'=>['_header','_footer']
            ]
            ]]);
            $list = array_column($list['list'],'url');
            $app->vars('_env.yonger.pages',$list);
        } 
        return in_array($link, $app->vars('_env.yonger.pages'));
    }

    function _beforeItemSave(&$item)
    {
        $app = &$_ENV['app'];
        isset($item['path']) ? null : $item['path'] = '';
        isset($item['name']) ? null : $item['name'] = '';
        $item['_site'] = $app->vars('_sett.site');
        $item['_login'] = $app->vars('_sett.login');
        $item['url'] = $item['path'] . '/' . $item['name'];
        $item['url'] == '/home' ? $item['url'] = '/' : null;
        return $item;
    }
