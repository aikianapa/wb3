<?php
class cmsFormsClass {
    function __construct($app) {
        $this->app = $app;
        $this->form = $app->vars("_route.form");
        method_exists($this, '_init') ? $this->_init() : null;
    }

    function __call($method,$params) {
        $this->methodForm($method);
        echo "cmsFormsClass: Method {$method} not found.";
        exit;
    }

    function list() {
        header('Content-Type: text/html; charset=utf-8');
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
        header('Content-Type: text/html; charset=utf-8');
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
        exit;
    }
}
?>
