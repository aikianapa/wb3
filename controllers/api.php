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
