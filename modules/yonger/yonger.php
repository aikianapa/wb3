<?php

// Author: oleg_frolov@mail.ru
require_once __DIR__ . '/yonger_page.php';

class modYonger
{
    public $app;
    public $dom;
    public $type;
    public $tables;
    public $count;
    public $map;
    public $list;
    public $file;

    public function __construct($obj)
    {
        if (wbIsDom($obj)) {
            $app = &$obj->app;
            $mode = $obj->params->mode;
            $this->dom = &$obj;
            $this->type = 'dom';
        } else {
            $app = &$obj;
            $mode = $app->route->mode;
            $this->type = 'app';
        }
        $app->yonger = &$this;

        if ($app->vars('_sett.modules.yonger') == null) {
            $app->vars('_sett.modules.yonger', ['allow' => 'admin', 'standalone' => 'on']);
            $sett = $app->itemRead('_settings', 'settings');
            $sett['modules'] = $app->vars('_sett.modules');
            $app->itemSave('_settings', $sett);
        }

        in_array($mode, explode(',', 'render,workspace,logo,signin,signup,signrc,createSite,blockpreview')) ? null : $app->apikey('module');
        if (in_array($mode, explode(',', 'createSite')) and $app->getDomain($app->route->refferer) !== $app->route->domain) {
            echo json_encode(['error'=>true,'msg'=>'Access denied']);
            exit;
        }
        $this->app = &$app;
        if (method_exists($this, $mode)) {
            $res = $this->$mode();
            echo $res;
        } elseif (!wbCheckBacktrace("wbModuleClass")) {
            $form = $app->controller('form');
            echo $form->get404();
            exit;
        }
    }

    public function yonmap()
    {
        $app = &$this->app;
        $this->tables = $app->tableList();
        $this->count = 0;
        $this->map = [];
        $this->list = $this->app->itemList('pages', ['return' => 'id,name,_lastdate,_form,header,active,attach,attach_filter,url,path,_sort,blocks']);
        $this->list = $this->list['list'];
        $this->yonmapnest();
        $app->putContents($app->vars('_env.dba') . '/_yonmap.json', json_encode($this->map,JSON_UNESCAPED_UNICODE));
        if ($app->vars('_sett.modules.yonger.sitemapxml') == 'on') {$this->sitemapxml();}
        header("Content-type:application/json");
        echo json_encode(['count'=>count($this->map)]);
        exit;
    }

    private function yonmapnest($path = '')
    {
        $this->count++;
        if ($this->count > 1000) {
            return;
        }
        $level = $this->app->json($this->list)->where('path', '=', $path)->sortBy('_sort')->get();
        $count = count($level);
        if (!$count) {
            return '';
        }
        foreach ($level as $item) {
            in_array($item['url'], ['/', '']) ? $url = '/'.$item['name'] : $url = $item['path'].'/'.$item['name'];
            if (isset($item['blocks']) && (array)$item['blocks'] === $item['blocks']) {
                foreach ($item['blocks'] as $block) {
                    $dot = $this->app->dot();
                    $dot->set($block);
                    if ($dot->get('name') == 'seo' && $dot->get('active') == 'on' && $dot->get('alturl') > '') {
                        $item['url'] = $url = $block['alturl'];
                    }
                }
            }
            $md5 = md5($url);
            unset($this->list[$item['id']]);
            $attach = (isset($item['attach']) and $item['attach'] > ' ') ? true : false;
            $res1 = $res2 = null;
            $res1 = $this->yonmapnest($url);
            $header = (array)$item['header'] === $item['header'] ? $item['header'][$this->app->vars('_sess.lang')] : $item['header'];
            $active = @$item['active'] == 'on' ? 'on' : '';
            $gmtime = new DateTime($item['_lastdate']);
            $gmtime->setTimezone(new DateTimeZone("Europe/Moscow"));
            $gmtime = $gmtime->format("c");
            substr($item['id'], 0, 1) == '_' or isset($this->map[$md5]) ? null : $this->map[$md5] = ['f' => $item['_form'], 'i' => $item['id'], 'u' => $url, 'n' => $item['name'],'h'=>$header, 'a'=>$active, 'd'=>$gmtime];
            $res2 = $attach ? $this->yonmaptable($item, $url) : null;
        }
    }
    private function yonmaptable($item, $path = '')
    {
        $table = $item['attach'];
        $filter = (isset($item['attach_filter']) && $item['attach_filter'] > '') ? $item['attach_filter'] : [];
        if (is_string($filter)) {
            $filter = str_replace("'", '"', $filter);
            $filter = json_decode($filter, true);
        }
        $options = [
            'return' => 'id,name,_form,_lastdate,header,active,tags,blocks',
            'filter' => $filter
        ];
        $class = $this->app->formClass($table);
        $level = $this->app->itemList($table, $options);
        $level = $level['list'];
        foreach ($level as $key => $item) {
            method_exists($class, 'beforeItemShow') ? $class->beforeItemShow($item) : null;
            isset($item['name']) ? null : $item['name'] = null;
            isset($item['header']) ? null : $item['header'] = $item['name'];
            if ((array)$item['header'] === $item['header'] && isset($item['header']['ru'])) {
                $item['header'] = $item['header']['ru'];
            } else if ((array)$item['header'] === $item['header']) {
                $item['header'] = array_shift($item['header']);
            }
            $item['_form'] = $table;
            $header = '';
            if ($item['header']) {
                $header = (array)$item['header'] === $item['header'] ? $item['header'][$this->app->vars('_sess.lang')] : $item['header'];
                $active = @$item['active'] == 'on' ? 'on' : '';
                $item['path'] = $path;
                $item['name'] = wbFurlGenerate($item['header']);
                $item['url'] = $item['path'] . '/' . $item['name'];
                if (isset($item['blocks']) && (array)$item['blocks'] === $item['blocks']) {
                    foreach ($item['blocks'] as $block) {
                        $dot = $this->app->dot();
                        $dot->set($block);
                        if ($dot->get('name') == 'seo' && $dot->get('active') == 'on' && trim($dot->get('alturl')) > ' ') {
                            $item['url'] = $block['alturl'];
                        }
                    }
                }
                $level[$key] = $item;
                $md5 = md5($item['url']);
                if (!isset($this->map[$md5])) {
                    $gmtime = new DateTime($item['_lastdate']);
                    $gmtime->setTimezone(new DateTimeZone("Europe/Moscow"));
                    $gmtime = $gmtime->format("c");
                    $this->map[$md5] = ['f' => $item['_form'], 'i' => $item['id'], 'u' => $item['url'], 'n' => $item['name'], 'h' => $header, 'a'=>$active, 'd' => $gmtime];
                }
            } else {
                unset($level[$key]);
            }
        }
    }
    public function workspace()
    {
        $app = $this->app;

        $subdom = $app->route->subdomain;
        if ($subdom > '' && $app->vars('_post.token') > '' && $app->vars('_post.login') > '') {
            $tok = file_get_contents($app->vars('_env.path_app').'/database/_token.json');
            $tok = json_decode($tok);
            if ($tok->token == $app->vars('_post.token') && $tok->login == $app->vars('_post.login')) {
                $user = $app->itemList('users', ['filter'=>[
                    'active' => 'on',
                    'login' => $tok->login,
                    'role' => 'admin',
                    'default' => true
                ]]);
                $user = array_pop($user['list']);
                $app->login($user);
            }
        }

        $user = $app->vars('_sess.user');
        $login = $app->vars('_sess.user.login');
        $role = $app->vars('_sess.user.role');

        if ($app->vars('_sett.modules.yonger.standalone') !== 'on') {
            if ($subdom == '' and ($login == '' or $role !== 'user')) {
                $form = $app->controller('form');
                return $form->get404();
            } elseif ($login == '_new') {
                $master = $ws = $app->fromFile(__DIR__."/tpl/master.php", true);
                $master->path = $ws->path = __DIR__ . '/tpl/';
                $master->fetch();
                return $master;
            }
        } else {
            $dir = $app->vars('_env.path_app').'/forms/pages';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/pages', $dir);
            $dir = $app->vars('_env.path_app').'/forms/news';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/news', $dir);
            $dir = $app->vars('_env.path_app').'/forms/users';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/users', $dir);
            $dir = $app->vars('_env.path_app').'/forms/quotes';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/quotes', $dir);
            $dir = $app->vars('_env.path_app').'/forms/comments';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/comments', $dir);
            $file = $app->vars('_env.path_app').'/functions.php';
            is_file($file) ? null : copy(__DIR__ .'/common/scripts/functions.php', $file);
        }
        if (is_file($app->route->path_app.'/tpl/workspace.php')) {
            $ws = $app->fromFile($app->route->path_app.'/tpl/workspace.php', true);
            $ws->path = $app->route->path_app . '/tpl/';
        } else {
            $ws = $app->fromFile(__DIR__."/tpl/workspace.php", true);
        }
        $ws->path = __DIR__ . '/tpl/';
        $ws->fetch();
        return '<!DOCTYPE html>'.$ws->outer();
    }

    public function pageselect()
    {
        if ($this->type == 'app') {
            // return list to json;
            /*
            if ($this->app->vars('_env.cache.yonpageselect')) {
                $list = $this->app->vars('_env.cache.yonpageselect');
            } else {
                $list = $this->app->itemList('pages', [
                    'sort' => 'url',
                    'return' => 'id,active,url,header,name,_site',
                    'filter'=> ['active'=>'on','_site' => ['$in'=> [null,$this->app->vars('_sett.site')]], 'id'=> ['$nin'=>['_header','_footer']]]
                ]);
                $list = array_values($list['list']);
                array_walk($list, function (&$item, $key) {
                    wbIsJson($item['header']) ? $item['header'] = json_decode($item['header'], true) : null;
                    (array)$item['header'] === $item['header'] ? $item['header'] = $item['header'][$this->app->vars('_sess.lang')] : null;
                });
                $this->app->vars('_env.cache.yonpageselect', $list);
            }*/
            $list = file_get_contents($this->app->vars('_route.path_app').'/database/_yonmap.json');
            $list = json_decode($list,true);
            $home = md5('/home');
            isset($list[$home]) ? $list[$home]['u'] = '/' : null;
            $list = array_values($list);
            $list = wbArraySort($list, "h:a");
            header("Content-type: application/json; charset=utf-8");
            return $this->app->jsonEncode($list);
        } else {
            // return input selector
            if (!$this->dom->is('input')) {
                $this->dom->after("<div class='alert alert-warning'>Требуется тэг input</div>");
            } else {
                $ui = $this->app->fromFile(__DIR__.'/common/modules/yonpageselect/yonpageselect_ui.php');
                $this->dom->removeAttr('wb');
                $this->dom->removeAttr('wb-module');
                $class = $this->app->attrToArray($this->dom->attr('class'));
                foreach ($class as $i => $c) {
                    if ($c == 'col' || substr($c, 0, 4) == 'col-') {
                        $ui->find('.input-group')->addClass($c);
                        unset($class[$i]);
                    }
                }
                $this->dom->attr('class', implode(' ', $class));
                $this->dom->params('url') > '' ? $this->dom->attr('data-url', $this->dom->params('url')) : null;
                $ui->find('.input-group > input')->remove();
                $ui->find('.input-group')->append($this->dom->outer());
                $this->dom->after($ui);
            }
        }
        $this->dom->remove();
    }

    public function structure()
    {
        $yp = new yongerPage($this->dom);
        $out = $this->app->fromString($yp->list());
        $preset = $this->dom->params('preset');
        if ($this->app->route->id == '_new' && $preset > '') {
            $out->find('[name=preset]')->attr('value', $preset);
            $preset = $this->preset_get($preset);
            $out->find('textarea[name=blocks]')->inner(json_encode($preset));
        }
        $this->dom->after($out);
        $this->dom->remove();
    }

    public function getblock(&$file = null)
    {
        if ($file == null) {
            return '';
        }
        strpos(' '.$file, '_yonger_') ? $file = str_replace('/_yonger_', __DIR__, $file) : null;
        strpos(' '.$file, '_app_') ? $file = str_replace('/_app_', $this->app->route->path_app, $file) : null;
        is_file($file) ? null : $file = __DIR__ .'/common/blocks/'.$file;
        return file_get_contents($file);
    }

    public function editblock()
    {
        $file = $this->app->vars('_post.form');
        $form = $this->getblock($file);
        $form = $this->app->fromString($form);
        $out  = $this->app->fromFile(__DIR__.'/common/forms/pages/editblock.php');
        $out->item = [
            'edit'=>$form->find('edit')->inner(),
            'view'=>$form->find('view')->inner()
        ];
        $out->fetch();
        return $out;
    }

    public function editblocksave()
    {
        $file = $this->app->vars('_post.form');
        $form = $this->getblock($file);
        $form = $this->app->fromString($form);
        $form->find('edit')->html(base64_decode($this->app->vars('_post.edit')));
        $form->find('view')->html(base64_decode($this->app->vars('_post.view')));
        $data = $form->outer();
        $res = file_put_contents($file, $data);
        if ($res) {
            return '{"error":false}';
        } else {
            return '{"error":true}';
        }
    }


    public function blocklist()
    {
        header("Content-type: application/json; charset=utf-8");
        $yp = new yongerPage();
        $res = $yp->list();
        return json_encode($res);
    }

    public function blockform()
    {
        if (!isset($this->dom)) {
            $ypg = new yongerPage("");
        } else {
            $ypg = new yongerPage($this->dom);
        }
        return $ypg->blockform($this->app->vars('_post.item'));
    }

    public function blockview($item)
    {
        $form = $item['form'];
        $ypg = new yongerPage($this->dom);
        $res = $ypg->blockview($form);
        if (!$res) {
            $result = (object)['head'=>false, 'body'=>false, 'result'=>null];
            $result->result = $this->dom->app->fromString('<!-- Form '.$form.' not found -->');
            return $result;
        }
        (isset($item['container']) && $item['container'] == 'on') ? $res->children()->addClass('container') : null;
        isset($item['lang']) ? $data = array_merge($item, $item['lang'][$this->app->vars('_sess.lang')]) : $data = &$item;
        $result = (object)$res->attributes();
        $res->fetch($data); // не удалять, иначе слюстрока не работает как нужно... шайтанама! :(
        if ($this->dom->app->vars('_sett.devmode') == 'on' && !$res->is('script')) {
            $res->prepend('<!-- Is block: '.$form.' -->');
        }
        $section = $this->dom->app->fromString('<html>'.$res->fetch($data)->inner().'</html>');
        isset($item['block_id']) && $item['block_id'] ? $section->children()->children(':first-child')->attr('id', $item['block_id']) : null;
        isset($item['block_class']) && $item['block_class'] ? $section->children()->children(':first-child')->addClass($item['block_class']) : null;
        if (isset($result->head)) {
            $result->head = $section->inner();
        } elseif ($section->find('head')) {
            $result->head = $section->find('head')->inner();
            $section->find('head')->remove();
        }
        if ($section->find('body') or isset($result->body)) {
            $result->body = $section->find('body');
            $section->find('body')->remove();
        }
        @$section->children()->prepend("<div content='pageMenuAnchor' id='blk{$item["id"]}'></div>");
        $result->result = $section->inner();
        return $result;
    }

    public function blockpreview()
    {
        $tpl = $this->app->getTpl('page.php');
        $block = $this->app->vars('_get.block');
        $ypg = new yongerPage($this->dom);
        $form = $ypg->blockfind($block);
        $preview = $ypg->blockpreview($form);
        if ($preview) {
            $preview->fetch();
            $tpl->find('wb-module[wb="module=yonger&mode=render"]')->after($preview);
        } else {
            $tpl->find('wb-module[wb="module=yonger&mode=render"]')->attr("wb", "module=yonger&mode=render&view={$block}");
        }
        $tpl->fetch();
        echo $tpl;
        exit;
    }

    public function block()
    {
        $app = &$_ENV['app'];
        $this->app = $app;
        $this->dom = $app->fromString('<html></html>');
        $this->dom->params = (object)['view'=>$app->vars('_route.params.0')];
        $this->dom->item = $app->vars('_post.item');
        $this->render();
        echo($this->dom->outer());
        exit;
    }

    public function copypage()
    {
        header("Content-type: application/json; charset=utf-8");
        $app = $this->app;
        $item = $app->itemRead('pages', $app->vars('_post.item'));
        if ($item) {
            $item['_id'] = $app->newId();
            $max = 30;
            $flag = false;
            while ($flag == false and $max > 0) {
                $item['name'] == '' ? $item['name'] = 'home_copy' : $item['name'] = $item['name'].'_copy';
                $check = $app->itemList('pages', ['filter'=>[
                '_site'=>$app->vars('_sett.site'),
                '_login'=>$app->vars('_sett.login'),
                'name'=>$item['name'],
                'path' => $item['path']
                ]]);
                intval($check['count']) == 0 ? $flag = true : $max--;
            }
            if ($flag == false) {
                return '{"error":true}';
            }
            $item['active'] = '';
            $item = $app->itemSave('pages', $item);
            echo json_encode($item);
        } else {
            echo '{"error":true}';
        }
        exit;
    }


    public function edit()
    {
        $dom = &$this->dom;
        $app = &$dom->app;
        $ypg = new yongerPage($this->dom);
        $form = $ypg->blockfind($dom->params('block'));
        $out = $ypg->blockedit($form);
        if ($out) {
            $out->fetch($dom->item);
            $dom->append($out);
        }
    }


    public function render()
    {
        $dom = &$this->dom;
        if (isset($dom->done)) return;
        $app = &$dom->app;
        $item = &$dom->item;
        $view = $dom->params('view');
        if (!in_array($view,['header','footer']) && $view > '') {
            $ypg = new yongerPage($this->dom);
            $form = $ypg->blockfind($dom->params('view'));
            $item['blocks'] = [];
            $item['blocks'][0] = ['id'=>wbNewId(),'form'=>$form,'active'=>'on', 'name'=>$dom->params('view')];
        } else {
            $dom->params('view') == 'header' ? $item = $app->itemRead('pages', '_header') : null;
            $dom->params('view') == 'footer' ? $item = $app->itemRead('pages', '_footer') : null;
        }
        isset($item['blocks']) ? $blocks = (array)$item['blocks'] : $item['blocks'] = [];
        $blocks = (array)$item['blocks'];
        $html = $dom->parents(':root');
        $html->find('head')->length ? null : $html->prepend('<head></head>');
        $html->find('body')->length ? null : $html->append('<body></body>');

        $head = $html->find('head');
        $body = $html->find('body');

        foreach ($blocks as $block) {
            if ($block === (array)$block) {
                //$start = microtime();
                isset($block['active']) ? null : $block['active'] = '';
                if ($block['active'] == 'on') {
                    $block['_parent'] = $app->objToArray($item);
                    $res = $this->blockview($block);
                    if ($res->head) {
                        $head->append($res->head);
                    } else {
                        $head->length && isset($res->head) ? $head->append($res->head) : null;
                        $body->length && isset($res->body) ? $body->append($res->body) : null;
                        $dom->before($res->result);
                    }
                }
                //$time = (microtime()-$start);
                //echo "<p>".$block['name'].' '.$time."</p>\n";
            }
        }
        $dom->done = true;
        $dom->remove();
    }

    public function logo()
    {
        $aLogo = $this->app->route->path_app . '/tpl/assets/img/logo.svg';
        $sLogo = $this->app->route->path_app . $this->app->vars('_sett.logo.0.img');
        $eLogo = __DIR__. '/tpl/assets/img/logo.svg';
        if (is_file($sLogo)) {
            @header('Content-type: '.wbMime($sLogo));
            return file_get_contents($sLogo);
        } elseif (is_file($aLogo)) {
            header('Content-type: image/svg+xml');
            return file_get_contents($aLogo);
        } else {
            header('Content-type: image/svg+xml');
            return file_get_contents($eLogo);
        }
        exit;
    }

    public function signin()
    {
        $form = $this->app->route->path_app . '/tpl/signin.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/signin.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }

    public function signup()
    {
        $form = $this->app->route->path_app . '/tpl/signup.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/signup.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }

    public function signrc()
    {
        $form = $this->app->route->path_app . '/tpl/signrc.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/signrc.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }

    public function support()
    {
        $form = $this->app->route->path_app . '/tpl/support.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/support.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }


    public function goto()
    {
        $app = &$this->app;
        $sid = $app->route->params[0];
        $login = $app->vars('_sess.user.login');
        $path = $app->vars('_env.path_app').'/sites/'.$sid;
        $app->route->subdomain == '' ? null : $path = realpath($app->vars('_env.path_app').'/../'.$sid);
        $token = md5($_SESSION['token'].time());
        file_put_contents($path.'/database/_token.json', json_encode(['token'=>$token,'login'=>$login]));
        header("Content-type: application/json; charset=utf-8");
        return json_encode([
            'goto' => $app->route->scheme . '://' . $sid . '.' . $app->route->domain . '/workspace',
            'token' => $token
        ]);
    }

    public function removeSite()
    {
        $app = &$this->app;
        $this->setMainDba();
        $sid = $app->route->params[0];

        $allow = false;
        $user = &$app->vars('_sess.user');
        $path = $app->vars('_env.path_app').'/sites/'.$sid;
        $res = ['error'=>true,'msg'=>'Ошибка удаления сайта'];
        $self = false;
        if ($app->route->subdomain > '') {
            $sid == $app->route->subdomain ? $self = true : null;
            $site = $app->itemRead('sites', $sid);
            $site && $site['login'] == $user['login'] ? $allow = true : null;
            $path = realpath($app->vars('_env.path_app').'/../'.$sid);
        } else {
            $path = $app->vars('_env.path_app').'/sites/'.$sid;
            $allow = true;
        }

        if ($allow) {
            $app->recurseDelete($path);
            $app->itemRemove('sites', $sid);
            $res = ['error'=>false,'msg'=>'Сайт удалён', 'self'=>$self];
        } else {
            $res = ['error'=>true,'msg'=>'Ошибка удаления сайта'];
        }
        header("Content-type: application/json; charset=utf-8");
        return json_encode($res);
    }

    public function createSite()
    {
        $app = &$this->app;
        $site = $app->vars('_post');
        $dirmod = dirname(__DIR__ .'..');
        if (isset($site['url'])) {
            $form = $this->app->fromFile(__DIR__ . '/tpl/create_site.php');
            return $form->fetch();
        } else {
            $res = false;
            $this->setMainDba();
            isset($site['login']) ? null : $site['login'] = $app->vars('_sess.user.login');
            $user = $app->itemList('users', ['filter'=>[
                'active' => 'on',
                'login' => $site['login'],
                'role' => 'user',
            ]]);
            $user = array_pop($user['list']);
            if ($user) {
                isset($user['sitenum']) ? $sitenum = intval($user['sitenum'])+1 : $sitenum = 1;
                $sid = $site['login'].'-'.$sitenum;
                $uid = $user['id'];
                $site['id'] = $sid;
                $app->login($user);
                $path = $app->vars('_env.path_app').'/sites/'.$sid;
                $hosts = $app->vars('_env.path_app').'/sites/hosts';
                is_dir($path) ? null : mkdir($path, 0777, true);
                is_dir($hosts) ? null : mkdir($hosts, 0777, true);
                foreach (['database','uploads','modules'] as $dir) {
                    is_dir($path.'/'.$dir) ? null : mkdir($path.'/'.$dir, 0777, true);
                }
                symlink($app->vars('_env.path_engine'), $path.'/engine');
                symlink(__DIR__, $path.'/modules/yonger');
                symlink($dirmod.'/phonecheck', $path.'/modules/phonecheck');
                symlink(__DIR__ .'/common/tpl', $path.'/tpl');
                symlink(__DIR__ .'/common/forms', $path.'/forms');
                symlink(__DIR__ .'/common/scripts/functions.php', $path.'/functions.php');

                copy($app->vars('_env.path_engine').'/index.php', $path. '/index.php');
                $domain = $app->route->domain;
                $this->createSiteUser($path);
                file_put_contents($hosts.'/.domainname', $domain);
                $tmp = $app->itemSave('users', ['id'=>$uid,'sitenum'=>$sitenum]);
                $settings = json_encode([
                    'settings' => [
                        'id'       => 'settings'
                        ,'header'   =>  $site['name']
                        ,'email'    =>  $user['email']
                        ,'login'    =>  $site['login']
                        ,'site'     =>  $site['id']
                        ,'locales'  =>  'ru,en'
                        ,'devmode'  => 'on'
                    ]
                ]);
                file_put_contents($path.'/database/_settings.json', $settings);

                $res = $app->itemSave('sites', $site);
                file_put_contents($hosts.'/'.$sid, null);
                header("Content-type: application/json; charset=utf-8");
            }
            if ($res) {
                $this->app->login($res);
                return json_encode(['error'=>false,'msg'=>'Сайт успешно создан']);
            } else {
                return json_encode(['error'=>true,'msg'=>'Ошибка создания сайта']);
            }
        }
    }

    public function createSiteUser($path)
    {
        $app = &$this->app;
        $user = $this->getMainUser();
        $uid = $user['id'];
        //$uid = $app->vars('_sess.user.id');
        // тут нужно не текущего пользователя брать, а того, который в yonger регился
        $users = $app->itemList('users', ['filter'=>[
            'active'=>'on',
            '$or'=> [
                ['isgroup' => 'on'],
                ['id' => $uid]
            ]
        ]]);
        $users = $users['list'];
        $users[$uid]['role'] = 'admin';
        $users[$uid]['default'] = true;
        $users = json_encode($app->arrayToObj($users));
        file_put_contents($path.'/database/users.json', $users);
    }

    public function getMainUser($login = null)
    {
        $app = &$this->app;
        if (!$login) {
            $login = $app->vars('_sess.user.login');
        }
        $user = $app->itemList('users', ['filter'=>[
            'active' => 'on',
            'login' => $login,
            'role' => 'user'
        ]]);
        $user = array_pop($user['list']);
        return $user;
    }

    public function listSites()
    {
        $this->setMainDba();
        $list = $this->app->fromFile(__DIR__ . '/tpl/list_sites.php');
        return $list->fetch();
    }


    public function finishRegistration()
    {
        header("Content-type: application/json; charset=utf-8");
        $user = $this->app->vars('_post');
        $user['id'] = $this->app->user->id;
        //unset($user['_login']);
        $res = $this->app->itemSave('users', $user);
        if ($res) {
            $this->app->login($res);
            return json_encode(['error'=>false]);
        } else {
            return json_encode(['error'=>true]);
        }
    }

    public function settings()
    {
        $app = $this->app;
        $out = $app->getForm('_settings', $app->vars("_route.form"));
        if ($out !== null) {
            $out->fetch();
        } else {
            $out = "Error: /forms/_settings/{$app->vars("_route.form")}.php not found!";
        }
        echo $out;
        exit;
    }

    public function setMainDba()
    {
        $app = &$this->app;
        $dba = $this->app->vars('_env.dba');
        if ($this->app->route->subdomain > '') {
            $dba = str_replace('/sites/'.$this->app->route->subdomain, '', $dba);
            $this->app->vars('_env.dba', $dba);
        }
    }

    /*
    function create_site() {
        $app = $this->app;
        $login = $this->main_login();
        if ($login) {
            $site = $app->vars('_post.formdata');
            $site['login'] = $login;
            $site = $app->itemSave('sites',$site);
            header("Content-type: application/json; charset=utf-8");
            if ($site) {
                return json_encode(['error'=>false,'data'=>$site]);
            } else {
                return json_encode(['error'=>true,'msg'=>'Неизвестная ошибка']);
            }
        } else {
            return json_encode(['error'=>true,'msg'=>'Запрещено для данного пользователя']);
        }
    }
    */
    public function main_login()
    {
        $user = $this->app->vars('_user');
        $login = false;
        if (isset($user['login']) && $user['login'] > '' && $user['active'] == 'on') {
            $login = $user['login'];
        } else {
        }
        return $login;
    }

    public function presets()
    {
        $res = null;
        if (isset($this->app->route->params[0])) {
            $this->file = $this->app->vars('_env.path_app').'/blocks/presets.json';
            $json = is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];
            $func = 'presets_'.$this->app->route->params[0];
            $res = method_exists($this, $func) ? $this->$func($json) : null;
        }
        header("Content-type: application/json; charset=utf-8");
        return json_encode($res);
    }

    public function presets_list($json)
    {
        //$res = $this->app->arrayToObj($json);
        $res = array_values($json);
        return $res;
    }

    public function presets_save($json)
    {
        $name = $this->app->vars('_post.name');
        $id = $this->app->furlGenerate($name);
        $item = ['blocks'=>$this->app->vars('_post.blocks'),'name'=>$name,'id'=>$id];
        $json[$id] = $item;
        $json = $this->app->itemToArray($json);
        $json = $this->app->jsonEncode($json);
        $this->app->putContents($this->file, $json);
        return $item;
    }

    public function preset_get($name)
    {
        $this->file = $this->app->vars('_env.path_app').'/blocks/presets.json';
        $json = is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];

        if (isset($json[$name])) {
            return $json[$name]['blocks'];
        } else {
            return [];
        }
    }

    public function sitemapxml()
    {
        $app = &$this->app;
        $yonmap = file_get_contents($app->vars('_env.dba') . '/_yonmap.json');
        $yonmap = json_decode($yonmap, true);
        $nr = "\n";
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

        foreach ($yonmap as $item) {
            if ($item['a'] == 'on') {
                $item['u'] == '/home' ? $item['u'] = '/' : 0;
                $priority = $item['u'] == '/' ? 1 : 1;
                $xml .= "<url>{$nr}<loc>" . htmlspecialchars($app->route->host . $item['u']) . "</loc>{$nr}<lastmod>{$item['d']}</lastmod>{$nr}<priority>{$priority}</priority>{$nr}</url>{$nr}";
            }
        }
        $xml .= "</urlset>{$nr}";
        file_put_contents($app->route->path_app . '/sitemap.xml', $xml);
        if ($app->route->mode == 'sitemapxml') {
            header("Content-Type: text/xml");
            header("Cache-Control: no-cache, must-revalidate");
            header("Cache-Control: post-check=0,pre-check=0");
            header("Cache-Control: max-age=0");
            header("Pragma: no-cache");
            echo $xml;
            exit;
        }
    }
}
