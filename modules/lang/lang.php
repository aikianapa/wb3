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
        try {
            $p = $app->vars('_route.mode');
        } catch (\Throwable $th) {
            $p = 'ru';
        }
		in_array($p, $langs) ? $lang = $p : null;
		$_SESSION["lang"] = $_ENV["lang"] = $_COOKIE["lang"] = $lang;
        if ($app->vars('_route.params.0') == 'set') {
            $redirect = $_SERVER["HTTP_REFERER"];
        } else {
            $check=explode("/", $_SERVER["HTTP_REFERER"]);
            if (isset($check[3]) && in_array($check[3],$langs)) {
                $check[3] = $lang;
            } else {
                $check = $this->array_insert($check, 3, $lang);
            }
            $redirect = implode($check, '/');
        }
        
		session_write_close();
        Header("HTTP/1.0 200 OK");
        header("Refresh:0; url=".$redirect);
        exit;
    }

public function array_insert(&$array, $position, $insert)
{
    if (is_int($position)) {
        array_splice($array, $position, 0, $insert);
    } else {
        $pos   = array_search($position, array_keys($array));
        $array = array_merge(
            array_slice($array, 0, $pos),
            $insert,
            array_slice($array, $pos)
        );
    }
    return $array;
}


}
?>
