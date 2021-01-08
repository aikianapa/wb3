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
    trigger - выполнение функции перед выполнением условий
    return - через запятую перечисляются поля, которые необходимо вернуть в выдачу
    */

    public function __construct($app)
    {
        set_time_limit(10);
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        $this->app = &$app;
        $mode = $this->mode = $app->route->mode;
        //print_r($app->route);
        if (method_exists($this, $mode)) {
            $this->$mode($app);
        }
    }

    public function catalog($app)
    {
        if (!$this->apikey()) {
            return;
        }
        $table = 'catalogs';
        $json = $app->itemRead($table, $app->route->item);
        $json = wbTreeToArray($json['tree']['data'], true);
        echo $app->jsonEncode($json);
    }

    public function call($app)
    {
        if (!$this->apikey()) {
            return;
        }
        $class = $app->formClass($app->route->form);
        $call = $app->route->call;
        $class->$call();
    }

    public function query($app)
    {
        if (!$this->apikey()) {
            return;
        }
        
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
            echo $app->jsonEncode($json);
        } else {
            $json = $app->itemList($table, $options);
            echo $app->jsonEncode($json["list"]);
        }
    }

    public function token()
    {
        $this->method = ['post','get'];
        $app = &$this->app;
        echo json_encode([
                    'token' => $app->vars("_sess.token")
                ]);
    }

    public function auth()
    {
        $this->method = ['post','get'];
    }


    public function apikey()
    {
        $app = &$this->app;
        $mode = &$this->mode;
        $token = $app->vars("_sess.token");
        $access = true;
        $local = false;

        //if ($app->vars('_sett.api_key_'.$mode) == 'on') $access = false;
        if ($app->vars('_sett.api_key_'.'query') == 'on') {
            $access = false;
        }
        /*
                        if ($access && $token !== $app->vars('_req.__token') ) {
                                echo json_encode(['error'=>true,'msg'=>'Access denied']);
                                die;
                        }
        */
        if (!$access && $app->vars('_sett.api_key') !== $app->vars('_req.__apikey')) {
            echo json_encode(['error'=>true,'msg'=>'Access denied']);
            die;
        }

        if ($app->vars('_req.__apikey')) {
            unset($_REQUEST['__apikey']);
            unset($_POST['__apikey']);
            unset($_GET['__apikey']);
            unset($app->route->query->__apikey);
        }
        if ($app->vars('_req.__token')) {
            unset($_REQUEST['__token']);
            unset($_POST['__token']);
            unset($_GET['__token']);
            unset($app->route->query->__token);
        }
        return true;
    }

    public function mail($app)
    {
        if (!$this->apikey()) {
            return;
        }
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

    public function apiOptions($arr) {
        // convert options array to string for __options
        $options = http_build_query($options);
        $options = str_replace(['&','%2C'], ';', $options);
        return $options;
    }

    public function prepQuery($query)
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
                    $sarr = [];
                    $sort = explode(',', $item[1]);
                    foreach ($sort as $key => $fld) {
                        $fld = explode(',', $sort[$key]);
                        $sarr[trim($fld[0])] = 1;
                    }
                    $item[1] = $sarr;
                } else if (isset($item[1]) && is_numeric($item[1])) {
                        $item[1] = $item[1] * 1;
                }
                isset($item[1]) ? $options[$item[0]] = $item[1] : null;
            }
            unset($query['__options']);
        }

        foreach ($query as $key => $val) {
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
