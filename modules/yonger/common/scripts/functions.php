<?php

    function customRoute(&$route = [])
    {
        $app = $_ENV['app'];
        $map = $app->vars('_env.dba').'/_yonmap.json';
        $app->route->uri == '/' ? $uri = '/home' : $uri = $app->route->uri;
        $app->yonmap = [];
        if (is_file($map)) {
            $map = (array)json_decode(file_get_contents($map), true);
            $app->yonmap = &$map;
            $idx = md5($uri);
            if (isset($map[$idx])) {
                $app->route->controller = 'form';
                $app->route->mode = 'show';
                $app->route->table = $map[$idx]['f'];
                $app->route->form = $map[$idx]['f'];
                $app->route->item = $map[$idx]['i'];
                $app->route->name = $map[$idx]['n'];
                isset($app->route->tpl) ? null : $app->route->tpl = $map[$idx]['f'].".php";
                $app->vars('_route', $app->objToArray($app->route));
                $route = $app->route;
                return $route;
            }
        }

        if ($app->vars('_route.controller') == 'form' && $app->vars('_route.mode') == 'show') {
            $path = explode('/', $uri);
            $name = array_pop($path);
            $path = implode('/', $path);

            $uri == '/' && $name == '' ? $name = 'home' : null;
            if (isset($app->route->item) && $app->route->item !== $name) {
                $app->route->alias = $name;
                $name = $app->route->item;
                $uri = $path.'/'.$name;
            };
            $uri == '/home' ? $uri = '/' : null;

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
                    $app->route->name = $name;
                    isset($app->route->tpl) ? null : $app->route->tpl = "page.php";
                    $app->vars('_route', $app->objToArray($app->route));
                    $route = $app->route;
                    return $route;
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
            ,'menu'=>'on'
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
            $item['children'] = yongerSiteMenu($path);
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


    function yongerSiteMap($path = '')
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
            $item['children'] = yongerSiteMap($path);
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
        if (substr($link, -1) == '/') {
            $link = substr($link, 0, -1);
        }
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
            $list = array_column($list['list'], 'url');
            $app->vars('_env.yonger.pages', $list);
        }
        return in_array($link, $app->vars('_env.yonger.pages'));
    }


    function _beforeItemSave(&$item)
    {
        if ($item['_table'] == 'pages') {
            isset($item['path']) ? null : $item['path'] = '';

            isset($item['name']) ? null : $item['name'] = '';
            $item['url'] = $item['path'] . '/' . $item['name'];
            $item['url'] == '/home' ? $item['url'] = '/' : null;
        }
        $app = &$_ENV['app'];
        $item['_site'] = $app->vars('_sett.site');
        $item['_login'] = $app->vars('_sett.login');

        return $item;
    }

    function yongerCheckUrl($url, $form = 'pages', $id = null) {
        $res = false;
        $app = $_ENV['app'];
        $map = &$app->yonmap;
        $md5 = md5($url);
        if (isset($map[$md5])) {
            $data = $map[$md5];
            if ($id == null) {
                $res = true;
            } else if ($data['i'] == $id && $data['f'] == $form) {
                $res = true;
            }
        } else {
            $res = true;
        }
        return $res;
    }