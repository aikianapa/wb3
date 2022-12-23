<?php
class modSetup {
  function __construct($app) {
      $app->router->addRouteFile(__DIR__."/router.ini");
      $app->vars('_post.setup') == 'start' ? $mode = 'setup' : $mode = $app->vars("_route.mode");
      $this->app = $app;
      if (method_exists($this,$mode)) {
        echo $this->$mode();
      } else {
        require_once($app->route->path_engine.'/controllers/form.php');
        $form = new ctrlForm($app);
        echo $form->get404();
      }
      exit;
  }

  function init()
  {
    if (!is_file($this->app->vars('_env.path_app').'/database/__setup.json')) {
      header("Location: /signin");
    } else {
        $app = $this->app;
        $setup = $app->fromFile(__DIR__."/setup_ui.php", true);
        $setup->fetch();
        return $setup;
    }
  }

  function setup() {
    $app = $this->app;
    $yoncom = $this->app->vars('_env.path_engine').'/modules/yonger/common';
    @copy($this->app->vars('_env.path_engine').'/database/_settings.json', $this->app->vars('_env.path_app').'/database/_settings.json');
    @copy($this->app->vars('_env.path_engine').'/database/pages.json', $this->app->vars('_env.path_app').'/database/pages.json');
    @copy($yoncom.'/tpl/pages.php', $this->app->vars('_env.path_app').'/tpl/pages.php');
    @copy($yoncom.'/scripts/_functions.php', $this->app->vars('_env.path_app').'/functions.php');

    unlink($this->app->vars('_env.path_app').'/database/__setup.json');
    $sett = wbItemRead('_settings', 'settings');

    //shell_exec("cp -r $src $dest");

    $sett = [
      'id' => 'settings'
      ,'header' => $app->vars('_post.header')
      ,'email' => $app->vars('_post.email')
    ];

    $user = [
      'id' => $app->newId()
      ,'role' => 'admin'
      ,'active' => 'on'
      ,'first_name' => 'Admin'
      ,'email' => $app->vars('_post.email')
      ,'password' => $app->passwordMake($app->vars('_post.password'))
    ];

    $s = $app->itemSave('_settings', $sett);
    $u = $app->itemSave('users', $user);
    $app->shadow('/module/yonger/yonmap');
    if ($s && $u) header("Location: /workspace");

  }


}
?>
