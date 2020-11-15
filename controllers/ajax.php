<?php
class ctrlAjax {
  function __construct($app) {
    header('Content-Type: charset=utf-8');
    header('Content-Type: application/json');
      include_once($_ENV['path_engine'].'/attrs/save/ajax.php');
      include_once($_ENV['path_engine'].'/attrs/tree/ajax.php');
      $app->initSettings($app);
      if (is_file($_ENV['path_app'].'/ajax.php')) {
          include_once($_ENV['path_app'].'/ajax.php');
          $this->ajax = new wbAjax($app);
      }
      $this->app = $app;
      $this->route = $app->route;
      $mode = $this->route->mode;
      echo $this->$mode();
      die;
  }

  function __call($mode, $params)  {
      if (in_array($mode,['save','tree'])) {
          require_once($_ENV['path_engine'].'/attrs/'.strtolower($mode).'/ajax.php');
          $class = 'wbAjax'.ucfirst($mode);
          $this->ajax = new $class($this->app);
      }

      if ($this->ajax) {
          echo $this->ajax->$mode();
      } else if (is_callable(@$this->$mode)) {
          echo $this->$mode();
      } else {
          echo json_encode([null]);
      }
      die;
  }

  public function alive() {
    if (isset($_SESSION['user'])) echo json_encode(['result' => true]);
    else echo json_encode(['result' => false]);
  }


  public function auth() {
      unset($_SESSION['user']);
      $app = $this->app;
      $post = (object)$app->vars('_post');
      $fld = $app->vars('_route.params.0');
      $url = '/';
      if ($fld == 'logout') {
          if (@isset($_SESSION['user']['userole']['url_login']) && $_SESSION['user']['userole']['url_login'] > '') {
              $url = $_SESSION['user']['userole']['url_login'];
          }
          return json_encode(['login'=>false,'error'=>false,'redirect'=>$url,'user'=>[],'role'=>[]]);
      }

      if (!in_array($fld,['email','phone','login']) OR !isset($post->login)) return json_encode(['login'=>false,'error'=>'Unknown']);

      $user = $app->itemList('users',['filter'=> [$fld => $post->login ], 'limit'=>1 ]);
      if (intval($user['count']) > 0) $user = array_shift($user['list']);
          else return json_encode(['login'=>false,'error'=>'Unknown']);
      $user = (object)$user;

      if (isset($post->password) AND isset($user->password) AND $app->passwordCheck($post->password, $user->password)) {
          $role = (object)$app->itemRead('users',$user->role);
          $url = '/cms';
          if (!isset($role->active) OR $role->active !== 'on' OR $user->active !== 'on') return json_encode(['login'=>false,'error'=>'Account is not active']);
          if (isset($role->url_login) AND $role->url_login > '') $url = $role->url_login;
          $_SESSION['user'] = (array)$user;
          $_SESSION['userole'] = (array)$role;
          return json_encode(['login'=>true,'error'=>false,'redirect'=>$url,'user'=>$user,'role'=>$role]);
      } else {
          return json_encode(['login'=>false,'error'=>'Wrong password']);
      }
  }


    function change_fld()
    {
        $app = &$this->app;
        $dir = $app->vars('_env.dbac').'/tmp';
        $cache = json_decode(file_get_contents($dir.'/'.$_POST['cache']), true);
        if (is_array($cache) and isset($cache['tpl'])) {
            $app->vars('_route', $cache['route']);
            $_ENV['locale'] = $cache['locale'];
            $_SESSION = $cache['session'];
            $tpl = &$cache['tpl'];
            foreach($_POST['data'] as $fld => $val) {
                $tpl = str_replace("%{$fld}%", $val, $tpl);
            }
            $tpl = $app->fromString($tpl);
            $tpl->fetch($cache['item']);
            echo wb_json_encode(["content"=>$tpl->outer()]);
        }
    }

  function form() {
      // передача вызова в контроллер form
      require_once(__DIR__.'/form.php');
      $this->app->vars('_route.mode','ajax');
      $this->app->route = (object)$this->app->vars('_route');
      $ctrl = new ctrlForm($this->app);
      //$ctrl->ajax->
  }

  function getform() {
    $form = $this->app->vars('_route.params.0');
    $mode = $this->app->vars('_route.params.1');
    $out = $this->app->getForm($form,$mode);
    return json_encode(['result'=>$out->outer()]);
  }

  function rmitem()
  {
      $app = $this->app;
      $form = $app->vars('_route.form');
      $item = $app->vars('_route.item');
      if (!isset($_REQUEST['_confirm'])) {
          $dom = $app->getForm('snippets', 'remove_confirm');
          $dom->item = ['_form'=>$form,'_item'=>$item];
          $ajax = $dom->find('[data-ajax]')[0];
          $params = wbAttrToValue($ajax->attr('data-ajax'));
          $append = json_encode($app->vars('_post'));
          $append = wbAttrToValue($append);
          $params = array_merge((array)$append,(array)$params);
          $ajax->attr('data-ajax',json_encode($params));
          $dom->fetch();
          header('Content-Type: text/html; charset=utf-8');
          echo $dom;
          die;
      } else {
          $result = $app->itemRemove($form, $item);
          if (isset($result['_removed'])) {
              $result['_id'] = $item;
              $result['_form'] = $form;
          }
          echo json_encode($result);
          die;
      }
  }

  function getsess() {
    $app = $this->app;
    echo json_encode($app->vars('_sess'));
  }

  function getsett() {
    $app = $this->app;
    $sett = $app->vars('_sett');
    unset($sett['cmsmenu']);
    unset($sett['api_key']);
    echo json_encode($sett);
  }
}
?>
