<?php

use Adbar\Dot;

class attrChange
{
    public function __construct($dom)
    {
        $this->change($dom);
    }

    public function change($dom)
    {
        $params = $dom->params;
        $app = &$dom->app;
        $dom->removeAttr('wb-change');

        try {
            if (is_object($dom->parents())) {
                $tpl = $dom->parents()->find($params->change);
            } else {
                $tpl = $dom->find($params->change);
            }
        } catch (\Throwable $th) {
            echo "<p class='alert alert-danger'>Wrong wb-change selector: {$params->change}<br>in: {$app->vars('_route.uri')}</p>";
            die;
        }

        $cache = [
                    'tpl' => $tpl->outer(),
                    'route' => $app->vars('_route'),
                    'locale' => $dom->locale
                ];
        $fname = md5(wb_json_encode($cache));
        $dir = $app->vars('_env.dbac').'/tmp';

        $cache['session'] = $_SESSION;
        $cache['item'] = $dom->item;
        $cache = wb_json_encode($cache);


        if (!is_dir($dir)) {
            mkdir($dir, 0766, true);
        }
        file_put_contents($dir.'/'.$fname, $cache);
        $onchange = $dom->attr('onchange');
        $onchange .= ';$(this).wbAttrChange("'.$params->change.'","'.$fname.'");';
        $dom->attr('onchange', $onchange);
        $dom->append("
        <script type='wbapp'>
        wbapp.loadScripts(['/engine/attrs/change/change.js'],'wb-change-js');
        </script>
        ");
    }
}
