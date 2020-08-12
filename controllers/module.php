<?php

class ctrlModule {
  function __construct($app) {
      $this->app = $app;
      $module = $app->route->module;
      $mode = $app->route->mode;
      if ($mode == '_settings') {
          $this->settings();
      } else {
        $aModule=$_ENV["path_app"]."/modules/{$module}/{$module}.php";
        $eModule=$_ENV["path_engine"]."/modules/{$module}/{$module}.php";
        if (is_file($aModule)) {require_once($aModule);}
        elseif (is_file($eModule)) {require_once($eModule);}
        $class = "mod".ucfirst($module);
        if (class_exists($class)) {
            $out = new $class($app);
            $out->$mode();
        } else {
            header( "HTTP/1.1 404 Not Found" );
            echo "Error 404";
        }
      }
      die;
  }

  function __call($mode, $params)  {
      if (!is_callable(@$this->$mode)) {
        header( "HTTP/1.1 404 Not Found" );
        echo "Error 404";
        die;
      }
  }

  function settings() {
      $app = $this->app;
      $modules = $app->listModules();
      $module = $modules[$app->route->module];
      $tpl = $app->fromFile($app->vars('_env.path_engine').'/modules/cms/forms/_settings/ui_mods.php');
      $tpl = $app->fromString($tpl->find('#modSettingsWrapper')->inner());
      $out = $app->fromFile($module['sett']);
      $tpl->find("form > div")->html($out->outer());
      $tpl->fetch(['module'=>$module['id']]);
      echo $tpl->outer();
  }
}
?>
