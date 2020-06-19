<?php
class cmsFormsClass {

    function __construct($app) {
        $this->app = $app;
        $this->form = $app->vars("_route.form");
    }

    function __call($method,$params) {
        $this->methodForm($method);
        echo "cmsFormsClass: Method {$method} not foind.";
        die;
    }

    function list() {
        $app = $this->app;
        $form = $this->getForm("list");
        if (!$form) {
          echo "Form {$app->vars("_route.form")}->list not found!";
        } else {
          $form->fetch();
          echo $form->html();
        }
    }

    function methodForm($method) {
        // edit, role
        $app = $this->app;
        $form = $this->getForm($method);
        if ($form) {
            $form->item = $this->app->itemRead($this->form,$app->vars("_route.id"));
            $form->fetch();
            echo $form->html();
        } else {
            echo "cmsFormsClass: Form {$this->form} not found.";
        }
        die;
    }

    function getForm($form) {
      $app = $this->app;
      if (is_file($app->vars("_env.path_app")."/forms/{$this->form}/{$form}.php")) {
        $form = $app->fromFile($app->vars("_env.path_app")."/forms/{$this->form}/{$form}.php");
      } else if (is_file(__DIR__."/forms/{$this->form}/{$form}.php")) {
        $form = $app->fromFile(__DIR__."/forms/{$this->form}/{$form}.php");
      } else {
        $form = false;
      }
      return $form;
    }

}
?>
