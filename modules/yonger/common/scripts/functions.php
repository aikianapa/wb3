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
                $app->route->table = @$map[$idx]['f'];
                $app->route->form = @$map[$idx]['f'];
                $app->route->item = @$map[$idx]['i'];
                $app->route->name = @$map[$idx]['n'];
                $app->route->url = @$map[$idx]['u'];
                if (!$app->vars('_route.tpl')) {
                    $tpl = $app->getTpl($app->vars('_route.table') . '.php');
                    if ($tpl !== NULL) {
                        $app->route->tpl =  $app->vars('_route.table') . '.php';
                    } else {
                        $app->route->tpl = 'pages.php';
                    }
                }
                $app->vars('_route', $app->objToArray($app->route));
                return $app->route;
            } else if ($app->vars('_route.name') > '' && $app->vars('_route.form') > '') {
                foreach ($map as $m) {
                    if ($m['f'] == $app->vars('_route.form') && $m['n'] == $app->vars('_route.name') && $m['f'] == $app->vars('_route.form') ) {
                        $app->route->item = $m['i'];
                        $app->route->table = $m['f'];
                    }
                }
            }
        }

        if ($app->vars('_route.controller') == 'form' && $app->vars('_route.table') == 'pages' && $app->vars('_route.mode') == 'show') {
            $name = '';
            if (isset($app->route->name)) {
               $name=  $app->route->name;
               $path = '';
            } else {
                $path = explode('/', $uri);
                array_pop($path);
                $path = implode('/', $path);
            }
            $uri == '/' && $name == '' ? $name = 'home' : null;
            if ($app->vars('_route.item') !== $name) {
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
                if ($page['url'] == $uri OR (isset($app->route->name) && $app->route->name == $page["name"])) {
                    $app->route->controller = 'form';
                    $app->route->mode = 'show';
                    $app->route->table = 'pages';
                    $app->route->item = $page['_id'];
                    $app->route->name = $name;
                    isset($app->route->tpl) ? null : $app->route->tpl = "pages.php";
                    $app->vars('_route', $app->objToArray($app->route));
                    $route = $app->route;
                    return $route;
                }
            }
        } else if ($app->vars('_route.controller') == 'form' && $app->vars('_route.table') !== 'pages' && $app->vars('_route.mode') == 'show') {
            if (!$app->vars('_route.tpl')) {
                $tpl = $app->getTpl($app->vars('_route.table') . '.php');
                if ($tpl !== NULL) {
                    $app->route->tpl =  $app->vars('_route.table') . '.php';
                } else {
                    $app->route->tpl = 'pages.php';
                }
            }
            $app->vars('_route', $app->objToArray($app->route));
            $route = $app->route;
            return $route;
        }
    }

    function yongerSiteMenu($path = '')
    {

        $app = &$_ENV['app'];
        $path == '' ? $list = null : $list = $app->vars('_env.cache.yonmenu');
        if (!$list) {
            $list = $app->itemList('pages', ['sort' => '_sort','filter'=>[
                'active'=>'on'
                ,'menu'=>'on'
                ,'_site' => [
                    '$in'=> [null,'{{_sett.site}}']
                ]
            ]])['list'];
            $app->vars('_env.cache.yonmenu', $list);
        }

        $checkpath = $path;
        foreach ($list as $key => &$item) {
            if ($item['path'] !== $checkpath) {
                unset($list[$key]);
            } else {
                $header = $item['header'];
                $item = [
                    'id' => $item['id'],
                    'menu' => $item['menu'],
                    'menu_title' => $item['menu_title'],
                    'menu_icon' => $item['menu_icon'],
                    'header' => $header,
                    'name' => $item['name'],
                    'path' => $item['path'],
                    'children' => $item['children'],
                    'active' => $item['active'],
                    'attach' => $item['attach'],
                    'attach_filter' => $item['attach_filter']
                ];
                if ((array)$item['menu_title'] === $item['menu_title']) {
                    $item['menu_title'] = isset($item['menu_title'][$_SESSION['lang']]) ? $item['menu_title'][$_SESSION['lang']] : $item['menu_title']['ru'];
                }
                $item['menu_title'] == '' ? $item['menu_title'] = $header : null;

                if ((array)$item['menu_title'] === $item['menu_title']) {
                    $item['menu_title'] = isset($item['menu_title'][$_SESSION['lang']]) ? $item['menu_title'][$_SESSION['lang']] : $item['menu_title']['ru'];
                }

                if ((array)$item['header'] === $item['header']) {
                    $item['header'] = isset($item['header'][$_SESSION['lang']]) ? $item['header'][$_SESSION['lang']] : $item['header']['ru'];
                }

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
        }

        if ($checkpath == '') {
            unset($_ENV['cache']['yonmenu']);
        }
        return array_values($list);
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

    function yongerFreecode($str) {
        $str = html_entity_decode($str);
        $str = str_replace("%7B%7B", "{{", $str);
        $str = str_replace("%7D%7D", "}}", $str);
        return $str;
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
        if (isset($item['_table']) && $item['_table'] == 'pages') {
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

    function yongerLinks(&$html) {
        $app = $_ENV['app'];
        $map = json_decode(file_get_contents($_ENV['dba'].'/_yonmap.json'), true);
        $fr = $to = [];
        foreach ($map as $m) {
            if ($m['f'] == 'pages') {
                $fr[] = urlencode('['.$m['n'].']');
                $to[] = $m['u'];
                $fr[] = '['.$m['n'].']';
                $to[] = $m['u'];
            } else {
                $fr[] = urlencode('['.$m['f'].':'.$m['n'].']');
                $to[] = $m['u'];
                $fr[] = '['.$m['f'].':'.$m['n'].']';
                $to[] = $m['u'];
            }
        }
        $html = str_replace($fr, $to, $html);
        return $html;
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


    function yongerCrumbs($path = '/')
    {
        $app = &$_ENV['app'];
        $map = json_decode(file_get_contents($app->route->path_app . '/database/_yonmap.json'), true);
        $lang = $app->vars('_sess.lang');
        $chunk = explode('/', $path);
        $path = '';
        isset($app->route->name) && !in_array($app->route->name,$chunk) ?  $chunk[] = $app->route->name : null;
        foreach ($chunk as $cp) {
            if ($cp > '') {
                $path .= '/' . $cp;
                $json = $app->json($map);
                $res = $json->where('u', $path)->get();
                count($res) ? $res = array_pop($res) : null;
                $item = $app->itemRead($res['f'], $res['i']);
                $header = '';
                if ($item && isset($item['header'])) {
                    if ((array)$item['header'] === $item['header']) {
                        @$header = isset($item['header'][$lang]) ? $item['header'][$lang] : $item['header']['ru'];
                    } else {
                        @$header = $item['header'];
                    }
                    $app->route->url == $path ? $path = '' : null;
                    $result[] = [
                        'path' => $path,
                        'header' => $header
                    ];
                }
            }
        }
        return $result;
    }


    function yongerFurl($item = null, $fld = 'header') {
        $app = $_ENV['app'];
        $item == null ? $item = $_ENV['_context'] : null;
        $item = (array)$item;
        isset($item['name']) ? null : $item['name'] = null;
        isset($item['header']) ? null : $item['header'] = $item['name'];
        $base = @wbFurlGenerate($item[$fld]);
        if ($item['_form'] == 'pages') {
            $url = $item['path'] . '/' . $base;
        } else {
            $url = '/'.$item['_form'] . '/' . $base;
        }
        if (isset($item['blocks']) && (array)$item['blocks'] === $item['blocks']) {
            foreach ($item['blocks'] as $block) {
                $dot = $app->dot();
                $dot->set($block);
                if ($dot->get('name') == 'seo' && $dot->get('active') == 'on' && $dot->get('alturl') > '') {
                    $url = $block['alturl'];
                    break;
                }
            }
        }
        return $url;
    }