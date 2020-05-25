<?php
class usersClass {
    function __construct($app) {
        $this->app = $app;
        $this->form = "users";
    }

    function __call($method,$params) {
        echo "usersClass: Method {$method} not foind.";
        $die;
    }

    function list() {
        $app = $this->app;
        $form = $this->getForm("list");
        $form->fetch();
        echo $form->html();
    }

    function edit() {
        $app = $this->app;
        $form = $this->getForm("edit");
        $form->item = $this->app->itemRead($this->form,$app->vars("_route.id"));
        $form->fetch();
        echo $form->html();
    }

    function getForm($form) {
      $app = $this->app;
      if (is_file(__DIR__."/{$form}.php")) {
        $form = $app->fromFile(__DIR__."/{$form}.php");
      } else {
        $form = $app->fromString("usersClass: Form {$form} not foind.");
      }
      return $form;
    }

}
?>
