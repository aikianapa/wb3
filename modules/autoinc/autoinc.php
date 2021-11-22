<?php
class modAutoinc {
    
  function __construct($app)
  {
    $this->app = &$app;
    $this->file = $this->app->vars('_env.dba').'/_mod_autoinc.json';
    !is_file($this->file) ? file_put_contents($this->file,json_encode([])) : null;
  }

  function inc($table, $field, $value = null ) {
      $app = &$this->app;
      $id = 't_'.$table;
      $json = $app->_db->itemRead('_mod_autoinc', $id);
      $json ? $json = (object)$json : $json = (object)['id'=>$id];
      isset($json->$field) ? null : $inc = $json->$field = 0;
      $inc = intval($json->$field);
        $value > 0 && $inc < $value ? $inc = intval($value) : $inc += 1;
        $json->$field = $inc;
        $json = (array)$json;
        $app->_db->itemSave('_mod_autoinc', $json);
        return $inc;
  }
}
?>