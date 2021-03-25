<?php
class ctrlModule {
  function __construct($app) {
      $this->app = $app;
			if (!isset($app->route->module)) {
					echo 'Error! Please, add module clause to router.<br \>Example: /cms/ajax/form/(:any)/(:any) => /module/<u>module:cms</u>/mode:ajax/form:$1/action:$2 ';
					die;
			}

			$module = $app->route->module;
      $mode = $app->route->mode;
      if ($mode == '_settings') {
          $this->settings();
      } else {
        $modpath = "/modules/{$module}";
        $aModule=$_ENV["path_app"].$modpath."/{$module}.php";
        $eModule=$_ENV["path_engine"].$modpath."/{$module}.php";
        if (is_file($aModule)) {
          $app->addModule($module, $modpath);
          require_once($aModule);
        } else if (is_file($eModule)) {
          $app->addModule($module, '/engine'.$modpath);
          require_once($eModule);
        }
        $class = "mod".ucfirst($module);
        if (class_exists($class)) {
            $out = new $class($app);
            $out->$mode();
        } else {
            $form = $app->controller('form');
            echo $form->get404();
        }
      }
      die;
  }

  function __call($mode, $params)  {
      if (!is_callable(@$this->$mode)) {
        $form = $app->controller('form');
        echo $form->get404();
        die;
      }
  }

  function settings() {
      $app = $this->app;
      if ($app->vars('_sess.user_role') !== 'admin') {
          echo $app->vars('_env.sysmsg.disallow');
          die;
      }
      $modules = $app->listModules();
      $module = $modules[$app->route->module];
      $tpl = $app->getForm('_settings','ui_mods');
      $tpl = $app->fromString($tpl->find('#modSettingsWrapper')->inner());
      $out = $app->fromFile($module['sett']);
      $tpl->find("form > div")->html($out->outer());
      $tpl->fetch(['module'=>$module['id']]);
      echo $tpl->outer();
  }
}
?>
