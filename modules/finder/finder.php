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
            exit;
        } else {
            $dom = &$obj;
            $out = $dom->app->fromFile(__DIR__ ."/finder_ui.php");
            $dom->attr('name') > '' ? $out->find('input')->attr('name', $dom->attr('name')) : null;
            $dom->attr('onchange') > '' ? $out->find('input')->attr('onchange', $dom->attr('onchange')) : null;
            $dom->attr('value') > '' ? $out->find('input')->attr('value', $dom->attr('value')) : null;

            $dom->attr('placeholder') > '' ? $out->find('input')->attr('placeholder', $dom->attr('placeholder')) : null;
            $dom->attr('class') > '' ? $out->find('input')->addClass('class', $dom->attr('class')) : null;
            $dom->attr('data-ajax') > '' ? $dom->params->finder = wbAttrToValue($dom->attr('data-ajax')) : null;
            $tpl = $dom->inner();
            $dom->find('script')->remove();

            $dom->params->finder['tpl'] = base64_encode($dom->inner());

            $out->find('input')->attr('data-params', wb_json_encode($dom->params));

            $dom->after($out);
            $dom->remove();
        }
    }

    function get() {
        $finder = $this->app->vars('_post.finder');
        if ($finder == '') return;
        $url = trim($finder['url']);
        $tpl = $this->app->fromString(base64_decode($finder['tpl']));
        $tpl->addClass('dropdown-item');
        if (!strpos('http://', ' '.strtolower($url)) && !strpos('https://', ' '.strtolower($url))) {
            $url = $this->app->route->host. $url;
        }
        $options = $finder;
        unset($options['url']);
        unset($options['tpl']);
        unset($options['value']);
        $options = http_build_query($options);
        $options = str_replace(['&','%2C'], [';',','],$options);
        $data = wbAuthPostContents($url, ['_context*'=>$finder['value'],'__options'=>$options]);
        $data = json_decode($data,true);
        if (!isset($data['result']) && !isset($data['pages']) && !isset($data['pagination'])) {
            $data = ['result' => $data];
        }
        $out = $this->app->fromString("<html><wb-foreach wb='from=result'>{$tpl}</wb-foreach></html>");
        $out->fetch($data);
        $data['render'] = $out->inner();
        header('Content-Type: charset=utf-8');
        header('Content-Type: application/json');
        echo wb_json_encode($data);
    }
}
?>