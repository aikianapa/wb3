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
        $form = $this->app->getForm($this->form, "list");
        if (!$form) {
          echo "Form {$app->vars("_route.form")}->list not found!";
        } else {
          $form->fetch();
          if ($app->vars('_post.target') > '') {
              $form = $form->find($app->vars('_post.target'));
              $form = $app->fromString('<html>'.$form->outer().'</html>');
          }
          echo $form->html();
        }
    }

    function methodForm($method) {
        // edit, role
        $app = $this->app;
        $form = $this->app->getForm($this->form,$method);
        if ($form) {
            $form->item = $this->app->itemRead($this->form,$app->vars("_route.id"));
            $form->item = wbTrigger('form', __FUNCTION__, 'beforeItem'.ucfirst($method), [$this->form], $form->item);
            $form->fetch();
            echo $form->html();
        } else {
            echo "cmsFormsClass: Form {$this->form} not found.";
        }
        die;
    }
}
?>
