<?php
use Adbar\Dot;
class ctrlApi
{

    /* 
    http://work2.loc/api/query/units/?active=on&square%3E=46&_context*=test&__options=sort=square:d;trigger=beforeItemShow;return=square,address;limit=2

    Знаки перед равно:
    *= - слово присутствует в поле
    >= - больше или равно
    <= - меньше или равно

    Значения в __options (опции перечисляются через ; ):
    sort - сортировка по указанному полю :d (desc) :a (asc)
    limit - ограничение по количеству выводимых записей
    trigger - выполнение функции перед проверкой условия
    return - через запятую перечисляются поля, которые необходимо вернуть в выдачу
    */

    public function __construct($app)
    {
        set_time_limit(10);
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        $this->app = &$app;
        $mode = $this->mode = $app->vars('_route.mode');
        $table = $app->vars('_route.table');
        if ($app->apikey() OR in_array($mode,['auth','token'])) {
            if (method_exists($this, $mode)) {
                echo $this->$mode();
            } else if ($table > '') {
                $form = $app->formClass($table);
                $func = 'api'.$mode;
                if (method_exists($form, $func)) {
                    echo $form->$func();
                } else {
                    echo json_encode(null);
                }
            } else {
                echo json_encode(null);
            }
        }
        die;
    }


    public function create() {
        $app = &$this->app;
        $app->route->item == '_new' ? $item = null : $item = $app->itemRead($app->route->table, $app->route->item);
        if ($item == null) {
            $item = $app->vars('_'.strtolower($app->route->method));
            $item['_id'] = $app->route->item;
            $item = $app->itemSave($app->route->table, $item);
            return $app->jsonEncode($item);
        } else {
            wbError("", "", 1016, [$app->route->table, $app->route->item]);
            return $app->jsonEncode(['error'=>true,'msg'=>$_ENV["last_error"]]);
        }
    }

    public function read() {
        $app = &$this->app;
        $json = $app->itemRead($app->route->table, $app->route->item);
        if ($json == null) {
            wbError("", "", 1006, [$app->route->table, $app->route->item]);
            return $app->jsonEncode(['error'=>true,'msg'=>$_ENV["last_error"]]);
        } else {
            return $app->jsonEncode($json);
        }
    }

    public function update() {
        $app = &$this->app;
        $item = $app->itemRead($app->route->table, $app->route->item);
        if ($item !== null) {
            $item = $app->vars('_'.strtolower($app->route->method));
            $item['_id'] = $app->route->item;
            $item = $app->itemSave($app->route->table, $item);
            return $app->jsonEncode($item);
        } else {
            wbError("", "", 1006, [$app->route->table, $app->route->item]);
            return $app->jsonEncode(['error'=>true,'msg'=>$_ENV["last_error"]]);
        }
    }

    public function delete() {
        $app = &$this->app;
        $json = $app->itemRemove($app->route->table, $app->route->item);
        if ($json == null) {
            wbError("", "", 1006, [$app->route->table, $app->route->item]);
            return $app->jsonEncode(['error'=>true,'msg'=>$_ENV["last_error"]]);
        } else {
            return $app->jsonEncode($json);
        }
    }

    public function save() {
        $app = &$this->app;
        $item = $app->vars('_'.strtolower($app->route->method));
        isset($app->route->item) ? $id = $app->route->item : null;
        isset($item['id']) ? $id = $item['id'] : null; 
        isset($item['_id']) ? $id = $item['_id'] : null;
        isset($id) && !isset($app->route->item) ? $app->route->item = $id : null;
        isset($id) ? $srci = $app->itemRead($app->route->table, $app->route->item) : $srci = ['id'=>$app->newId()];
        if (isset($app->route->field)) {
            $fld = $app->dot($item);
            $fld = $fld->get($app->route->field);
            $dot = $app->dot($srci);
            $dot->set($app->route->field,$fld);
            $item = $dot->get();
        } else {
            !$srci ? $srci = [] : null;
            !$item ? $item = [] : null;
            $item = array_merge($srci, $item);
        }
        $item = $app->itemSave($app->route->table, $item);
        return $app->jsonEncode($item);
    }

    public function catalog()
    {
        $app = &$this->app;
        $table = 'catalogs';
        $json = $app->itemRead($table, $app->route->item);
        $json = wbTreeToArray($json['tree']['data'], true);
        return $app->jsonEncode($json);
    }

    public function call()
    {
        $app = &$this->app;
        $class = $app->formClass($app->route->form);
        $call = $app->route->call;
        if (method_exists($class,$call)) {
            return $class->$call();
        } else {
            return $app->jsonEncode(['error'=>true, 'msg'=>"Method {$call} not exists in {$app->route->form}"]);
        }
        
    }

    public function query()
    {
        $app = &$this->app;
        $table = $app->route->table;
        $query = (array)$app->route->query;
        if ($app->vars('_post._tid') == '') {
            // если передан _tid, то значения в _post не используются в условиях запроса
            $app->route->method == 'POST' ? $query = array_merge($query, $app->vars('_post')) : null;
            $app->route->query = (object)$query;
        }

        $options = $this->prepQuery($app->route->query);

        if (isset($app->route->item)) {
            $json = $app->itemRead($table, $app->route->item);
            if (isset($app->route->field)) {
                $fields = new Dot();
                $fields->setReference($json);
                $json = $fields->get($app->route->field);
            }
            return $app->jsonEncode($json);
        } else {
            $json = $app->itemList($table, $options);
            $options = (object)$options;
            if (!isset($options->size)) {
                return $app->jsonEncode($json['list']);
            } else {
                $pages = ceil($json['count'] / $options->size);
                $pagination = wbPagination($json['page'], $pages);
                return $app->jsonEncode(['result'=>$json['list'], 'pages'=>$pages, 'page'=>$json['page'], 'pagination'=>$pagination]);
            }
        }
    }

    public function token()
    {
        $this->method = ['post','get'];
        $app = &$this->app;
        echo json_encode([
            'token' => $app->getToken()
        ]);
    }

    public function auth()
    {
        $app = &$this->app;
        $this->method = ['post','get'];
        $post = (object)$app->vars('_req');
        $fld = $app->route->type;
        $url = '/';
        if ($fld == 'logout') {
            if (@isset($_SESSION['user']['userole']['url_login']) && $_SESSION['user']['userole']['url_login'] > '') {
                $url = $_SESSION['user']['userole']['url_login'];
            }
            setcookie("user", null, time()-3600, "/");
            unset($_SESSION['user']);
            session_regenerate_id();
            session_destroy();
            return json_encode(['login'=>false,'error'=>false,'redirect'=>$url,'user'=>[],'role'=>[]]);
        }

        if (!in_array($fld, ['email','phone','login','signup','recover'])) {
            return json_encode(['login'=>false,'error'=>'Unknown']);
        } else if (in_array($fld, ['signup','recover'])) {
            if (method_exists($this, $fld)) {
                return $this->$fld();
            } else {
                return json_encode(['login'=>false,'error'=>'Unknown']);
            }
        }

        if (!isset($post->login)) {
            return json_encode(['login'=>false,'error'=>'Login is empty']);
        }

        $user = $app->itemList('users', ['filter'=> [$fld => $post->login ], 'limit'=>1 ]);
        if (intval($user['count']) > 0) {
            $user = array_shift($user['list']);
        } else {
            return json_encode(['login'=>false,'error'=>'Unknown']);
        }
        $user = (object)$user;

        if (isset($post->password) and isset($user->password) and $app->passwordCheck($post->password, $user->password)) {
            $role = (object)$app->itemRead('users', $user->role);
            $url = '/cms';
            if (!isset($role->active) or $role->active !== 'on' or $user->active !== 'on') {
                return json_encode(['login'=>false,'error'=>'Account is not active']);
            }
            isset($role->url_login) and $role->url_login > '' ? $url = $role->url_login : null;
            unset($user->password);
            $_SESSION['user'] = (array)$user;
            $_SESSION['userole'] = (array)$role;
            $user->token = $app->getToken();

            return json_encode(['login'=>true,'error'=>false,'redirect'=>$url,'user'=>$user,'role'=>$role]);
        } else {
            return json_encode(['login'=>false,'error'=>'Wrong password']);
        }
    }

    public function recover() {
        $app = &$this->app;
        $recover = $app->vars('_route.params.0');
        if ($app->vars('_route.refferer') == '' && $recover > ' ') {
                $list = $app->itemList('users', [
                    'filter'=>['active'=>'on','recover'=>$recover]
                ]);
                $list['count'] > 0 ? $user = array_pop($list['list']) : $user = null;
                if ($user && $user['recover'] == $recover) {
                    $user['password'] = $user['recover_password'];
                    $user['recover'] = null;
                    $user['recover_password'] = null;
                    $user = $app->itemSave('users', $user);
                }
                if (!$user) {
                    $form = $app->controller('form');
                    echo $form->get404();
                    die;
                } else {
                    header("Location: {$app->route->host}/signin/");
                }
            die;
        } else if ($app->vars('_route.refferer') == '') {
            return json_encode(['recover'=>false,'error'=>'Unknown']);
        }
        $login = $app->vars('_post.login');
        $text = $app->fromString(json_decode($app->vars('_post.text')));

        $error = null;
        $phone = $app->digitsOnly($login);

        $or = [];
        if (is_email($login)) {
            $or[] = ['email' => $login];
        } else if (strlen($phone) > 8) {
            $or[] = ['phone' => $phone];
            $or[] = ['login' => $login];
        } else {
            $or[] = ['login' => $login];
        }
        $list = $app->itemList('users', [
            'filter'=>['active'=>'on','$or'=>$or]
        ]);
        if ($list['count'] == 0) {
            return json_encode(['recover'=>false,'error'=>'User not found']);
        } else {
            $user = array_pop($list['list']);
            if (!isset($user['email']) OR !is_email($user['email'])) {
                return json_encode(['recover'=>false,'error'=>'User email is empty']);
            } else {
                $recover = md5(time().session_id().$user['email']);
                $data = $user;
                $data['recover'] = $app->route->host.'/api/auth/recover/'.$recover;
                $data['password'] = $app->vars('_post.password');
                $text->fetch($data);
                $res = $app->mail('test@test.ts', $user['email'], 'test', $text->outer());
                if (!$res['error']) {
                    $user['recover'] = $recover;
                    $user['recover_password'] = $app->passwordMake($app->vars('_post.password'));
                    $user = $app->itemSave('users', $user);
                    if ($user) {
                        return json_encode(['recover'=>true,'error'=>'Recovery link sended']);
                    }
                } else {
                    return json_encode(['recover'=>false,'error'=>'Unknown error']);
                }
            }
        }
    }

    public function signup() {
        $app = &$this->app;
        if ($app->vars('_route.refferer') == '') {
            return json_encode(['sugnup'=>false,'error'=>'Unknown']);
        }
        $new = $app->vars('_post');
        $error = null;
        $or = [];
        isset($new['phone']) ? $phone = $app->digitsOnly($new['phone']): $phone = null;
        isset($new['email']) ? $email = trim($new['email']) : $email = null;
        isset($new['login']) ?  $login = trim($new['login']) : $login = null;

        if ($email) $or[] = ['email' => $email];
        if ($phone) $or[] = ['phone' => $phone];
        if ($login) $or[] = ['login' => $login];

        $list = $app->itemList('users', [
            'filter'=>['$or' => $or]
        ]);

        if (intval($list['count']) > 0)  {
            return json_encode(['signup'=>false,'error'=>'User already exists']);
        } else {
            $new['role'] = $app->vars('_sett.modules.login.group');
            $new['active'] = $app->vars('_sett.modules.login.status');
            $new['password'] = $app->passwordMake($new['password']);
            
            $user = $app->itemSave('users', $new);
            if ($user) {
                unset($user['password']);
                return json_encode(['signup'=>true,'user'=>$user]);
            } else {
                return json_encode(['signup'=>false,'error'=>'Unknown error']);
            }
        }
    }

    public function mail()
    {
        $app = &$this->app;
        $attachments=[];
        if (!isset($_POST["_subject"])) {
            $_POST["_subject"]=$_ENV['sysmsg']["mail_from_site"];
        }
        if (!isset($_POST["subject"])) {
            $_POST["subject"]=$_POST["_subject"];
        }
        if (isset($_POST['formdata'])) {
            foreach ($_POST['formdata'] as $key => $val) {
                $_POST[$key] = $val;
            }
            unset($_POST['formdata']);
        }
        if (isset($_POST["_tpl"])) {
            $out = $app->getTpl($_POST["_tpl"]);
        } elseif (isset($_POST["_form"])) {
            $out = $app->getTpl($_POST["_form"]);
        } elseif (isset($_POST["_message"])) {
            $out = $app->fromString('<html>'.$_POST["_message"].'</html>');
            $b64img = $out->find("img[src^='data:']");
            foreach ($b64img as $b64) {
                $attachments[] = $b64->attr("src");
                $b64->remove();
            }
        } else {
            $out = $app->getTpl("mail.php");
        }
        if (!$out) {
            $out = $app->fromString('<html>{{message}}</html>');
        }
        if (!isset($_POST["email"])) {
            $_POST["email"]=$_ENV["route"]["mode"]."@".$_ENV["route"]["hostname"];
        }
        if (!isset($_POST["name"])) {
            $_POST["name"]="Site Mailer";
        }
        if (isset($_POST["_mailto"])) {
            $mailto=$_POST["_mailto"];
        } else {
            $mailto = $_ENV["settings"]["email"];
        }
        $out->item = $_POST;
        $out->fetch();
        $out=$out->outer();
        $res=wbMail("{$_POST["email"]};{$_POST["name"]}", "{$mailto};{$_ENV["settings"]["header"]}", $_POST["subject"], $out, $attachments);
        if (!$res) {
            $result=json_encode(array("error"=>true,"msg"=>$_ENV['sysmsg']["mail_sent_error"].": ".$_ENV["error"]['wbMail']));
        } else {
            $result=json_encode(array("error"=>false,"msg"=>$_ENV['sysmsg']["mail_sent_success"]."!"));
        }
        if (isset($_POST["_callback"]) and is_callable($_POST["_callback"])) {
            return @$_POST["_callback"]($result);
        }
        echo $result;
    }

    private function apiOptions($arr) {
        // convert options array to string for __options
        $options = http_build_query($options);
        $options = str_replace(['&','%2C'], ';', $options);
        return $options;
    }

    private function prepQuery($query)
    {
        $query = (array)$query;
        $options = [];
        if (isset($query['__options'])) {
            $opt = $query['__options'];
            $list = explode(';', $opt);
            foreach ($list as $key => $item) {
                $item = explode('=', $list[$key]);
                if ($item[0] == 'sort') {
                    $sort = $item[1];
                    $sarr = [];
                    $sort = explode(',', $sort);
                    foreach ($sort as $key => $fld) {
                        $fld = explode(':', $sort[$key]);
                        if (!isset($fld[1])) {
                            $fld[1] = '1';
                        }
                        if ($fld[1] == 'a' || $fld[1] == 'asc' || $fld[1] == '1') {
                            $sarr[$fld[0]] = 1;
                        }
                        if ($fld[1] == 'd' || $fld[1] == 'desc' || $fld[1] == '-1') {
                            $sarr[$fld[0]] = -1;
                        }
                    }
                    $item[1] = $sarr;
                } elseif ($item[0] == 'return') {
                    $item[0] = 'projection';
                    $item[1] = explode(',', $item[1]);
                } else if (isset($item[1]) && is_numeric($item[1])) {
                        $item[1] = $item[1] * 1;
                }
                isset($item[1]) ? $options[$item[0]] = $item[1] : null;
            }
            unset($query['__options']);
        }

        foreach ($query as $key => $val) {
            (array)$val === $val ? $val = json_encode($val) : null;
            if (substr($val, -1) == "]" && substr($val, 0, 1) == "[") {
                // считаем что в val массив и разбтраем его
                $val = explode(",", substr($val, 1, strlen($val) -2));
                switch (substr($key, -1)) {
                default:
                    $query[$key] = ['$in' => $val];
                                        break;
                case '!':
                    unset($query[$key]);
                    $query[substr($key, 0, strlen($key) -1)] =  ['$nin'=> $val];
                    break;
                }
            } else {
                switch (substr($key, -1)) {
                case '<': // меньше (<)
                      $query[substr($key, 0, strlen($key) -1)] = ['$lte'=>$val];
                      unset($query[$key]);
                      break;
                case '>': // больше (>)
                      $query[substr($key, 0, strlen($key) -1)] = ['$gte'=>$val];
                      unset($query[$key]);
                      break;
                case '"': // двойная кавычка (") без учёта регистра
                      $query[substr($key, 0, strlen($key) -1)] = ['$regex' => '(?mi)^'.$val."$"];
                      unset($query[$key]);
                      break;
                case '*':
                      //var regex = new RegExp(val, "i");
                      $query[substr($key, 0, strlen($key) -1)] = ['$like'=>$val];
                      unset($query[$key]);
                      break;
                case '!':
                      $query[substr($key, 0, strlen($key) -1)] = ['$ne'=>$val];
                      unset($query[$key]);
                      break;
              }
            }
        }
        $options["filter"] = $query;
        return $options;
    }
}
