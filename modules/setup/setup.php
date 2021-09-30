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
      die;
  }

  function init()
  {
    if (!is_file($this->app->vars('_env.path_app').'/database/__setup.json')) {
      header("Location: /cms/login/");
    } else {
        $app = $this->app;
        $setup = $app->fromFile(__DIR__."/setup_ui.php", true);
        $setup->fetch();
        return $setup;
    }
  }

  function setup() {
    $app = $this->app;
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

    @copy($this->app->vars('_env.path_app').'/database/__setup.json', $this->app->vars('_env.path_app').'/database/_settings.json');
    unlink($this->app->vars('_env.path_app').'/database/__setup.json');
    $s = $app->itemSave('_settings', $sett);
    $u = $app->itemSave('users', $user);

    if ($s && $u) header("Location: /cms/login/");

  }


}
?>
