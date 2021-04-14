<?php

class modFormbuilder
{
    public function __construct($obj)
    {
		strtolower(get_class($obj)) == 'wbapp' ? $app = &$obj : $app = &$obj->app;
		$this->app = &$app;
		$this->sett = $app->vars('_sett.modules.formbuilder');
		$mode=$app->route->mode;
		//if (!wbRole("admin")) return;
		if (isset($app->route->mode)) {
			if (method_exists($this,$mode)) {
				echo @$this->$mode();
			}
		} else {
			echo $this->init($obj);
		}
		die;
    }


function init()
{
        $out=$this->app->fromFile(__DIR__ ."/formbuilder_ui.php");
        $out->fetch(["snippets"=>$this->getsnippets(),"modset"=>$this->sett]);
        return $out;
}

/*
function css() {
	if (is_file(__DIR__.'/css/'.$this->app->route->params[0])) {
		echo file_get_contents(__DIR__.'/css/'.$this->app->route->params[0]);
	}
}
*/

function snipview() {
	$type = $this->app->route->params[0];
	$snip = $this->app->route->params[1];
	$data = $this->app->treeFindBranch($this->sett['prop']['data'],"{$type}->{$snip}");
	$data = $data[0]['data']['snipcode'];
	$out = $this->app->fromString('<html><head></head><body></body></html>');
	
	$this->snipheads($out);
	$out->find('head')->append('<link rel="stylesheet" href="/engine/modules/formbuilder/css/miniature.less" />');
	$out->find('body')->addClass($type);
	$out->find('body')->attr('miniature', true);
	$out->find('body')->html($data);
	$out->fetch();
	return $out->outer();
}

function snipheads($out) {
	$out->find('head')->append('<link rel="stylesheet" href="/engine/lib/bootstrap/css/bootstrap.min.css" />');
	$out->find('head')->append('<link rel="stylesheet" href="/engine/modules/formbuilder/css/formbuilder.less" />');

	$out->find('head')->append('<script src="/engine/js/jquery.min.js" />');
	$out->find('head')->append('<script src="/engine/lib/bootstrap/js/bootstrap.bundle.min.js" />');
}


function savestate() {
	$this->app->apikey();
	$cache = $this->app->vars('_env.dbac').'/modFormbuilder/'.md5($this->app->user->_id).'.html';
	$res = $this->app->putContents($cache,$this->app->vars('_post.page'));
}

function loadstate() {
	$this->app->apikey();
	$cache = $this->app->vars('_env.dbac').'/modFormbuilder/'.md5($this->app->user->_id).'.html';
	if (is_file($cache)) {
		$out = $this->app->fromFile($cache,true);
	} else {
		$out = $this->app->fromFile(__DIR__ . '/formbuilder_view.php');
		$this->snipheads($out);
	} 
	$out->fetch();
	return $out->outer();
}
/*
function formbuilder__locale() {
        $out=wbFromString(file_get_contents(__DIR__ ."/formbuilder_ui.php"));
        $locale=$out->wbGetFormLocale();
        return base64_encode(json_encode($locale[$_SESSION["lang"]]));
}

function formbuilder__create() {
	$formName=strtolower($_ENV["route"]["params"][1]);
	$formPath=$_ENV["path_app"]."/forms/".$formName;
	$res=false;
	if (!is_dir($formPath)) { mkdir($formPath);
		$modes=array("","edit","list");
		foreach($modes as $mode) {
			if ($mode>"") {
				$form=file_get_contents($_ENV["path_engine"]."/forms/common/common_{$mode}.php");
				file_put_contents("{$formPath}/{$formName}_{$mode}.php",$form);
			} else {
				$prog=file_get_contents($_ENV["path_engine"]."/forms/common/common_prog.php");
				$prog=str_replace("%form%",$formName,$prog);
				file_put_contents("{$formPath}/{$formName}.php",$prog);
			}
		}
		$res=true;
	}
	return json_encode($res);
}


function formbuilder__getmodelist() {
	$res=array();
	$formName=strtolower($_ENV["route"]["params"][1]);
	$list=glob("{$_ENV['path_engine']}/forms/{$formName}/*.php");
	foreach($list as $name) {
		$name=array_pop(explode("/",$name));
		if (!in_array($name,$res)) $res[$name]=array("formname"=>$formName,"filename"=>$name,"filepath"=>"/forms/{$formName}/{$name}");
	}
	$list=glob("{$_ENV['path_app']}/forms/{$formName}/*.php");
	foreach($list as $name) {
		$name=array_pop(explode("/",$name));
		if (!in_array($name,$res)) $res[$name]=array("formname"=>$formName,"filename"=>$name,"filepath"=>"/forms/{$formName}/{$name}");
	}
	$list=glob("{$_ENV['path_engine']}/forms/{$formName}/*.ini");
	foreach($list as $name) {
		$name=array_pop(explode("/",$name));
		if (!in_array($name,$res)) $res[$name]=array("formname"=>$formName,"filename"=>$name,"filepath"=>"/forms/{$formName}/{$name}");
	}
	$list=glob("{$_ENV['path_app']}/forms/{$formName}/*.ini");
	foreach($list as $name) {
		$name=array_pop(explode("/",$name));
		if (!in_array($name,$res)) $res[$name]=array("formname"=>$formName,"filename"=>$name,"filepath"=>"/forms/{$formName}/{$name}");
	}
	return json_encode($res);
}

function formbuilder__getform() {
	$formName=strtolower($_ENV["route"]["params"][1]);
	$formFile=$_POST["formFile"];
	$form=glob("{$_ENV['path_app']}/forms/{$formName}/{$formFile}");
	if (!count($form)) {
		$form=glob("{$_ENV['path_engine']}/forms/{$formName}/{$formFile}");
	}
	if (isset($form[0]) AND is_file($form[0])) {
		$form=file_get_contents($form[0]);
	} else {
		$form = false;
	}
	return base64_encode(json_encode($form));
}

*/

function getsnippets() {
	$snippets = wbTreeRead("formbuilder_snippets");
	if (!$snippets) return [];
	return $snippets["tree"];
}

}
?>