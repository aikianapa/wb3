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


        parse_str($params->change, $ini);
        if (isset($ini['filter']) && isset($ini['target'])) {
            return $this->filter($dom, $ini);
        }


        $chlist = explode(',', $params->change);
        $sl = [];
        $fn = [];
        foreach ($chlist as $selector) {
            $selector = trim($selector);
            $selector = str_replace("'", '"', $selector);
            try {
                if ($dom->parents()) {
                    $tpl = $dom->parents()->find($selector);
                } else {
                    $tpl = $dom->find($params->change);
                }
            } catch (\Throwable $th) {
                echo "<p class='alert alert-danger'>Wrong wb-change selector: {$selector}<br>in: {$app->vars('_route.uri')}</p>";
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
            $sl[] = "'".$selector."'";
            $fn[] = "'".$fname."'";
        }

        $sl = implode(',', $sl);
        $fn = implode(',', $fn);

        $onchange = $dom->attr('onchange');
        $onchange .= ';$(this).wbAttrChange(['.$sl.'],['.$fn.']);';
        $dom->attr('onchange', $onchange);
        $dom->append("
        <script type='wbapp'>
        wbapp.loadScripts(['/engine/attrs/change/change.js'],'wb-change-js');
        </script>
        ");
    }

    public function filter(&$dom, $ini)
    {
        $onclick = $dom->attr('onclick');
        if (isset($ini['clear']) && !in_array($ini['clear'], ['false','0'])) {
            $onclick .= ';$(this).wbFilterChange("'.$ini['filter'].'","'.$ini['target'].'","clear");';
        } else {
            $onclick .= ';$(this).wbFilterChange("'.$ini['filter'].'","'.$ini['target'].'");';
        }
        $dom->attr('onclick', $onclick);
        $dom->append("
        <script type='wbapp'>
        wbapp.loadScripts(['/engine/attrs/change/filter.js'],'wb-change-filter-js');
        </script>
        ");
    }
}
