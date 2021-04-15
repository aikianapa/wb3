<?php

class modFormbuilder
{
    public function __construct($obj)
    {
		strtolower(get_class($obj)) == 'wbapp' ? $app = &$obj : $app = &$obj->app;
		$this->app = &$app;
		$this->sett = $app->vars('_sett.modules.formbuilder');
		if (!$this->sett) {
			$this->sett = file_get_contents(__DIR__ ."/formbuilder.json");
			$this->sett = json_decode($this->sett, true);
			$this->sett = $this->sett['formbuilder'];
		}
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
	$out = '';
	if (is_file($cache)) {
		$out = $this->app->fromFile($cache,true);
	} 
	if ($out == '') {
		$out = $this->app->fromFile(__DIR__ . '/formbuilder_view.php');
		$this->snipheads($out);
	} 
	$out->fetch();
	return $out->outer();
}


function getsnippets() {
	$snippets = wbTreeRead("formbuilder_snippets");
	if (!$snippets) return [];
	return $snippets["tree"];
}

}
?>