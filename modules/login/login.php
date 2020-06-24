<?php
use Adbar\Dot;

class modLogin
{
    public function __construct(&$obj)
    {
        if ($obj instanceof wbDom) {
            $app = $obj->app;
            $dom = $obj;
            if (!isset($dom->params->mode)) $dom->params->mode = "signin";
            $this->mode = $dom->params->mode;
            $this->embed = true;
        } elseif ($obj instanceof wbApp) {
            $app = $obj;
            $dom = $app->fromString("");
            $this->mode = $app->vars("_route.mode");
            $this->embed = false;
        } else {
            return;
        }
        $this->app = $app;
        $this->dom = $dom;
        $this->init($dom);
    }

    public function init(&$dom = null)
    {
        $out = "";
        $app = $this->app;
        if ($dom == null) $dom = $this->dom;
        $call = $this->mode;
        if (method_exists($this, $call)) $out = @$this->$call($dom);
        if ($this->embed == false) {
          echo $out->fetch();
          die;
        } else {
          $dom->before($out);
          $dom->remove();
        }
    }

    public function signin($dom)
    {
        $app = $dom->app;
        $out = $app->getTpl("login_ui.php");
        if (!$out) {
            $out = $app->fromFile(__DIR__."/login_ui.php");
        }
        $out->item = $app->vars("_post");
        $out->item["_dir_"] = $out->path;
        $out->fetch();
        if (count($app->vars("_post"))) {
            $user = $this->modLoginCheckUser($app->vars("_post.l"), $app->vars("_post.p"));
            if ($user) {
                $user = $this->modLoginSuccess($app, $user);
                header('Location: '.$user->group->login_url);
            } else {
                $out->find("#signin .signin-wrong")->removeClass("d-none");
            }
        }
        return $out;
    }

    public function login__signup(&$app)
    {
        $out = $app->getTpl("login_ui.php");
        if (!$out) {
            $out = $app->fromFile(__DIR__."/login_ui.php", true);
        }
        $out->item = $app->vars("_post");
        $out->item["_dir_"] = $out->path;
        $out->fetch();
        if (count($app->vars("_post"))) {
            if ($app->vars("_sett.modules.login.loginby") == "phone") {
                $fld = "phone";
            }
            if ($app->vars("_sett.modules.login.loginby") == "email") {
                $fld = "email";
            }
            if ($app->vars("_sett.modules.login.loginby") == "userid") {
                $fld = "login";
            }

            $user = modLoginCheckUser($app->vars("_post.{$fld}"));
            if ($user) {
                $out->find("#signup .signup-wrong")->removeClass("d-none");
                if ($user->active == "on") {
                    $out->find("#signup .signup-wrong .signup-wrong-ia")->remove();
                }
            } else {
                $app->vars("_post.password", wbPasswordMake($app->vars("_post.password")));
                $user=array(
               "id"               => wbNewId()
              ,"active"           => ""
              ,"role"             => "user"
          );
                if ($app->vars("_sett.modules.login.loginby") == "userid") {
                    $user["id"] = $app->vars("_post.{$fld}");
                    unset($_POST["login"]);
                }
                if ($app->vars("_sett.modules.login.status")) {
                    $user["active"] = $app->vars("_sett.modules.login.status");
                }
                if ($app->vars("_sett.modules.login.group")) {
                    $user["role"] = $app->vars("_sett.modules.login.group");
                }
                $user = $app->postToArray($user);
                $app->itemSave("users", $user);
                header('Location: /signin');
            }
        }
        $out->find("#signin")->removeClass("show active");
        $out->find("#signup")->addClass("show active");
        $out->find("#signin-tab")->removeClass("active");
        $out->find("#signup-tab")->addClass("active");
        return $out;
    }

    public function login__signout(&$app)
    {
        $user = wbArrayToObj($app->vars("_env.user"));
        $group = wbArrayToObj($app->itemRead("users", $user->role));
        $app->vars->set("_sess.user", null);
        $app->vars->set("_env.user", null);
        setcookie("user", "", time()-3600, "/");

        if ($group->logout_url > "") {
            header('Location: '.$group->logout_url);
        } else {
            header('Location: /');
        }
        die;
    }

    public function login__recover(&$app)
    {
        $out = $app->getTpl("login_ui.php");
        if (!$out) {
            $out = $app->fromFile(__DIR__."/login_ui.php", true);
        }
        $out->item = $app->vars("_post");
        $out->item["_dir_"] = $out->path;
        $out->fetch();
        $out->find("#signin")->removeClass("show active");
        $out->find("#recovery")->addClass("show active");
        $out->find("#signin-tab")->removeClass("active");
        $out->find("#recovery-tab")->addClass("active");
        return $out;
    }


    public function modLoginSuccess(&$app, $user)
    {
        if ($user->avatar > "") {
            if ($user->avatar->length) {
                $user->avatar = $user->avatar[0];
            }
            $user->avatar="/uploads/users/{$user->id}/{$user->avatar->img}";
        } else {
            $user->avatar = "/engine/tpl/img/person.svg";
        }
        if ($user->group->logout_url == "") {
            $user->group->logout_url = "/";
        }
        if ($user->group->login_url == "") {
            $user->group->login_url = "/";
        }
        unset($user->password);
        $app->vars("_sess.user", wbObjToArray($user));
        $app->vars("_env.user", wbObjToArray($user));
        setcookie("user", $user->id, time()+3600);
        $app->user = $user;
        return $user;
    }

    public function modLoginCheckUser($login=null, $pass=null)
    {
        $app = $_ENV["app"];
        $fld = "id";
        if (is_email($login)) {
            $fld = "email";
        }
        if ($app->vars("_sett.modules.login.loginby") == "phone") {
            $fld = "phone";
        }
        if ($app->vars("_sett.modules.login.loginby") == "email") {
            $fld = "email";
        }
        if ($app->vars("_sett.modules.login.loginby") == "userid") {
            $fld = "id";
        }
        $users = wbItemList("users", $fld . ' = "'.$login.'" AND isgroup != "on" AND active = "on"');
        if (!count($users)) {
            return false;
        }
        $user = wbArrayToObj(array_shift($users));
        $group = wbArrayToObj(wbItemRead("users", $user->role));
        $user->group = $group;
        if ($pass == null) {
            return $user;
        }
        if ($group->active == "on" and wbPasswordCheck($pass, $user->password)) {
            return $user;
        }
        return false;
    }


    public function __engineRecoveryPassword(&$app)
    {
        $out = $app->getTpl("login_ui.php");
        if (!$out) {
            $out = $app->fromFile(__DIR__."/login_ui.php", true);
        }
        $out->fetch();
        if (isset($_POST["l"]) and $_POST["l"]>"") {
            if (strpos($_POST["l"], "@")) {
                $users=wbItemList("users", 'email="'.$_POST["l"].'"');
                foreach ($users as $key => $item) {
                    $_POST["l"]=$item["id"];
                    break;
                }
            }
            if ($user=wbItemRead("users", $_POST["l"])) {
                if (isset($user["lang"]) and $user["lang"]>"") {
                    $_SESSION["lang"]=$_ENV["lang"]=$user["lang"];
                }
                $user["pwdtoken"]=wbNewId();
                wbItemSave("users", $user, true);
                $letter=$out->find(".recovery-letter", 0);
                $link=$_ENV["route"]["hostp"]."/login/recovery/".base64_encode($user["password"].";".$user["email"].";".$user["pwdtoken"]);
                $letter->wbSetData(["link"=>$link]);
                $subject=$out->find(".signbox-header .recovery-block")->text();
                $res=wbMail($_ENV["settings"]["email"].";".$_ENV["settings"]["header"], $user["email"], $subject, $letter->outerHtml());
                $out->find(".recovery-block")->removeClass("d-none");
                $out->find('.main-block')->addClass('d-none');
                $out->find('.recovery-password')->addClass('d-none');
                $out->find('.login-block')->addClass('d-none');
                $out->wbSetData(["email"=>$user["email"],"site"=>$_ENV["settings"]["header"]]);
                if ($res) {
                    $out->find(".recovery-info")->removeClass("d-none");
                    echo $out;
                } else {
                    $out->find(".recovery-wrong")->removeClass("d-none");
                    echo $out;
                }
            } else {
                header('Location: /login');
            }
        }
        die;
    }
}
