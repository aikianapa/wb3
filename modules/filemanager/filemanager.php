<?php
class modFilemanager
{
    public function __construct($obj)
    {
        $this->init($obj);
    }
    public function init($obj)
    {
        strtolower(get_class($obj)) == 'wbapp' ? $app = &$obj : $app = &$obj->app;
        $app->apikey('module');
        $this->app = &$app;
        if (isset($app->route->mode) AND $app->route->mode !== 'init') {
            $mode = $app->route->mode;
            try {
                echo $this->$mode();
            } catch (\Throwable $err) {
                echo $err;
            }
            die;
        } else {
            $out = $app->fromFile(__DIR__ ."/filemanager_ui.php");
            $out->fetch();
            echo $out;
            die;
        }
    }

    public function getdir($result=false)
    {
        if ($this->allow()) {
            $app = &$this->app;
            $list=array();
            $dir = $_ENV["path_app"];
            if ($_ENV["route"]["params"]["dir"]>"") {
                $dir.=$_ENV["route"]["params"]["dir"];
                $dir=str_replace("..", "", $dir);
                @$list[] = array("type"=>"back","path"=>$back,"link"=>false,"ext"=>$ext,"name"=>"..");
            }
            if ($dircont = scandir($dir)) {
                $i=0;
                $idx=0;
                while (isset($dircont[$i])) {
                    if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
                        $current_file = "{$dir}/{$dircont[$i]}";
                        $ext=pathinfo($current_file, PATHINFO_EXTENSION);
                        $link=is_link($current_file);
                        $modif = date ("d.m.y H:i", filemtime($current_file));
                        $href=$_ENV["route"]["params"]["dir"]."/".$dircont[$i];
                        $perms=substr(sprintf('%o', fileperms($current_file)), -4);
                        if (is_file($current_file)) {
                            $size=filesize($current_file);
                            if ($size>1024*1024*1024) {
                                $size=sprintf("%u", $size/(1024*1024*1024))."Гб";
                            } elseif ($size>1024*1024) {
                                $size=sprintf("%u", $size/(1024*1024))."Мб";
                            } elseif ($size>1024) {
                                $size=sprintf("%u", $size/1024)."Кб";
                            } else {
                                $size.="";
                            }
                            $list[] = array("type"=>"file","path"=>$dir,"modif"=>$modif,"perms"=>$perms,"size"=>$size,"href"=>$href,"link"=>$link,"ext"=>$ext,"name"=>$dircont[$i],'wr'=>is_writable($current_file));
                        }
                        if (is_dir($current_file)) {
                            $list[] = array("type"=>"dir","path"=>$dir,"modif"=>$modif,"perms"=>$perms,"size"=>"-","href"=>$href,"link"=>$link,"ext"=>"DIR","name"=>$dircont[$i],'wr'=>is_writable($current_file));
                        }
                    }
                    $i++;
                }
            }
            $path = explode("/", $_ENV["route"]["params"]["dir"]);
            unset($path[0]);
            $arr=array(
            "result"=> $this->app->arraySort($list, "type,name"),
            "path"=>$path
        );
            if ($result==true) {
                return $arr;
            } else {
                $out = $this->app->getTpl("/engine/modules/filemanager/filemanager_ui.php", true);
                $out->fetch($arr);
                $out = $out->find("#panel", 0);
                echo $out;
            }
        }
    }

    public function getfile()
    {
        if ($this->allow() && isset($_POST["file"])) {
            $file = $_ENV["path_app"].$_POST["file"];
            if (is_file($file)) {
                $text = file_get_contents($_ENV["path_app"].$_POST["file"]);
                $mime = wbMime($file);
                //$text = $mime . ':' . $text;
                echo $text;
            }
        }
    }

    public function putfile()
    {
        $res=false;
        if (wbRole("admin") && isset($_POST["file"]) && isset($_POST["text"])) {
            $file=$_ENV["path_app"].$_POST["file"];
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $code = $_POST["text"];
            if ($ext == "php" && substr(trim(escapeshellarg($code)), 1, 2)=="<?") {
                // если форма перезаписывается из engine в app приводим в порядок имена функций
                if (substr($file, 0, strlen($_ENV["path_engine"]."/forms/")) !== $_ENV["path_engine"]."/forms/") {
                    $mask='`(function\W?[\w\d\-\_]+__[\w\d\-\_]+)|(function\W?_[\w\d\-\_]+)`u';
                    preg_match_all($mask, $code, $res, PREG_OFFSET_CAPTURE);
                    if (isset($res[0])) {
                        foreach ($res[0] as $i => $func) {
                            $func=$func[0];
                            $func=array_pop(explode(" ", trim($func)));
                            $newf=str_replace("__", "_", $func);
                            if (substr($newf, 0, 1) == "_") {
                                $newf=substr($newf, 1);
                            }
                            $code=str_replace($func, $newf, $code);
                        }
                    }
                }
                $res=wbCheckPhpCode($code);
                if (!$res) {
                    $res=array("result"=>false,"error"=>"invalid");
                    echo json_encode($res);
                    die;
                }
            }
            $path=explode("/", $file);
            array_pop($path);
            $path=implode("/", $path);
            $umask=umask(0);
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $res=file_put_contents($file, $code);
            umask($umask);
            if ($res) {
                $res=array("result"=>true,"error"=>"success");
            } else {
                $res=array("result"=>false,"error"=>"unknown");
            }
        }
        echo json_encode($res);
    }

    public function dialog()
    {
        if ($this->allow()) {
            $out = $this->app->getTpl("/engine/modules/filemanager/filemanager_ui.php", true);

            $action = $this->app->route->params[0];

            $tpl = $out->find("template[name={$action}]");
            
            $out->find("#filemanagerModalDialog .modal-title")->inner($tpl->find('title')->inner());
            $tpl->find('title')->remove();
            $out->find("#filemanagerModalDialog .modal-body form")->inner($tpl->inner());
            $out->find("#filemanagerModalDialog .modal-footer .btn-primary")->attr("data-action", $action);

            $out->fetch();

            $out = $out->find("#filemanagerModalDialog");
            
            if ($action=="paste") {
                $this->action_paste($out);
            } elseif ($action=="zip") {
                $this->action_zip($out);
            } else {
                echo $out;
            }

            die;
            // ======================================================

            $title = $out->find("meta[name={$action}]")->attr("title");
            $content = $this->app->fromString(html_entity_decode($out->find("meta[name=$action]")->attr("content")));
            $visible = $out->find("meta[name={$action}]")->attr("visible");
            $invisible = $out->find("meta[name={$action}]")->attr("invisible");
            $fields = [];
            

            foreach ($out->find("#filemanagerModalDialog [name]:not(meta)") as $inp) {
                $fld=$inp->attr("name");
                //$inp->setAttributes();
                if ($inp->is("input") and isset($visible) and in_array($fld, wbAttrToArray($visible))) {
                    $inp->attr("type", "text");
                }
                if ($inp->is("input") and isset($invisible) and in_array($fld, wbAttrToArray($invisible))) {
                    $inp->attr("type", "hidden");
                }
                $content->append($inp);
            }
            echo $content; die;
            $out->find("meta,input")->remove();

            $out->find("#filemanagerModalDialog .modal-title")->inner($title);
            $out->find("#filemanagerModalDialog .modal-body form")->inner($content);
            $out->find("#filemanagerModalDialog .modal-footer .btn-primary")->attr("data-action", $action);
            $out->fetch();
            $out = $out->find("#filemanagerModalDialog");

            if ($action=="paste") {
                $this->action_paste($out);
            } elseif ($action=="zip") {
                $this->action_zip($out);
            } else {
                echo $out;
            }
        }
    }

    public function action_zip($out=null)
    {
        $flag=true;
        if ($out!==null) {
            $out->find("[name=filename]")->attr("value", "archive.zip");
            echo $out->outer();
        } else {
            $path=$_ENV["path_app"].$_POST["path"];
            $zipname=$_POST["filename"];
            $src="";
            foreach ($_POST["list"] as $item) {
                $src.=" ".$item;
            }
            if (is_file($path."/".$zipname)) {
                unlink($path."/".$zipname);
            }
            exec("cd {$path} && zip -o -D -r {$zipname} ".$src);
            echo json_encode(array("res"=>$_POST["list"],"action"=>"reload_list"));
        }
        die;
    }


    public function action_unzip($out=null)
    {
        $flag=true;
        if ($out!==null) {
            echo $out->outer();
        } else {
            $path=$_ENV["path_app"].$_POST["path"];
            foreach ($_POST["list"] as $zipname) {
                exec("cd {$path} && unzip -q -o {$zipname} ");
            }
            echo json_encode(array("res"=>$_POST["list"],"action"=>"reload_list"));
        }
        die;
    }

    public function action_paste($out=null)
    {
        $flag=true;
        if ($out!==null) {
            $_ENV["route"]["params"]["dir"]=$_POST["path"];
            $cur = $this->getdir(true);
            $arr = [];
            foreach ($cur["result"] as $item) {
                if ($item["type"]!=="back") {
                    $arr[]=$item["name"]."::".$item["type"];
                }
            }
            foreach ($_POST["list"] as $i => $item) {
                $check=$item["name"]."::".$item["type"];
                if (in_array($check, $arr)) {
                    $flag=false;
                    break;
                }
            }
            if (!$flag) {
                echo json_encode(array("res"=>"dialog","action"=>$out->outer(),"post"=>$_POST));
                die;
            }
        }
        if ($flag==true) {
            foreach ($_POST["list"] as $i => $item) {
                $dst=$_ENV["path_app"].$_POST["path"]."/".$item["name"];
                $src=$_ENV["path_app"].$item["path"]."/".$item["name"];
                if ($_POST["method"]=="cut") {
                    rename($src, $dst);
                }

                if ($_POST["method"]=="copy") {
                    if (file_exists($dst)) {
                        $dst=$this->copyname($dst);
                    }
                    wbRecurseCopy($src, $dst);
                }
            }
            echo json_encode(array("res"=>$_POST["method"],"action"=>"reload_list"));
        }
        die;
    }

    public function copyname($name)
    {
        // тут нужно не просто конкатенировать, а вставить с расширением
        for ($i=0 ; $i<100 ; $i++) {
            $name.="_copy";
            if (!file_exists($name)) {
                return $name;
            }
        }
        return $name;
    }

    public function action_remove($out=null)
    {
        $_POST["list"]=json_decode($_POST["list"], true);
        foreach ($_POST["list"] as $i => $item) {
            $dst=$_ENV["path_app"].$_POST["path"]."/".$item["name"];
            $engine = $this->check_engine($dst); // если дирректория движка, то не даём удалять
            if (!$engine and is_dir($dst)) {
                wbRecurseDelete($dst);
            }
            if (!$engine and is_file($dst)) {
                wbFileRemove($dst);
            }
        }
        echo json_encode(array("res"=>$_POST["list"],"action"=>"reload_list"));
        die;
    }

    public function check_engine($path)
    {
        $res=false;
        $check=strpos("#".$path."/", $_ENV["path_app"]."/engine/");
        if ($check) {
            $res=true;
        }
        return $res;
    }

    public function action()
    {
        if (!$this->allow()) {
            return json_encode(false);
        }
        $this->action = $this->app->route->params[0];
        $call = "action_".$this->action;
        if (method_exists($this,$call)) {
            $res = $this->$call();
            if (is_array($res)) {
                echo json_encode($res);
            } else {
                echo json_encode(false);
            }
        }
        die;
    }

    public function action_multi()
    {
        return null;
    }


    public function action_newdir()
    {
        $res=false;
        if (!$this->allow()) {
            return $res;
        }
        $action = $this->app->route->params[0];
        $dir = $_ENV["path_app"].$_POST["path"];
        $newname=$_POST["newname"];
        $path=$dir."/".$newname;
        if (!is_file($path) AND !is_dir($path) and $newname>"") {
            $umask=umask(0);
            $res=mkdir($path, 0777, true);
            umask($umask);
        }
        if (!$res) {
            return $res;
        }
        return array(
        "res"=>$res,
        "name"=>$newname,
        "type"=>"dir",
        "action"=>"reload_list"
    );
    }

    public function action_newfile()
    {
        $res=false;
        if (!$this->allow()) {
            return $res;
        }
        $action=$this->app->route->params[0];
        $dir=$_ENV["path_app"].$_POST["path"];
        $newname=$_POST["newname"];
        $path=$dir."/".$newname;
        if (!is_file($path) AND !is_dir($path) and $newname>"") {
            $res=file_put_contents($path, "");
        }
        if (is_file($path)) {
            return array(
                "res"=>$res,
                "name"=>$newname,
                "type"=>"file",
                "ext"=> pathinfo($path, PATHINFO_EXTENSION),
                "action"=>"reload_list"
            );
        }
    }

    public function action_rmdir()
    {
        $res=false;
        if (!$this->allow()) {
            return $res;
        }
        $action=$this->app->route->params[0];
        $dir=$_ENV["path_app"].$_POST["path"]."/".$_POST["dirname"];
        if (is_dir($dir) and !is_link($dir)) {
            $res=wbRecurseDelete($dir);
        }
        if (is_dir($dir) && $res!==false) {
            return $res;
        } else {
            return array(
            "res"=>$res,
            "name"=>$_POST["dirname"],
            "type"=>"dir",
            "action"=>"reload_list"
        );
        }
    }

    public function action_rmfile()
    {
        $res=false;
        if (!$this->allow()) {
            return $res;
        }
        $action=$this->app->route->params[0];
        $file=$_ENV["path_app"].$_POST["path"]."/".$_POST["filename"];
        if (is_file($file) and !is_link($file)) {
            $res=wbFileRemove($file);
        }
        if (is_file($file) and $res!==false) {
            return $res;
        } else {
            return array(
            "res"=>$res,
            "name"=>$_POST["filename"],
            "type"=>"dir",
            "action"=>"reload_list"
        );
        }
    }

    public function action_renfile()
    {
        $res=false;
        if (!$this->allow()) {
            return $res;
        }
        $action=$this->app->route->params[0];
        $oldfile=$_ENV["path_app"].$_POST["path"]."/".$_POST["oldname"];
        $newfile=$_ENV["path_app"].$_POST["path"]."/".$_POST["filename"];
        if (is_file($oldfile) and !is_file($newfile) and $_POST["filename"]>"") {
            $res=rename($oldfile, $newfile);
        }
        if (!is_file($newfile) or $res==false) {
            return $res;
        } else {
            return array(
            "res"=>$res,
            "name"=>$_POST["filename"],
            "type"=>"file",
            "ext"=> pathinfo($newfile, PATHINFO_EXTENSION),
            "action"=>"change_name"
        );
        }
    }

    public function action_rendir()
    {
        $res=false;
        if (!$this->allow()) {
            return $res;
        }
        $action=$this->app->route->params[0];
        $olddir=$_ENV["path_app"].$_POST["path"]."/".$_POST["oldname"];
        $newdir=$_ENV["path_app"].$_POST["path"]."/".$_POST["dirname"];
        if (is_dir($olddir) and !is_dir($newdir) and $_POST["dirname"]>"") {
            $res=rename($olddir, $newdir);
        }
        if (!is_dir($newdir) or $res==false) {
            return $res;
        } else {
            return array(
            "res"=>$res,
            "name"=>$_POST["dirname"],
            "type"=>"dir",
            "action"=>"change_name"
        );
        }
    }

    public function allow()
    {
        //return true;
        return $this->app->role("admin");
    }
}
?>