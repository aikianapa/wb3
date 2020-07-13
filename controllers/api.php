<?php
use Adbar\Dot;

class ctrlApi {
  function __construct($app) {
      header('Content-Type: charset=utf-8');
      header('Content-Type: application/json');
      $mode = $app->route->mode;
      if (method_exists($this,$mode)) {
          $this->$mode($app);
      }
  }

  function query($app) {
      $table = $app->route->table;
      $options = $this->prepQuery($app->route->query);
      if (isset($app->route->item)) {
            $json = $app->itemRead($table,$app->route->item);
            if (isset($app->route->field)) {
                $fields = new Dot();
                $fields->setReference($json);
                $json = $fields->get($app->route->field);
            }
            echo $app->jsonEncode($json);
      } else {
            $json = $app->itemList($table,$options);
            echo $app->jsonEncode($json["list"]);
      }
  }


    function mail($app) {
        if ($app->vars('_sett.api_key_mail') == 'on' && $app->vars('_sett.api_key') !== $app->vars('_post._token') ) {
            echo json_encode(['error'=>true,'msg'=>'Invalid API key']);
            die;
        }
        
        $attachments=[];
        if (!isset($_POST["_subject"])) {
            $_POST["_subject"]=$_ENV['sysmsg']["mail_from_site"];
        }
        if (!isset($_POST["subject"])) {
            $_POST["subject"]=$_POST["_subject"];
        }
        if (isset($_POST['formdata'])) {
            foreach($_POST['formdata'] as $key => $val) {
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
        if (!$out) $out = $app->fromString('<html>{{message}}</html>');
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

  function prepQuery($query) {
    $query = (array)$query;
    $options = [];
    if ($query["__options"]) {
      $opt = $query["__options"];

  		$list = explode(';',$opt);
		  foreach($list as $key => $item) {
  			  $item = explode('=',$list[$key]);
  			  if ($item[0] == 'sort') {
  				  $sort = $item[1];
  				  $sarr = [];
  				  $sort = explode(',',$sort);
  				  foreach($sort as $key => $fld) {
  						$fld = explode(':',$sort[$key]);
  						if (!isset($fld[1])) $sarr[$fld[0]] = 1;
  						if ($fld[1] == 'a' || $fld[1] == 'asc' || $fld[1] == '1') $sarr[$fld[0]] = 1;
  						if ($fld[1] == 'd' || $fld[1] == 'desc' || $fld[1] == '-1') $sarr[$fld[0]] = -1;
  				  }
  				  $item[1] = $sarr;
  			  } else if ($item[0] == 'return') {
  				  $item[0] = 'projection';
  				  $sarr = [];
  				  $sort = explode(',',$item[1]);
  				  foreach($sort as $key => $fld) {
  						$fld = explode(',',$sort[$key]);
  						$sarr[trim($fld[0])] = 1;
  				  }
  				  $item[1] = $sarr;
  			  } else {
  				  if (is_numeric($item[1])) $item[1] = $item[1] * 1;
  			  }
  			  $options[$item[0]] = $item[1];
  		  }
        unset($query["__options"]);
    }

    foreach($query as $key => $val) {
        if (substr($val,-1) == "]" && substr($val,0,1) == "[") {
            // считаем что в val массив и разбтраем его
            $val = explode(",",substr($val,1,strlen($val) -2));
            switch (substr($key,-1)) {
                default:
                    $query[$key] = ['$in' => $val];
                case '!':
                    unset($query[$key]);
                    $query[substr($key,0,strlen($key) -1)] =  ['$nin'=> $val];
                    break;
            }
        } else {

              switch (substr($key,-1)) {
                case '<': // меньше (<)
                      $query[substr($key,0,strlen($key) -1)] = ['$lte'=>$val];
                      unset($query[$key]);
                      break;
                case '>': // больше (>)
                      $query[substr($key,0,strlen($key) -1)] = ['$gte'=>$val];
                      unset($query[$key]);
                      break;
                case '"': // двойная кавычка (") без учёта регистра
                      $query[substr($key,0,strlen($key) -1)] = ['$regex' => '(?mi)^'.$val."$"];
                      unset($query[$key]);
                      break;
                case '*':
                      //var regex = new RegExp(val, "i");
                      $query[substr($key,0,strlen($key) -1)] = ['$like'=>$val];
                      unset($query[$key]);
                      break;
                case '!':
                      $query[substr($key,0,strlen($key) -1)] = ['$ne'=>$val];
                      unset($query[$key]);
                      break;
              }
        }
    }
    $options["filter"] = $query;
    return $options;

  }
}
