<?php

class ctrlModule
{
    public $app;
    
    public function __construct($app)
    {
        $this->app = $app;
        if (!isset($app->route->module)) {
            echo 'Error! Please, add module clause to router.<br \>Example: /cms/ajax/form/(:any)/(:any) => /module/<u>module:cms</u>/mode:ajax/form:$1/action:$2 ';
            exit;
        }

        $module = $app->vars('_route.module');
        $mode = $app->vars('_route.mode');
        if ($mode == '_settings') {
            $this->settings();
        } else {
            /*
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
            */
            $class = "mod".ucfirst($module);
            if (class_exists($class)) {
                $out = new $class($app);
                $out->$mode();
            } else {
                $form = $app->controller('form');
                echo $form->get404();
            }
        }
        exit;
    }

    public function __call($mode, $params)
    {
        if (!is_callable(@$this->$mode)) {
            $form = $this->app->controller('form');
            echo $form->get404();
            exit;
        }
    }

    public function settings()
    {
        $app = $this->app;
        $modules = $app->listModules();
        $module = $modules[$app->route->module];
        $tpl = $app->getForm('_settings', 'ui_mods');
        $tpl = $app->fromString($tpl->find('#modSettingsWrapper')->inner());
        $out = $app->fromFile($module['sett']);

        if ($out->find('[name="__jsonfile"]')->length) {
            $jsonset = explode('.', $module['sett']);
            $jsonset[count($jsonset)-1] = 'json';
            $jsonset = implode('.', $jsonset);
            if (!is_file($jsonset)) {
                file_put_contents($jsonset, json_encode([$module['id']=>[]]));
            }
            $out->find('[name="__jsonfile"]')->attr('value', $jsonset);
            $data = [$module['id'] => json_decode(file_get_contents($jsonset), true)];
        } else {
            $data = $app->vars('_sett.modules');
        }
        isset($data[$module['id']]) ? null : $data[$module['id']] = [];
        $tpl->fetch(['module'=>$module['id']]);
        $out->fetch($data[$module['id']]);
        $tpl->find("form > div")->html($out->outer());
        isset($out->params->allow) && $out->params->allow == true ? $allow = $out->params->allow : $allow = null;
        if ($app->vars('_sess.user_role') !== 'admin' and $allow !== true) {
            echo $app->vars('_env.sysmsg.disallow');
            exit;
        }

        echo $tpl->outer();
    }
}
