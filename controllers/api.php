<?php
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
      $query = $this->prepQuery($app->route->query);
      $json = $app->itemList($table,["filter"=>$query]);
      echo $app->jsonEncode($json["list"]);
  }

  function prepQuery($query) {
    $query = (array)$query;
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
    return $query;

  }
}
