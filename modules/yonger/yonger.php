<?php
// Author: oleg_frolov@mail.ru
require_once __DIR__ . '/yonger_page.php';

class modYonger
{
    private $app;
    private $dom;
    private $type;

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

        in_array($mode,explode(',','render,workspace,logo,signin,signup,signrc,createSite,blockpreview'))? null : $app->apikey('module');
        if (in_array($mode,explode(',','createSite')) AND $app->getDomain( $app->route->refferer) !== $app->route->domain ) {
            echo json_encode(['error'=>true,'msg'=>'Access denied']);
            die;
        }
        $this->app = &$app;
        if (method_exists($this, $mode)) {
            echo $this->$mode();
        } else if (!wbCheckBacktrace("wbModuleClass")) {
            $form = $app->controller('form');
            echo $form->get404();
        }
    }

    public function workspace()
    {
        $app = $this->app;

        $subdom = $app->route->subdomain;
        if ($subdom > '' && $app->vars('_post.token') > '' && $app->vars('_post.login') > '') {
            $tok = file_get_contents( $app->vars('_env.path_app').'/database/_token.json');
            $tok = json_decode($tok);
            if ($tok->token == $app->vars('_post.token') && $tok->login == $app->vars('_post.login')) {
                $user = $app->itemList('users',['filter'=>[
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
            if ($subdom == '' AND ($login == '' OR $role !== 'user')) {
                $form = $app->controller('form');
                return $form->get404();
            } else if ($login == '_new') {
                $master = $ws = $app->fromFile(__DIR__."/tpl/master.php", true);
                $master->path = $ws->path = __DIR__ . '/tpl/';
                $master->fetch();
                return $master;
            }
        } else {
            $dir = $app->vars('_env.path_app').'/forms/pages';
            is_file($dir) OR is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/pages' , $dir );
            $dir = $app->vars('_env.path_app').'/forms/news';
            is_file($dir) OR is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/news' , $dir );
            $dir = $app->vars('_env.path_app').'/forms/users';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/users', $dir);
            $dir = $app->vars('_env.path_app').'/forms/quotes';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/quotes', $dir);
            $dir = $app->vars('_env.path_app').'/forms/comments';
            is_file($dir) or is_dir($dir) ? null : symlink(__DIR__ .'/common/forms/comments', $dir);
            $file = $app->vars('_env.path_app').'/functions.php';
            is_file($file) ? null : symlink(__DIR__ .'/common/scripts/functions.php' , $file);
            $file = $app->vars('_env.path_app').'/tpl/page.php';
            is_file($file) ? null : symlink(__DIR__ .'/common/tpl/page.php' , $file);
        }
        $ws = $app->fromFile(__DIR__."/tpl/workspace.php", true);
        $ws->path = __DIR__ . '/tpl/';
        $ws->fetch();
        return '<!DOCTYPE html>'.$ws->outer();
    }

    function pageselect() {
        if ($this->type == 'app') {
            // return list to json;
            if ($this->app->vars('_env.cache.yonpageselect')) {
                $list = $this->app->vars('_env.cache.yonpageselect');
            } else {
                $list = $this->app->itemList('pages', [
                    'sort' => 'url',
                    'return' => 'id,active,url,header,_site',
                    'filter'=> ['active'=>'on','_site' => ['$in'=> [null,$this->app->vars('_sett.site')]], 'id'=> ['$nin'=>['_header','_footer']]]
                ]);
                $list = array_values($list['list']);
                array_walk($list, function(&$item,$key){
                    $item['header'] = $item['header'][$this->app->vars('_sess.lang')];
                });
                $this->app->vars('_env.cache.yonpageselect', $list);
            }
            header("Content-type: application/json; charset=utf-8");
            return $this->app->jsonEncode($list);
        } else {
            // return input selector
            if (!$this->dom->is('input')) {
                $this->dom->after("<div class='alert alert-warning'>Требуется тэг input</div>");
            } else {
                $ui = $this->app->fromFile($this->app->route->path_engine.'/modules/yonger/tpl/pageselect_ui.php');
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

    function structure() {
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

    function getblock(&$file = null) {
        if ($file == null) return '';
        strpos(' '.$file, '_yonger_') ? $file = str_replace('/_yonger_', __DIR__, $file) : null;
        strpos(' '.$file, '_app_') ? $file = str_replace('/_app_', $this->app->route->path_app, $file) : null;
        is_file($file) ? null : $file = __DIR__ .'/common/blocks/'.$file;
        return file_get_contents($file);
    }
    
    function editblock() {
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

    function editblocksave() {
        $file = $this->app->vars('_post.form');
        $form = $this->getblock($file);
        $form = $this->app->fromString($form);
        $form->find('edit')->html(base64_decode($this->app->vars('_post.edit')));
        $form->find('view')->html(base64_decode($this->app->vars('_post.view')));
        $data = $form->outer();
        $res = file_put_contents($file,$data);
        if ($res) {
            return '{"error":false}';
        } else {
            return '{"error":true}';
        }
    }


    function blocklist() {
        header("Content-type: application/json; charset=utf-8");
        $yp = new yongerPage();
        $res = $yp->list();
        return json_encode($res);
    }

    function blockform() {
        if (!isset($this->dom)) {
            $ypg = new yongerPage("");    
        } else {
            $ypg = new yongerPage($this->dom);
        }
        return $ypg->blockform($this->app->vars('_post.item'));
        
    }

    function blockview($item) {
        $form = $item['form'];
        $ypg = new yongerPage($this->dom);
        $res = $ypg->blockview($form);
        if (!$res) {
            $result = (object)['head'=>false, 'body'=>false, 'result'=>null];
            $result->result = $this->dom->app->fromString('<!-- Form '.$form.' not found -->');
            return $result;
        }
        (isset($item['container']) && $item['container'] == 'on') ? $res->children()->addClass('container') : null;
        isset($item['lang']) ? $data = array_merge($item,$item['lang'][$this->app->vars('_sess.lang')]) : $data = &$item;
        $result = (object)$res->attributes();
        $res->fetch($data); // не удалять, иначе слюстрока не работает как нужно... шайтанама! :(
        if ($this->dom->app->vars('_sett.devmode') == 'on' && !$res->is('script')) {
            $res->prepend('<!-- Is block: '.$form.' -->');
        }
        $section = $this->dom->app->fromString('<html>'.$res->fetch($data)->inner().'</html>');
        isset($item['block_id']) && $item['block_id'] ? $section->children()->children(':first-child')->attr('id',$item['block_id']) : null;
        isset($item['block_class']) && $item['block_class'] ? $section->children()->children(':first-child')->addClass($item['block_class']) : null;
        if (isset($result->head)) {
            $result->head = $section->inner();
        } else if ($section->find('head')) {
            $result->head = $section->find('head')->inner();
            $section->find('head')->remove();
        }
        if ($section->find('body') or isset($result->body)) {
            $result->body = $section->find('body');
            $section->find('body')->remove();
        }
        $result->result = $section->inner();
        return $result;
    }

    function blockpreview() {
        $tpl = $this->app->getTpl('page.php');
        $block = $this->app->vars('_get.block');
        $ypg = new yongerPage($this->dom);
        $form = $ypg->blockfind($block);
        $preview = $ypg->blockpreview($form);
        if ($preview) {
            $preview->fetch();
            $tpl->find('wb-module[wb="module=yonger&mode=render"]')->after($preview);
        } else {
            $tpl->find('wb-module[wb="module=yonger&mode=render"]')->attr("wb","module=yonger&mode=render&view={$block}");            
        }
        $tpl->fetch();
        echo $tpl;
        die;
    }

    function block() {
        $app = &$_ENV['app'];
        $this->app = $app;
        $this->dom = $app->fromString('<html></html>');
        $this->dom->params = (object)['view'=>$app->vars('_route.params.0')]; 
        $this->dom->item = $app->vars('_post.item');
        $this->render();
        echo ($this->dom->outer());
        die;
    }

    function copypage() {
        header("Content-type: application/json; charset=utf-8");
        $app = $this->app;
        $item = $app->itemRead('pages',$app->vars('_post.item'));
        if ($item) {
            $item['_id'] = $app->newId();
            $max = 30;
            $flag = false;
            while($flag == false AND $max > 0) {
                $item['name'] == '' ? $item['name'] = 'home_copy' : $item['name'] = $item['name'].'_copy';
                $check = $app->itemList('pages', ['filter'=>[
                '_site'=>$app->vars('_sett.site'),
                '_login'=>$app->vars('_sett.login'),
                'name'=>$item['name'],
                'path' => $item['path']
                ]]);
                intval($check['count']) == 0 ?  $flag = true : $max--;
            }
            if ($flag == false) {return '{"error":true}';}
            $item['active'] = '';
            $item = $app->itemSave('pages', $item);
            return json_encode($item);
        } else {
            return '{"error":true}';
        }
    }


    function edit() {
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


    function render() {
        $dom = &$this->dom;
        $app = &$dom->app;
        $item = null;
        $dom->params('view') == 'header' ? $item = $app->itemRead('pages','_header') : null;
        $dom->params('view') == 'footer' ? $item = $app->itemRead('pages','_footer') : null;
        if ($item === null && $dom->params('view') > '') {
            $ypg = new yongerPage($this->dom);
            $form = $ypg->blockfind($dom->params('view'));
            $id = wbNewId();
            $item = $dom->item;
            $item['blocks'] = [];
            $item['blocks'][$id] = ['id'=>$id,'form'=>$form,'active'=>'on'];
        } else if ($item === null) {
            $item = $dom->item;
        }
        isset($item['blocks']) ? $blocks = (array)$item['blocks'] : $item['blocks'] = []; 
        $blocks = (array)$item['blocks'];
        $blocks = wbItemToArray($blocks);
        $html = $dom->parents(':root');
        $html->find('head')->length ? null : $html->prepend('<head></head>');
        $html->find('body')->length ? null : $html->prepend('<body></body>');

        $head = $html->find('head');
        $body = $html->find('body');
        foreach($blocks as $id => $block) {
            if ($block === (array)$block) {
                isset($block['active']) ? null : $block['active'] = '';
                //$dom->params('view') == 'header' ? $method = 'prepend' : $method = 'append';
                $method = 'append';
                if ($block['active'] == 'on') {
                    $block['_parent'] = $app->objToArray($item);
                    $res = $this->blockview($block);
                    if ($res->head) {
                        $head->$method($res->head);
                    } else {
                        $head->length && isset($res->head) ? $head->$method($res->head) : null;
                        $body->length && isset($res->body) ? $body->$method($res->body) : null;
                        $dom->$method($res->result);
                    }

                }
            }
        }
    }

    function logo() {
        $aLogo = $this->app->route->path_app . '/tpl/assets/img/logo.svg';
        $sLogo = $this->app->route->path_app . $this->app->vars('_sett.logo.0.img');
        $eLogo = __DIR__. '/tpl/assets/img/logo.svg';
        if (is_file($sLogo)) {
            header('Content-type: '.wbMime($sLogo));
            return file_get_contents($sLogo);
        } else         if (is_file($aLogo)) {
            header('Content-type: image/svg+xml');
            return file_get_contents($aLogo);
        } else {
            header('Content-type: image/svg+xml');
            return file_get_contents($eLogo);
        }
        die;
    }

    function signin() {
        $form = $this->app->route->path_app . '/tpl/signin.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/signin.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }

    function signup() {
        $form = $this->app->route->path_app . '/tpl/signup.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/signup.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }

    function signrc() {
        $form = $this->app->route->path_app . '/tpl/signrc.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/signrc.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }

    function support() {
        $form = $this->app->route->path_app . '/tpl/support.php';
        if (is_file($form)) {
            $form = $this->app->fromFile($form);
        } else {
            $form = $this->app->fromFile(__DIR__ . '/tpl/support.php');
        }
        $form->path = __DIR__ . '/tpl/';
        return $form->fetch();
    }


    function goto() {
        $app = &$this->app;
        $sid = $app->route->params[0];
        $login = $app->vars('_sess.user.login');
        $path = $app->vars('_env.path_app').'/sites/'.$sid;
        $app->route->subdomain == '' ? null : $path = realpath($app->vars('_env.path_app').'/../'.$sid);
        $token = md5($_SESSION['token'].time());
        file_put_contents( $path.'/database/_token.json', json_encode(['token'=>$token,'login'=>$login]));
        header("Content-type: application/json; charset=utf-8");
        return json_encode([
            'goto' => $app->route->scheme . '://' . $sid . '.' . $app->route->domain . '/workspace',
            'token' => $token
        ]);
    }

    function removeSite() {
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
            $site = $app->itemRead('sites',$sid);
            $site && $site['login'] == $user['login'] ? $allow = true : null;
            $path = realpath($app->vars('_env.path_app').'/../'.$sid);
        } else {
            $path = $app->vars('_env.path_app').'/sites/'.$sid;
            $allow = true;
        }

        if ($allow) {
            $app->recurseDelete($path);
            $app->itemRemove('sites',$sid);
            $res = ['error'=>false,'msg'=>'Сайт удалён', 'self'=>$self];
        } else {
            $res = ['error'=>true,'msg'=>'Ошибка удаления сайта'];
        }
        header("Content-type: application/json; charset=utf-8");
        return json_encode($res);
    }

    function createSite() {
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
            $user = $app->itemList('users',['filter'=>[
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
                foreach(['database','uploads','modules'] as $dir) {
                    is_dir($path.'/'.$dir) ? null : mkdir($path.'/'.$dir, 0777, true);
                }
                symlink($app->vars('_env.path_engine'), $path.'/engine' );
                symlink(__DIR__ , $path.'/modules/yonger' );
                symlink($dirmod.'/phonecheck', $path.'/modules/phonecheck');
                symlink(__DIR__ .'/common/tpl' , $path.'/tpl' );
                symlink(__DIR__ .'/common/forms' , $path.'/forms' );
                symlink(__DIR__ .'/common/scripts/functions.php' , $path.'/functions.php' );
                
                copy ($app->vars('_env.path_engine').'/index.php' , $path. '/index.php' );
                $domain = $app->route->domain;
                $this->createSiteUser($path);
                file_put_contents($hosts.'/.domainname',$domain);
                $tmp = $app->itemSave('users',['id'=>$uid,'sitenum'=>$sitenum]);
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
                file_put_contents($path.'/database/_settings.json',$settings);

                $res = $app->itemSave('sites',$site);
                file_put_contents($hosts.'/'.$sid,null);
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

    function createSiteUser($path) {
        $app = &$this->app;
        $user = $this->getMainUser();
        $uid = $user['id'];
        //$uid = $app->vars('_sess.user.id');
        // тут нужно не текущего пользователя брать, а того, который в yonger регился
        $users = $app->itemList('users',['filter'=>[
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
        file_put_contents($path.'/database/users.json',$users);
    }

    function getMainUser($login = null) {
        $app = &$this->app;
        if (!$login) $login = $app->vars('_sess.user.login');
        $user = $app->itemList('users',['filter'=>[
            'active' => 'on',
            'login' => $login,
            'role' => 'user'
        ]]);
        $user = array_pop($user['list']);
        return $user;
    }

    function listSites() {
        $this->setMainDba();
        $list = $this->app->fromFile(__DIR__ . '/tpl/list_sites.php');
        return $list->fetch();
    }


    function finishRegistration() {
        header("Content-type: application/json; charset=utf-8");
        $user = $this->app->vars('_post');
        $user['id'] = $this->app->user->id;
        //unset($user['_login']);
        $res = $this->app->itemSave('users',$user);
        if ($res) {
            $this->app->login($res);
            return json_encode(['error'=>false]);
        } else {
            return json_encode(['error'=>true]);
        }
    }

    function settings()
    {
        $app = $this->app;
        $out = $app->getForm('_settings', $app->vars("_route.form"));
        if ($out !== null) {
            $out->fetch();
        } else {
            $out = "Error: /forms/_settings/{$app->vars("_route.form")}.php not found!";
        }
        echo $out;
        die;
    }

    function setMainDba() {
        $app = &$this->app;
        $dba = $this->app->vars('_env.dba');
        if ($this->app->route->subdomain > '') {
            $dba = str_replace('/sites/'.$this->app->route->subdomain,'',$dba);
            $this->app->vars('_env.dba',$dba);
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
    function main_login() {
        $user = $this->app->vars('_user');
        $login = false;
        if (isset($user['login']) && $user['login'] > '' && $user['active'] == 'on') {
            $login = $user['login'];
        } else {

        }
        return $login;
    }

    function presets() {
        $res = null;
        if (isset($this->app->route->params[0])) {
            $this->file = $this->app->vars('_env.path_app').'/blocks/presets.json';
            $json = is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];
            $func = 'presets_'.$this->app->route->params[0];
            $res = method_exists($this,$func) ? $this->$func($json) : null;
        }
        header("Content-type: application/json; charset=utf-8");
        return json_encode($res);
    }

    function presets_list($json) {
            $res = $this->app->arrayToObj($json);
            return $res;
    }

    function presets_save($json) {
            $name = $this->app->vars('_post.name');
            $id = $this->app->furlGenerate($name);
            $item = ['blocks'=>$this->app->vars('_post.blocks'),'name'=>$name,'id'=>$id];
            $json[$id] = $item;
            $json = $this->app->itemToArray($json);
            $json = $this->app->jsonEncode($json);
            $this->app->putContents($this->file, $json);
            return $item;
    }

    function preset_get($name) {
        $this->file = $this->app->vars('_env.path_app').'/blocks/presets.json';
        $json = is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];

        if (isset($json[$name])) {
            return $json[$name]['blocks'];
        } else {
            return [];
        }
    }

}
