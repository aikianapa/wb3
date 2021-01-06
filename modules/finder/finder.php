<?php
class modFinder
{
    public function __construct($obj)
    {
        $this->init($obj);
    }
    public function init($obj)
    {

        strtolower(get_class($obj)) == 'wbapp' ? $app = &$obj : $app = &$obj->app;
        $this->app = &$app;
        
        if (isset($app->route->mode) and $app->route->mode !== 'init' and $app->route->module == 'finder') {
            $mode = $app->route->mode;
            try {
                echo $this->$mode();
            } catch (\Throwable $err) {
                echo $err;
            }
            die;
        } else {
            $dom = &$obj;
            $out = $dom->app->fromFile(__DIR__ ."/finder_ui.php");

            $dom->attr('name') > '' ? $out->find('input[type=hidden]')->attr('name', $dom->attr('name')) : null;
            $dom->attr('onchange') > '' ? $out->find('input[type=hidden]')->attr('onchange', $dom->attr('onchange')) : null;

            $dom->attr('placeholder') > '' ? $out->find('input[type=search]')->attr('placeholder', $dom->attr('placeholder')) : null;
            $dom->attr('class') > '' ? $out->find('input[type=search]')->addClass('class', $dom->attr('class')) : null;
            $dom->attr('data-ajax') > '' ? $dom->params->finder = wbAttrToValue($dom->attr('data-ajax')) : null;
            $tpl = $dom->inner();
            $dom->find('script')->remove();

            $dom->params->finder['tpl'] = base64_encode($dom->inner());

            $out->find('input[type=search]')->attr('data-params', wb_json_encode($dom->params));
            $out->fetch();
            $dom->after($out);
            $dom->remove();
        }
    }

    function get() {
        $finder = $this->app->vars('_post.finder');
        $url = trim($finder['url']);
        $tpl = $this->app->fromString(base64_decode($finder['tpl']));
        $tpl->attr('data-finder-id', '{{id}}');
        $tpl->addClass('dropdown-item');
        if (!strpos('http://', ' '.strtolower($url)) && !strpos('https://', ' '.strtolower($url))) {
            $url = $this->app->route->host. $url;
        }
        $data = wbAuthPostContents($url, ['_context*'=>$finder['value']]);
        $data = json_decode($data,true);
        if (!isset($data['result']) && !isset($data['pages']) && !isset($data['pagination'])) {
            $data = ['result' => $data];
        }
        $out = $this->app->fromString("<html><wb-foreach wb='from=result'>{$tpl}</wb-foreach></html>");
        $out->fetch($data);
        echo $out->inner();
    }

    function ajax() {
        
    }
}
?>