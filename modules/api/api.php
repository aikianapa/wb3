<?php

require_once __DIR__ .'/vendor/autoload.php';
use Notihnio\RequestParser\RequestParser;

class modApi
{
    public function __construct($app)
    {
        set_time_limit(60);
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        $this->app = &$app;
        $this->checkMethod(['get','post','put','auth','delete']);
        $mode = $this->mode = $app->vars('_route.mode');
        $table = $this->table = $app->vars('_route.table');
        $result = null;
        if ($this->apikey() or in_array($mode, ['login','logout','token'])) {
            if (method_exists($this, $mode)) {
                $result = $this->$mode();
            } elseif ($table > '') {
                $form = $app->formClass($table);
                $func = 'api'.$mode;
                method_exists($form, $func) ? $result = $form->$func() : null;
            }
            echo $app->jsonEncode($result);
        }
        die;
    }


    function apikey($mode = null)
    {
        $app = &$this->app;
        if ($app->vars('_sett.modules.api.active') !== 'on' or ($app->route->localreq == true && !$app->vars('_route.token'))) {
            return true;
        }
        $mode == null ? $mode = $app->vars('_route.mode') : null;
        $access = $app->checkToken($app->vars('_route.token'));

        if (!$access) {
            echo json_encode(['error'=>true,'msg'=>'Access denied']);
            die;
        }
        return true;
    }


    public function token()
    {
        $this->method = ['post','get'];
        $token = $this->app->getToken();
        return ['token' => $token];
    }

    private function checkMethod($methods)
    {
        $methods = (array)$methods;
        if (!in_array(strtolower($this->app->route->method), $methods)) {
            header($this->app->route->method." 405 Method Not Allowed", true, 405);
            die;
        }
        $this->method = strtolower($this->app->route->method);
    }

    private function create()
    {
        /*
        /api/v2/create/{{table}}/{{id}}
        */

        $this->checkMethod(['post','put']);
        $table = $this->table;
        $item = $this->vars('_route.item');
        $request = RequestParser::parse(); // PUT, DELETE, etc.. support
        //$_POST = $request->params;
        //$_FILES = $request->files;
        $post = $request->params;
        $check = $this->app->itemRead($table, $item);
        if ($check) {
            header($this->app->route->method.' 409 Conflict', true, 409);
        } else {
            ($item > '') ? $post['_id'] = $item : null;
            $data = $this->app->itemSave($table, $post);
            header($this->app->route->method.' 201 Created', true, 201);
            return $data;
        }
        die;
    }

        private function read()
        {
            /*
            /api/v2/read/{{table}}/{{id}}
            */

            $this->checkMethod(['post','put']);
            $table = $this->table;
            $item = $this->vars('_route.item');
            $request = RequestParser::parse(); // PUT, DELETE, etc.. support
            //$_POST = $request->params;
            //$_FILES = $request->files;
            $post = $request->params;
            $item = $this->app->itemRead($table, $item);
            if (!$item) {
                header($this->app->route->method.' 404 Not found', true, 404);
            } else {
                return $item;
            }
            die;
        }
    private function update()
    {
        /*
        /api/v2/update/{{table}}/{{id}}
        */

        $this->checkMethod(['post','put']);
        $table = $this->table;
        $item = $this->vars('_route.item');
        $request = RequestParser::parse(); // PUT, DELETE, etc.. support
        //$_POST = $request->params;
        //$_FILES = $request->files;
        $post = $request->params;
        $check = $this->app->itemRead($table, $item);
        if (!$check) {
            header($this->app->route->method.' 404 Not found', true, 404);
        } else {
            ($item > '') ? $post['_id'] = $item : null;
            $data = $this->app->itemSave($table, $post);
            header($this->app->route->method.' 200 OK', true, 200);
            return $data;
        }
        die;
    }

    private function delete()
    {
        /*
        /api/v2/delete/{{table}}/{{id}}
        */

        $this->checkMethod(['get','post','delete']);
        $table = $this->table;
        $item = $this->vars('_route.item');
        $check = $this->app->itemRead($table, $item);
        if ($check) {
            $data = $this->app->itemRemove($table, $item);
            if (isset($data['_removed'])) {
                header($this->app->route->method.' 204 Deleted', true, 204);
            } else {
                header($this->app->route->method.' 409 Conflict', true, 409);
            }
        } else {
            header($this->app->route->method.' 404 Not found', true, 404);
        }
    }

    private function login()
    {
        $this->checkMethod(['post','put','get','auth']);
        $type = $this->table ? $this->table : $this->app->vars('_sett.modules.login.loginby');
        $type == '' ? $type = 'login' : null;
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

    private function logout()
    {
        $group = (object)$this->app->user->group;
        @$redirect = $group->url_logout > '' ? $group->url_logout : '/';
        setcookie("user", null, time()-3600, "/");
        unset($_SESSION['user']);
        session_regenerate_id();
        session_destroy();
        return ['login'=>false,'error'=>false,'redirect'=>$redirect,'user'=>null,'role'=>null];
    }


    private function func()
    {
        /*
        Вызов функции из класса формы
        Если требуется доступ по токену, то соответствующая проверка должна быть в функции
        /api/v2/func/{{table}}/{{func}}
        */
        $app = &$this->app;
        $form = $app->route->form;
        $func = $app->route->func;
        $class = $app->formClass($form);
        return $class->$func();
    }


    private function list()
    {
        /*
        /api/v2/list/{{table}}
        /api/v2/list/{{table}}/{{id}}
        /api/v2/list/{{table}}/{{id}}/{{field}}
        /api/v2/list/{{table}}?field=value&@option=value

        query:
            &field=[val1,val2]   - in_array(field,[..,..]);
            &field!=[val1,val2]  - !in_array(field,[..,..]);
            &field=val           - field == 'val'
            &field!=val          - field !== 'val'
            &field"=val          - field == 'val' && field == 'VAL' && field == 'vAl' (регистр не учитывается)
            &field*=val          - field like 'val'
            &field>=val          - field >= 'val'
            &field<=val          - field <= 'val'
            &field>>=val          - field > 'val'
            &field<<=val          - field < 'val'

        options:
            &@return=id,name     - return selected fields only
            &@size=10            - break list and return current page
            &@chunk=10           - chunk list and return
            &@page=2             - return page by value
            &@sort=name:d        - sort list by field :d(desc) :a(asc)
        */

        $this->checkMethod(['get','post']);
        $app = &$this->app;
        $table = $app->route->table;
        $query = (array)$app->route->query;
        $options = $this->prepQuery($app->route->query);

        $app->vars('_post.filter') > '' ? $options['filter'] = $app->vars('_post.filter') : null;

        if (isset($app->route->item)) {
            $json = $app->itemRead($table, $app->route->item);
            if (isset($app->route->field)) {
                $fields = new $this->app->Dot();
                $fields->setReference($json);
                $json = $fields->get($app->route->field);
            }
            return $json;
        } else {
            $json = $app->itemList($table, $options);
            $json['list'] = (object)$json['list'];
            $options = (object)$options;
            if (isset($options->chunk)) {
                return (array)$json;
            } elseif (!isset($options->size)) {
                //return $app->jsonEncode(array_values((array)$json['list']));
                return array_values((array)$json['list']);
            } else {
                $pages = ceil($json['count'] / $options->size);
                $pagination = wbPagination($json['page'], $pages);
                return ['result'=>$json['list'], 'pages'=>$pages, 'page'=>$json['page'], 'pagination'=>$pagination];
            }
        }
    }

    private function apiOptions($arr)
    {
        // convert options array to string for __options
        $options = http_build_query($arr);
        $options = str_replace(['&','%2C'], ';', $options);
        return $options;
    }

    private function prepQuery($query)
    {
        $query = (array)$query;
        $options = [];

        foreach ($query as $key => $val) {
            if (substr($key, 0, 1) == '@') {
                $options[substr($key, 1)] = $val;
                unset($query[$key]);
            } else {
                (array)$val === $val ? $val = json_encode($val) : null;
                if (substr($val, -1) == "]" && substr($val, 0, 1) == "[") {
                    // считаем что в val массив и разбираем его
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

                    switch (substr($key, -2)) {
                        case '<<': // меньше (<)
                            $query[substr($key, 0, strlen($key) -2)] = ['$lt'=>$val];
                            unset($query[$key]);
                            break;
                        case '>>': // больше (>)
                            $query[substr($key, 0, strlen($key) -2)] = ['$gt'=>$val];
                            unset($query[$key]);
                            break;
                    }

                    if (isset($query[$key])) {
                        switch (substr($key, -1)) {
                            case '<': // меньше или равно (<=)
                                $query[substr($key, 0, strlen($key) -1)] = ['$lte'=>$val];
                                unset($query[$key]);
                                break;
                            case '>': // больше или равно (>=)
                                $query[substr($key, 0, strlen($key) -1)] = ['$gte'=>$val];
                                unset($query[$key]);
                                break;
                            case '"': // двойная кавычка (") без учёта регистра
                                $query[substr($key, 0, strlen($key) -1)] = ['$regex' => '(?mi)^'.$val."$"];
                                unset($query[$key]);
                                break;
                            case '~':
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
            }
        }
        $options["filter"] = $query;
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
