<?php
class modLang
{
    public function __construct($dom)
    {
        $this->init($dom);
    }
    public function init($dom)
    {
		$app = &$_ENV['app'];
		$langs = wbListLocales($app);
		!count($langs) ? $langs = ['en','ru'] : null;
		$lang = $langs[0];
		$p = $app->vars('_route.mode');
		in_array($p, $langs) ? $lang = $p : null;
		$_SESSION["lang"] = $_ENV["lang"] = $lang;
        $check=explode("/", $_SERVER["REQUEST_URI"]);
		session_write_close();
        Header("HTTP/1.0 200 OK");
        header("Refresh:0; url=".$_SERVER["HTTP_REFERER"]);
        exit;
    }
}
?>
