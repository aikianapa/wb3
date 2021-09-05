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
        $json = file_get_contents($this->file,LOCK_EX);
        try {
            $json = $app->dot(json_decode($json,true));
        } catch (\Throwable $th) {
            $json = $app->dot();
        }
        $value !== null && intval($json->get($table.'.'.$field)) < $value ? $inc = intval($value) : $inc = intval($json->get($table.'.'.$field)) + 1;
        $json->set($table.'.'.$field, $inc);
        file_put_contents($this->file,json_encode($json->get()),LOCK_UN);
        return $inc;
  }
}
?>