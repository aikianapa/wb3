<?php
class modAutoinc {

  /*
  <span class="article__views" wb="module=autoinc&table=blog&field={{id}}&ajax=true"></span> - отображает значение
  <span class="article__views" wb="module=autoinc&table=blog&field={{id}}&increase=true&ajax=true"></span> - прибавляет и отображает
  без параметра ajax всё происходит на этапе рендеринга, но не работает на кэшированных страницах
  <span class="article__views" wb="module=autoinc&table=blog&field={{id}}"></span> - отображает значение
  <span class="article__views" wb="module=autoinc&table=blog&field={{id}}&increase=true"></span> - прибавляет и отображает
  */

  function __construct(&$obj)
  {
    if (strtolower(get_class($obj)) == 'wbdom') {
      $this->app = &$obj->app;
      $this->dom = &$obj;
      $this->increase();
    } else {
      $this->app = &$obj;
    }
    $this->file = $this->app->vars('_env.dba').'/_mod_autoinc.json';
    !is_file($this->file) ? file_put_contents($this->file,json_encode([])) : null;
  }

  function ajax() {
      $url = "/module/autoinc/{$this->method}/?table={$this->table}&field={$this->field}";
      $json = [
        'url' => $url,
        'silent' => true,
      ];
      $this->dom->attr('data-ajax', json_encode($json));
      $this->dom->attr('auto', true);
  }


  function increase() {
    // Нужно автоматом формировать data-ajax, только так будет работать при включении кэша
    
    if (isset($this->dom)) {
      // <span wb="module=autoinc&table=blog&field={{id}}&increase=true"></span>
      $table = null;
      @$this->dom->params('table') > '' ? $table = $this->dom->params('table') : $table = $this->dom->app->route->form;
      @$this->dom->params('field') > '' ? $field = $this->dom->params('field') : $field = 'id';
      @$this->dom->params('value') > '' ? $value = $this->dom->params('value') : $value = null;
      @$this->dom->params('increase')=='true' ? $method = 'increase' : $method = 'get';

      if ($this->dom->params('ajax')=='true' && $table > '' && $field > '') {
          $this->table = $table;
          $this->field = $field;
          $this->method = $method;
          $this->ajax();
          return;
      } else {
          if ($this->dom->params('increase')=='true' && $table > '' && $field > '') {
              $count = $this->inc($table, $field, $value);
          } else {
              $count = $this->get($table, $field);
          }
          $this->dom->inner('<wb>'.$count.'</wb>');
          $this->dom->find('wb')->unwrap('wb');
      }
    } else {
        $res = null;
        @$this->app->route->params->table > '' ? $table = $this->app->route->params->table : $table = @$this->dom->app->route->form;
        @$this->app->route->params->field > '' ? $field = $this->app->route->params->field : $field = 'id';
        @$this->app->route->params->value > '' ? $value = $this->app->route->params->value : $value = null;
        if ($table > '' && $field > '') {
            $res = $this->inc($table, $field, $value);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
          'result'=>$res,
          'callback' => wbMinifyJs(file_get_contents(__DIR__.'/callback.js'))
        ]);

    }
  }

  function get($table = null, $field = null) {
    if ($table > '' && $field > '') {
        // <span wb="module=autoinc&table=blog&field={{id}}"></span>
        $app = &$this->app;
        $id = 't_'.$table;
        $json = (object)$app->_db->itemRead('_mod_autoinc', $id);
        $inc = intval($json->$field);
        return $inc;
    } else {
        $method = $this->app->route->mode;
        $table = $this->app->route->params->table;
        $field = $this->app->route->params->field;
        $res = $this->$method($table, $field);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
          'result'=>$res,
          'callback' => wbMinifyJs(file_get_contents(__DIR__.'/callback.js'))
        ]);
    }
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