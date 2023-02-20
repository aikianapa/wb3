<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/lib/vendor/autoload.php';
require_once __DIR__ .'/vendor/autoload.php';
use Notihnio\RequestParser\RequestParser;
use Nahid\JsonQ\Jsonq;

class modApi
{
    private $app;
    private $table;
    private $role;
    private $mode;
    private $method;
    private $options;

    function __construct($app)
    {
        $mode = $this->mode = $app->vars('_route.mode');
        if (!wbCheckBacktrace("wbModuleClass")) {
            $this->init($app);
            exit;
        } else if (in_array(strtolower($mode),['getsett'])) {
            $this->ajaxSettings($app);
        }
    }

    function ajaxSettings($sett = null) {
        $sett = [];
        return $sett;
    }
    function init($app) {
        set_time_limit(60);
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        $this->app = &$app;
        $this->role = $this->app->vars('_sess.user.role');
        $app->api = &$this;
        $this->checkMethod(['get','post','put','auth','delete']);
        $mode = $this->mode = $app->vars('_route.mode');
        $table = $this->table = $app->vars('_route.table');
        $result = null;
        if ($this->apikey()) {
            if (method_exists($this, $mode)) {
                $result = $this->$mode();
            } elseif ($table > '') {
                $form = $app->formClass($table);
                $func = 'api'.$mode;
                method_exists($form, $func) ? $result = $form->$func() : null;
            }
            $result = $app->jsonEncode($result);
            $re = '/,"__token":"(.*)"|"__token":"(.*)"/mU';
            $result = preg_replace($re, '', $result);
            echo $result;
        }
    }
    function apikey($mode = null)
    {
        $app = &$this->app;

        if (in_array($this->mode, ['login','logout','token'])) {
            return true;
        }

        if ($app->vars('_sett.modules.api.active') !== 'on' or ($app->route->localreq == true && !$app->vars('_route.token'))) {
            return true;
        }

        foreach ($app->vars('_sett.modules.api.allowmode') as $am) {
            $amm = str_replace('//', '/', '/api/v2/'.$am);
            if (substr($amm, -1) == '/') {
                $amm = substr($amm, 0, -1);
            }
            if ($am > '' && substr($this->app->vars('_route.uri'), 0, strlen($amm)) == $amm) {
                return true;
            }
        }
        $mode == null ? $mode = $app->vars('_route.mode') : null;

        $access = $this->checkAllow();

        if (!$access) {
            header("HTTP/1.1 401 Unauthorized", true, 401);
            echo json_encode(['error'=>true,'msg'=>'Access denied']);
            exit;
        }
        return true;
    }

    public function checkAllow() {
        $app = &$this->app;
        $table = $this->app->vars('_route.table');
        $mode = $this->app->vars('_route.mode');
        $role = $this->role;
        $modes = ['create','read','update','delete','func','list'];
        if (!in_array($mode, $modes)) {
            return true;
        }
        $mode = substr($mode, 0, 1);

        if ($role == 'admin' && in_array($table,['_settings','users'])) {
            return true;
        }
        
        if ($role == 'admin' && $mode == 'f') {
            return true;
        }

        $result = false;
        $allow = true;
        foreach ((array)$this->app->vars('_sett.modules.api.allow') as $am) {
            $am['role'] = isset($am['role']) ? (array)$am['role'] : [''];
            $am['table'] = isset($am['table']) ? (array)$am['table'] : [''];
            if (in_array($table, $am['table']) OR in_array("*", $am['table'])) {
                $allow = false;
                if (in_array($role, $am['role']) OR in_array("*", $am['role'])) {
                    if (in_array($mode, $am['mode']) or in_array("*", $am['mode'])) {
                        $allow = true;
                    }
                }
            }
        }
        $disallow = false;
        foreach ((array)$this->app->vars('_sett.modules.api.disallow') as $am) {
            $am['role'] = isset($am['role']) ? (array)$am['role'] : [''];
            $am['table'] = isset($am['table']) ? (array)$am['table'] : [''];
            if (in_array($table, $am['table']) or in_array("*", $am['table'])) {
                $disallow = false;
                if (in_array($role, $am['role']) or in_array("*", $am['role'])) {
                    if (in_array($mode, $am['mode']) or in_array("*", $am['mode'])) {
                        $disallow = true;
                    }
                }
            }
        }
        $result = ($allow == true && $disallow == false) ? true : false;

        if ($result) {
            if ($app->vars('_route.token') && in_array($app->vars('_route.token'), $app->vars('_sett.modules.api.tokens'))) {
                $result = true;
            } else if ($this->role > '') {
                $result = $app->checkToken($app->vars('_route.token'));
            }
        }

        return $result;
    }

    public function token()
    {
        $this->method = ['post','get'];
        $token = $this->app->getToken();
        return ['token' => $token];
    }

    function checkMethod($methods)
    {
        $methods = (array)$methods;
        if (!in_array(strtolower($this->app->route->method), $methods)) {
            header('HTTP/1.1 405 Method Not Allowed', true, 405);
            exit;
        }
        $this->method = strtolower($this->app->route->method);
    }

    function create()
    {
        /*
        /api/v2/create/{{table}}/{{id}}
        */

        $this->checkMethod(['post','put','get']);
        $table = $this->table;

        $request = RequestParser::parse(); // PUT, DELETE, etc.. support
        //$_POST = $request->params;
        $_FILES = $request->files;
        
        $post = ($this->method == 'get') ? $_GET: $request->params;
        $item = $this->app->vars('_route.item');

        ($item == '' && isset($post['id'])) ? $item = $post['id'] : null;
        
        $check = $this->app->itemRead($table, $item);
        if ($check) {
            header('HTTP/1.1 409 Conflict', true, 409);
        } else {
            ($item > '') ? $post['id'] = $item : null;
            $data = $this->app->itemSave($table, $post);
            header('HTTP/1.1 201 Created', true, 201);
            return $data;
        }
        exit;
    }

        function read()
        {
            /*
            /api/v2/read/{{table}}/{{id}}
            */

            $this->checkMethod(['post','put','get']);
            $table = $this->table;
            $item = $this->app->vars('_route.item');
            $request = RequestParser::parse(); // PUT, DELETE, etc.. support
            //$_POST = $request->params;
            //$_FILES = $request->files;
            $post = $request->params;
            $item = $this->app->itemRead($table, $item);
            if (!$item) {
                header('HTTP/1.1 404 Not found', true, 404);
                return ['error'=>true,'msg'=>"Item {$item} not found",'errno'=>404];
            } else {
                return $item;
            }
        }


    function save()
    {
        /*
        /api/v2/save/{{table}}/{{id}}
        */

        $this->checkMethod(['post', 'get']);
        $table = $this->table;
        $item = $this->app->vars('_route.item');
        $request = RequestParser::parse(); // PUT, DELETE, etc.. support
        //$_POST = $request->params;
        $_FILES = $request->files;
        $post = ($this->method == 'get') ? $_GET: $request->params;
        ($item == '' && isset($post['_id']) && $post['_id'] > '') ? $item = $post['_id'] : null ;
        ($item == '' && isset($post['id']) && $post['id'] > '') ? $item = $post['id'] : null;
        $post['_id'] = ($item > '') ? $post['_id'] = $item : $this->app->newId();
        $data = $this->app->itemSave($table, $post);
        header('HTTP/1.1 200 OK', true, 200);
        return $data;
        exit;
    }

    function update()
    {
        /*
        /api/v2/update/{{table}}/{{id}}
        */

        $this->checkMethod(['post','put','get']);
        $table = $this->table;
        $item = $this->app->vars('_route.item');
        $request = RequestParser::parse(); // PUT, DELETE, etc.. support
        //$_POST = $request->params;
        $_FILES = $request->files;
        $post = ($this->method == 'get') ? $_GET: $request->params;
        $check = $this->app->itemRead($table, $item);
        if (!$check) {
            header('HTTP/1.1 404 Not found', true, 404);
            return ['error'=>true,'msg'=>"Item {$item} not found",'errno'=>404];
        } else {
            ($item > '') ? $post['_id'] = $item : null;
            $data = $this->app->itemSave($table, $post);
            header('HTTP/1.1 200 OK', true, 200);
            return $data;
        }
        exit;
    }

    function delete()
    {
        /*
        /api/v2/delete/{{table}}/{{id}}
        */

        $this->checkMethod(['get','post','delete']);
        $table = $this->table;
        $item = $this->app->vars('_route.item');
        $check = $this->app->itemRead($table, $item);
        if ($check) {
            $data = $this->app->itemRemove($table, $item);
            if (isset($data['_removed'])) {
                return ['error'=>false,'msg'=>"Item {$item} deleted",'errno'=>204, 'data'=>$data];
            } else {
                header('HTTP/1.1 409 Conflict', true, 409);
                return ['error'=>true,'msg'=>"Item {$item} don't deleted",'errno'=>409];
            }
        } else {
            header('HTTP/1.1 404 Not found', true, 404);
        }
    }

    function login()
    {
        $this->checkMethod(['post','put','get','auth']);
        $type = $this->table ? $this->table : $this->app->vars('_sett.modules.login.loginby');
        $type == '' ? $type = 'email' : null;
        $request = RequestParser::parse();
        $post = $request->params;
        $this->method == 'get' ? $post = array_merge($post, $this->app->vars('_get')) : null;
        $post = (object)$post;
        $login = isset($post->login) ? $post->login : null;
        $password = isset($post->password) ? $post->password : null;
        $user = $this->app->checkUser($login, $type, $password);
        if ($user) {
            $this->app->login($user);
            header('HTTP/1.1 200 OK', true, 200);
            @$redirect = $user->group->url_login > '' ? $user->group->url_login : null;
            return ['login'=>true,'error'=>false,'msg'=>'You are successfully logged in','redirect'=>$redirect,'user'=>$this->app->user,'token'=>$this->app->token];
        } else {
            setcookie("user", null, time()-3600, "/");
            unset($_SESSION['user']);
            session_regenerate_id();
            session_destroy();
            header("HTTP/1.1 401 Unauthorized", true, 401);
            return ['login'=>false,'error'=>true,'msg'=>'Authorization has been denied for this request.','errno'=>401];
        }
    }

    function logout()
    {
        $group = (object)$this->app->user->group;
        @$redirect = $group->url_logout > '' ? $group->url_logout : '/';
        setcookie("user", '', -1,'/');
        unset($_SESSION['user']);
        unset($_COOKIE['user']);
        session_regenerate_id();
        session_destroy();
        return ['login'=>false,'error'=>false,'redirect'=>$redirect,'user'=>null,'role'=>null];
    }



    function upload()
    {
        /*
        Загрузка файлов
        Обязательно указание таблицы
        /api/v2/upload/{{table}}
        */
        $this->checkMethod(['post']);
        $uploads = '/uploads'.$this->app->vars('_post.path');
        if ($this->table > '') {
            $uploads .= $this->table;
        }

        if ($this->app->vars('_post.path')) {
            $path = $this->app->vars("_env.path_app").$uploads;
        }

        $path = wbNormalizePath($path);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $error = true;
        $request = RequestParser::parse();
        $_FILES = $request->files;
        $filename = basename($_FILES['file']['name']);
        $file = str_replace('//','/',$path .'/'. $filename);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $msg = 'File uploaded';
            header('HTTP/1.1 200 '.$msg, true, 200);
            return ['error'=>false,
                    'msg'=>$msg,
                    'file'=>$file,
                    'uri'=>$uploads.'/'.$filename,
                    'filename'=>$filename,
                    'errno'=>200
            ];
        } else {
            $msg = "Unsupported Media Type";
        }
        header('HTTP/1.1 415 '.$msg, true, 415);
        return ['error'=>true,'msg'=>$msg,'errno'=>415];

    }


    function func()
    {
        /*
        Вызов функции из класса формы
        Если требуется доступ по токену, то соответствующая проверка должна быть в функции
        /api/v2/func/{{table}}/{{func}}
        */
        $this->checkMethod(['post','get']);
        $app = &$this->app;
        $form = $app->route->form;
        $func = $app->route->func;
        $class = $app->formClass($form);
        if (!method_exists($class,$func)) {
            header('HTTP/1.1 404 Not found', true, 404);
            return ['error'=>true,'msg'=>"Function {$func} not found",'errno'=>404];
        }
        return $class->$func();
    }

    function listview() {
        header("Content-Type: text/html; charset=UTF-8");
        $out = $this->app->fromFile(__DIR__ . '/api_ui.php');
        $out->fetch();
        $result = json_encode($this->list(),JSON_UNESCAPED_UNICODE);
        echo $out;
        exit;
    }

    function list()
    {
        /*
        /api/v2/list/{{table}}
        /api/v2/list/{{table}}/{{id}}
        /api/v2/list/{{table}}/{{id}}/{{field}}
        /api/v2/list/{{table}}/{{id}}/{{field}}.*?@return=fld1,fld2
        /api/v2/list/{{table}}?field=value&@option=value

        query:
            &field=[val1,val2]   - in_array(field,[..,..]); значение поля присутствует в указанном массиве
            &field!=[val1,val2]  - !in_array(field,[..,..]); значение поля отсутствует в указанном массиве
            &field~=[val1,val2]  - массив в поле содержит одно из перечисленных значений
            &field=val           - field == 'val'
            &field!=val          - field !== 'val'
            &field"=val          - field == 'val' && field == 'VAL' && field == 'vAl' (регистр не учитывается)
            &field*=val          - field like 'val'
            &field>=val          - field >= 'val'
            &field<=val          - field <= 'val'
            &field>>=val         - field > 'val'
            &field<<=val         - field < 'val'
            &field~=val          - field like 'val' (RegExp(val, "i"))

            "{'active':'on','$in': ['some_value','$students'] }" - работает в запросах ($students - это имя поля-массива |$-указатель на поле|, в котором ищем значение)

        options:
            &@return=id,name     - return selected fields only
            &@size=10            - break list and return current page
            &@chunk=10           - chunk list and return
            &@page=2             - return page by value
            &@sort=name:d        - sort list by field :d(desc) :a(asc)
            &@group=fld1,fld2    - группирует вывод по указанным полям
            &@supress            - подавляет вывод промежуточных данных при группировке
        */
        header('Access-Control-Allow-Origin: *');
        $this->checkMethod(['get','post']);
        $app = &$this->app;
        $table = $app->route->table;
        $query = (array)$app->route->query;
        $options = $this->app->filterPrepare($app->route->query);
        $form = $app->formClass($table);
        if ($app->vars('_post.filter') > '') {
            $options['filter'] = (array)$app->vars('_post.filter');
            foreach($options['filter'] as $key => $val) {
                if (substr($key,0,1) == '@') {
                    $options[substr($key,1)] = $val;
                    unset($options['filter'][$key]);
                }
            }
        }
        $fields = $app->Dot();
        $jflds = $app->Dot();
        $options = $this->options = (object)$options;
        $return = isset($options->return) ? explode(',', $options->return) : false;
        $group =  isset($options->group) ? explode(',', $options->group) : false;
        if ($return && $options->filter) {
            $return = array_keys(array_flip($return) + array_flip($options->filter));
        }
        if ($return && $group) {
            $return = array_keys(array_flip($return) + array_flip($group));
            $options->return = implode(',',$return);
        }
        if (isset($app->route->item)) {
            $json = $app->itemRead($table, $app->route->item);        
            if ($form && @method_exists($form, 'beforeItemShow')) {
                $form->beforeItemShow($json);
            }
            if (isset($app->route->field)) {
                $fields->setReference($json);
                if (substr($app->route->field, -2) == '.*') {
                    $json = array_values($fields->get(substr($app->route->field, 0, -2)));
                } else {
                    $json = $fields->get($app->route->field);
                }
                if ((array)$json === $json && isset($options->filter)) {
                    $json = $this->app->arrayFilter((array)$json, (array)$options);
                }
                if ($return) {
                    foreach($json as &$jtm) {
                        $jflds->setReference($jtm);
                        $tmp = [];
                        foreach ($return as $ret) {
                            $ret = trim($ret);
                            $tmp[$ret] = $jflds->get($ret);
                        }
                        $jtm = $tmp;
                    }
                    $json = array_values($json);
                }
            }
            return $json;
        } else {
            $json = $app->itemList($table, (array)$options);
            $json['list'] = (array)$json['list'];
            if ($form && @method_exists($form, 'beforeItemShow')) {
                foreach($json['list'] as &$item) {
                    $form->beforeItemShow($item);
                    if ($return) {
                    $jflds->setReference($item);
                    $tmp = [];
                    foreach ($return as $ret) {
                        $ret = trim($ret);
                        $tmp[$ret] = $jflds->get($ret);
                    }
                    $item = $tmp;
                    }
                }
            }
            if (isset($options->group)) {
                $json['list'] = $this->group($json['list']);
                return (array)$json['list'];
            }

            if (isset($options->chunk)) {
                //print_r((array)$json);
            } elseif (!isset($options->size)) {
                //return $app->jsonEncode(array_values((array)$json['list']));
                return array_values((array)$json['list']);
            } else {
                $pages = ceil($json['count'] / $options->size);
                $pagination = wbPagination($json['page'], $pages);
                return ['result'=>(array)$json['list'], 'pages'=>$pages, 'page'=>$json['page'], 'pagination'=>$pagination];
            }
        }
    }

    private function group($list, $flds = null)
    {
        $jsonq = $this->app->json($list);
        $flds = ($flds == null) ? wbAttrToArray($this->options->group) : $flds;
        //$total = $this->total($list);
        $list = [];
        if (count($flds)) {
            $fld = array_shift($flds);
            $grps = $jsonq->groupBy($fld)->get();
            foreach ($grps as $key => $grp) {
                count($flds) > 0 ? $grp = $this->group($grp, $flds) : null;
                isset($this->options->supress) ? array_push($list,$key) : $list[$key] = $grp;
                
            }
        }
        return $list;
    }


    function apiOptions($arr)
    {
        // convert options array to string for __options
        $options = http_build_query($arr);
        $options = str_replace(['&','%2C'], ';', $options);
        return $options;
    }
}
/*
200 OK — это ответ на успешные GET, PUT, PATCH или DELETE. Этот код также используется для POST, который не приводит к созданию.
201 Created — этот код состояния является ответом на POST, который приводит к созданию.
204 Нет содержимого. Это ответ на успешный запрос, который не будет возвращать тело (например, запрос DELETE)
304 Not Modified — используйте этот код состояния, когда заголовки HTTP-кеширования находятся в работе
400 Bad Request — этот код состояния указывает, что запрос искажен, например, если тело не может быть проанализировано
401 Unauthorized — Если не указаны или недействительны данные аутентификации. Также полезно активировать всплывающее окно auth, если приложение используется из браузера
403 Forbidden — когда аутентификация прошла успешно, но аутентифицированный пользователь не имеет доступа к ресурсу
404 Not found — если запрашивается несуществующий ресурс
405 Method Not Allowed — когда запрашивается HTTP-метод, который не разрешен для аутентифицированного пользователя
409 Conflict - если сервер не обработает запрос, но причина этого не в вине клиента
410 Gone — этот код состояния указывает, что ресурс в этой конечной точке больше не доступен. Полезно в качестве защитного ответа для старых версий API
415 Unsupported Media Type. Если в качестве части запроса был указан неправильный тип содержимого
422 Unprocessable Entity — используется для проверки ошибок
429 Too Many Requests — когда запрос отклоняется из-за ограничения скорости
*/