<?php
class ctrlAjax
{
    public function __construct($app)
    {
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        include_once($_ENV['path_engine'].'/attrs/save/ajax.php');
        include_once($_ENV['path_engine'].'/attrs/tree/ajax.php');
        $this->app = $app;
        $this->route = $app->route;
        $mode = $this->route->mode;

        in_array($mode,['getsess','getsett','gettpl','getform','auth','alive']) ? null : $app->apikey('ajax');

        //$app->initSettings($app); // сильно тормозит
        if (is_file($_ENV['path_app'].'/ajax.php')) {
            include_once($_ENV['path_app'].'/ajax.php');
            $this->ajax = new wbAjax($app);
        }
        echo $this->$mode();
        die;
    }

    public function __call($mode, $params)
    {
        if (in_array($mode, ['save','tree'])) {
            require_once($_ENV['path_engine'].'/attrs/'.strtolower($mode).'/ajax.php');
            $class = 'wbAjax'.ucfirst($mode);
            $this->ajax = new $class($this->app);
        }

        if (isset($this->ajax)) {
            echo $this->ajax->$mode();
        } elseif (is_callable(@$this->$mode)) {
            echo $this->$mode();
        } else {
            echo json_encode([null]);
        }
        die;
    }

    public function list()
    {
        $app = $this->app;
        $params = (array)$app->route->params;
        $table = $params[0];
        $data = $app->vars('_post');
        $data['__token'] = $app->vars('_sess.token');
        $list = $app->authPostContents($app->route->host."/api/query/{$table}", $data);
        $list = json_decode($list, true);
        $count = count($list);
        $options = ( object )$_POST;
        !isset($options->size) ? $options->size = 500 : 0 ;
        !isset($options->page) ? $options->page = 1 : 0;
        !isset($options->filter) ? $options->filter = [] : 0;
        $pages = ceil($count / $options->size);
        $pagination = wbPagination($options->page, $pages);
        echo json_encode(['result'=>$list, 'pages'=>$pages, 'page'=>$options->page, 'pagination'=>$pagination]);
        die;
    }


    public function alive()
    {
        if (isset($_SESSION['user'])) {
            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
    }


    public function auth()
    {
        unset($_SESSION['user']);
        $app = $this->app;
        $post = (object)$app->vars('_post');
        $fld = $app->vars('_route.params.0');
        $url = '/';
        if ($fld == 'logout') {
            if (@isset($_SESSION['user']['userole']['url_login']) && $_SESSION['user']['userole']['url_login'] > '') {
                $url = $_SESSION['user']['userole']['url_login'];
            }
            return json_encode(['login'=>false,'error'=>false,'redirect'=>$url,'user'=>[],'role'=>[]]);
        }

        if (!in_array($fld, ['email','phone','login','id']) or !isset($post->login)) {
            return json_encode(['login'=>false,'error'=>'Unknown']);
        }
        $user = $app->checkUser($post->login, $fld, $post->password);

        if ($user) {
            $role = $user->group;
            $url = '/cms';
            if ($user->role !== 'admin' and (!isset($role->active) or $role->active !== 'on') or $user->active !== 'on') {
                return json_encode(['login'=>false,'error'=>'Account is not active']);
            }
            isset($role->url_login) and $role->url_login > '' ? $url = $role->url_login : null;
            $app->login($user);
            return json_encode(['login'=>true,'error'=>false,'redirect'=>$url,'user'=>$user,'role'=>$user->role]);
        } else {
            return json_encode(['login'=>false,'error'=>'Unknown']);
        }



        die;
        $user = $app->itemList('users', ['filter'=> [$fld => $post->login ], 'limit'=>1 ]);
        if (intval($user['count']) > 0) {
            $user = array_shift($user['list']);
        } else {
            return json_encode(['login'=>false,'error'=>'Unknown']);
        }
        $user = (object)$user;
		!isset($user->role) ? $user->role = '' : null;
        if (isset($post->password) and isset($user->password) and $app->passwordCheck($post->password, $user->password)) {
            $role = (object)$app->itemRead('users', $user->role);
            $url = '/cms';
            if ($user->role !== 'admin' and (!isset($role->active) or $role->active !== 'on') or $user->active !== 'on') {
                return json_encode(['login'=>false,'error'=>'Account is not active']);
            }
            if (isset($role->url_login) and $role->url_login > '') {
                $url = $role->url_login;
            }
            $user->group = &$role;
            $app->login($user);
            return json_encode(['login'=>true,'error'=>false,'redirect'=>$url,'user'=>$user,'role'=>$role]);
        } else {
            return json_encode(['login'=>false,'error'=>'Wrong password']);
        }
    }


    public function change_fld()
    {
        $app = &$this->app;
        $dir = $app->vars('_env.dbac').'/tmp';
        $cache = json_decode(file_get_contents($dir.'/'.$_POST['cache']), true);
        if (is_array($cache) and isset($cache['tpl'])) {
            $app->vars('_route', $cache['route']);
            $_ENV['locale'] = $cache['locale'];
            $_SESSION = $cache['session'];
            $tpl = $cache['tpl'];
            foreach ($_POST['data'] as $fld => $val) {
                $tpl = str_replace("%{$fld}%", $val, $tpl);
                $tpl = str_replace("&amp;", '&', $tpl);
            }
            $tpl = $app->fromString($tpl);
            $opt = $tpl->find("option:first");
            $tpl->fetch($cache['item']);
            if ($tpl->is("select[placeholder]") and !$tpl->find('option[value=""]')->length) {
                $opt->attr('value', '');
                $opt->html($tpl->attr('placeholder'));
                $tpl->prepend($opt);
            }
            echo wb_json_encode(["content"=>$tpl->outer()]);
        }
    }

    public function form()
    {
        // передача вызова в контроллер form
        require_once(__DIR__.'/form.php');
        $this->app->vars('_route.mode', 'ajax');
        $this->app->route = (object)$this->app->vars('_route');
        $ctrl = new ctrlForm($this->app);
    }

    public function getform()
    {
        $form = $this->app->vars('_route.params.0');
        $mode = $this->app->vars('_route.params.1');
        $out = $this->app->getForm($form, $mode);
        $out && $this->app->vars('_post.data') > '' ? $out->fetch($this->app->vars('_post.data')) : $out->fetch();
        return json_encode(['result'=>$out->outer()]);
    }

    public function gettpl()
    {
        $tpl = $this->app->vars('_route.params.0');
        $out = $this->app->getTpl($tpl);
        $res = '';
        if ($out) {
            $res = ($this->app->vars('_post.data') > '') ? $out->fetch($this->app->vars('_post.data')) : $out->outer();
        }
        return json_encode(['result'=>$res]);
    }

    public function renderTpl()
    {
        header('Content-Type: text/html; charset=utf-8');
        $app = &$this->app;
        if (count($app->vars('_route.params')) == 1) {
            $tpl = $app->vars('_route.params.0');
            $path = false;
        } else {
            $tpl = implode('/', $app->vars('_route.params'));
            $path = true;

        }

        $tpl = $app->getTpl($tpl, $path);
        if (!$tpl) {
            return null;
        } else {
            $tpl->fetch();
            $out = $tpl->outer();
            return $out;
        }
    }

    public function rmitem()
    {
        $app = $this->app;
        $form = $app->vars('_route.form');
        $item = $app->vars('_route.item');
        if (!isset($_REQUEST['_confirm'])) {
            $dom = $app->getForm('snippets', 'remove_confirm');
            $dom->item = ['_form'=>$form,'_item'=>$item];
            $ajax = $dom->find('[data-ajax]')[0];
            $params = wbAttrToValue($ajax->attr('data-ajax'));
            $append = json_encode($app->vars('_post'));
            $append = wbAttrToValue($append);
            $params = array_merge((array)$append, (array)$params);
            $ajax->attr('data-ajax', json_encode($params));
            $dom->fetch();
            header('Content-Type: text/html; charset=utf-8');
            echo $dom;
            die;
        } else {
            $result = $app->itemRemove($form, $item);
            if (isset($result['_removed'])) {
                $result['_id'] = $item;
                $result['_form'] = $form;
            }
            echo json_encode($result);
            die;
        }
    }

    public function getsess()
    {
        echo json_encode($this->app->vars('_sess'));
    }

    public function mail()
    {
        $app = &$this->app;
        $attachments=[];
        isset($_POST['formdata']) ? $formdata = $_POST['formdata'] : $formdata = [];
        isset($_POST['formflds']) ? $formflds = $_POST['formflds'] : $formflds = [];

        !isset($formdata["_subject"]) ? $formdata["_subject"]=$_ENV['sysmsg']["mail_from_site"] : null;
        !isset($formdata["subject"]) ? $formdata["subject"]=$formdata["_subject"] : null;

        //$formdata["subject"] = mb_encode_mimeheader($formdata["subject"], "UTF-8");

        if (isset($formdata["_tpl"])) {
            $out = $app->getTpl($formdata["_tpl"]);
        } elseif (isset($formdata["_form"])) {
            $out = $app->getTpl($formdata["_form"]);
        } elseif (isset($formdata["_attach"])) {
            substr($formdata["_attach"],0,5) == 'data:' ? $attachments[] = $formdata["_attach"] : null;
        } elseif (isset($formdata["_message"])) {
            $out = $app->fromString($formdata["_message"]);
            $b64img = $out->find("img[src^='data:']");
            foreach ($b64img as $b64) {
                $attachments[] = $b64->attr("src");
                $b64->remove();
            }
        } else {
            $out = $app->getTpl("mail.php");
        }
        !isset($formdata["email"]) ? $formdata["email"]=$_ENV["route"]["mode"]."@".$_ENV["route"]["domain"] : null;
        !isset($formdata["name"]) ? $formdata["name"]="Site Mailer" : null;
        !isset($formdata["_mailto"]) ? $mailto = $_ENV["settings"]["email"] : $mailto = $formdata["_mailto"];
        $receivers = [];
        $sendlist = explode(",", $mailto);
        foreach ($sendlist as $tmp) {
            $tmp = trim($tmp);
            $receivers[] = "{$tmp};{$_ENV["settings"]["header"]}";
        }
        if (!$out) {
            $out = "";
            foreach ($formdata as $fld => $val) {
                if (substr($fld, 0, 1) !== '_' and $fld !== 'subject') {
                    isset($formflds[$fld]) ? $out.='<strong>'.$formflds[$fld].'</strong>' : $out.='<strong>'.$fld.'</strong>';
                    $out .= ' : '.$val."<br>\n";
                }
            }
        } else {
            $out->item = $formdata;
            $out->fetch();
            $out=$out->outer();
        }
        $res=wbMail("{$formdata["email"]};{$formdata["name"]}", $receivers, $formdata["subject"], $out, $attachments);
        if (!$res) {
            $result=json_encode(array("error"=>true,"msg"=>$_ENV['sysmsg']["mail_sent_error"].": ".$_ENV["error"]['wbMail']));
        } else {
            $result=json_encode(array("error"=>false,"msg"=>$_ENV['sysmsg']["mail_sent_success"]."!"));
        }
        if (isset($formdata["_callback"]) and is_callable($formdata["_callback"])) {
            return @$formdata["_callback"]($result);
        }
    
        return $result;
    }

    public function onlineusers()
    {
        clearstatcache();
        $count = null;
        if ($directory_handle = opendir(session_save_path())) {
            $count = 0;
            while (false !== ($file = readdir($directory_handle))) {
                if ($file != '.' && $file != '..') {
                    if (time()- filemtime(session_save_path() . '' . $file) < 3 * 60) {
                        $count++;
                    }
                }
            }
            closedir($directory_handle);
        }

        header('Content-Type: application/json');
        return json_encode(["count"=>$count]);
    }


    public function getsett()
    {
        $app = $this->app;
        $sett = $app->vars('_sett');
        unset($sett['cmsmenu']);
        unset($sett['api_key']);
        echo json_encode($sett);
    }
}
