<?php

use Nahid\JsonQ\Jsonq;

class pagesClass extends cmsFormsClass
{

    public $app;
    public $tables;
    public $list;
    public $count;
    public $tpl;

    function beforeItemShow(&$item)
    {
        $item = (array)$item;
        $data = $this->app->dot($item);
        @$header = $item['header'];
        if ($this->app->vars('_route.mode')=='show') {
            isset($item['lang']) ? $lang = $item['lang'][$this->app->vars('_sess.lang')] : $lang = [];
            $item = (array)$lang + $item;
            $item['header'] == '' ? $item['header'] = $header : null;
        }
        if ((array)$item['header'] === $item['header']) {
            @$item['header'] = isset($item['header'][$_SESSION['lang']]) ? $item['header'][$_SESSION['lang']] : $item['header']['ru'];
        }
        @$item['menu_title'] = $data->get('menu_title'.$_SESSION['lang']) ? $data->get('menu_title'.$_SESSION['lang']) :  $data->get('menu_title.ru');
        $item['menu_title'] == '' ? $item['menu_title'] = $item['header'] : null;
        $blocks = (array)$data->get('blocks');
        $tmp = array_column($blocks, "name");
        if (in_array("seo", $tmp)) {
            $seo = $this->app->dot($blocks[array_search("seo", $tmp)]);
            if ($seo->get('active') == 'on') {
                $item['seo'] = 'on';
                $item['meta_title'] = $seo->get("lang.{$_SESSION['lang']}.title");
                $item['meta_keywords'] = $seo->get("lang.{$_SESSION['lang']}.keywords");
                $item['meta_description'] = $seo->get("lang.{$_SESSION['lang']}.descr");
            }
        }
    }

    function beforeItemEdit(&$item)
    {
        $tables =  wbListTables();
        foreach ($tables as $k => $t) {
            if (substr($t, 0, 1) == '_' or $t == 'pages') {
                unset($tables[$k]);
            }
        }
        $this->app->vars('_var.attaches', $tables);
    }

    function afterItemRead(&$item)
    {
        if (!$item) return;
        isset($item['blocks']) ? null : $item['blocks'] = [];
        isset($item['container']) ? null : $item['container'] = '';
        isset($item['id']) ? null : $item['id'] = '';
        if (in_array($item['id'], ['_header', '_footer'])) return;
        isset($item['name']) ? null : $item['name'] = $item['id'];
        isset($item['path']) ? null : $item['path'] = '';
        if ($item['path'] == '/') $item['path'] = '';
        if (isset($item['blocks'])) $item['template'] = '';
        $item['url'] == '/home' ? $item['url'] = '/' : null;
        $item['url'] = $item['path'] . '/' . $item['name'];
        $item['name'] == 'home' ? $item['_sort'] = 0 : null;
        $this->app->vars('_route.action') == 'list' ? $this->beforeItemShow($item) : null;

    }

    function beforeItemSave(&$item)
    {
        $item['path'] == '/home' ? $item['path'] = '' : null;
        @$item['url'] = $item['path'] . '/' . $item['name'];
    }

    function afterItemSave(&$item)
    {
        $this->beforeItemShow($item);
    }

    function list1()
    {
        $app = &$this->app;
        $this->tables = $app->tableList();
        $this->jq = new Jsonq();
        $this->count = 0;
        $out = $app->fromFile(__DIR__ . '/list1.php');
        $this->tpl = $out->find('#pagesList');
        $this->list = $this->app->itemList('pages', ['return' => 'id,name,_form,header,active,attach,attach_filter,url,path,_sort']);
        $this->list = $this->list['list'];
        $res = $this->listNested();
        $out->find('#pagesList')->replaceWith($res);
        echo $out;
    }
    private function listNested($path = '')
    {
        $this->count++;
        if ($this->count > 1000) {
            return;
        }
        $out = $this->tpl->clone();
        $level = $this->app->json($this->list)->where('path', '=', $path)->sortBy('_sort')->get();
        $count = count($level);
        if (!$count) return '';
        $path > '' ? $out->removeAttr('id') : null;
        $out->fetch(['list' => $level]);

        foreach ($level as $item) {
            in_array($item['url'], ['/', '']) ? $url = '/home' : $url = $item['url'];
            $md5 = md5($url);
            unset($this->list[$item['id']]);
            $attach = (isset($item['attach']) and $item['attach'] > ' ') ? true : false;
            $res1 = $res2 = null;
            $res1 = $this->listNested($url);
            $res2 = $attach ? $this->listTable($item, $url) : null;

            $li = ($url == '/home') ? $out->find('li[data-path="/"]') : $out->find('li[data-path="' . $url . '"]');
            if ($res2 !== null) $li->append($res2);
            $li->find('template')->remove();
            ($url == '/home') ? $li->find('.dd-handle')->addClass('dd-home')->removeClass('dd-handle')->inner(' ') : null;
            $li->addClass('dd-collapsed');
            if ($attach) {
                $li->find('> ol .dd-add')->remove();
                $li->find('> ol .dd-handle')->remove();
                $li->find('> .dd-info .dd-add')->attr('data-inner', $item['attach']);
                $li->attr('data-inner', $item['attach']);
            }
            if ($res1 !== null) $li->append($res1);
        }
        $out->parent = $this;
        return $out;
    }

    private function listTable($item, $path = '')
    {
        $table = $item['attach'];
        $filter = (isset($item['attach_filter']) && $item['attach_filter'] > '') ? $item['attach_filter'] : [];
        if (is_string($filter)) {
            $filter = str_replace("'", '"', $filter);
            $filter = json_decode($filter, true);
        }
        $options = [
            'return' => 'id,name,_form,header,active,tags',
            'filter' => $filter
        ];
        $out = $this->tpl->clone();
        $class = $this->app->formClass($table);
        $level = $this->app->itemList($table, $options);
        $level = array_chunk($level['list'], 100);
        $level = $level[0];
        foreach ($level as $key => $item) {

            if (method_exists($class, 'beforeItemShow')) {
                $class->beforeItemShow($item);
            }

            isset($item['name']) ? null : $item['name'] = null;
            isset($item['header']) ? null : $item['header'] = $item['name'];
            $item['_form'] = $table;
            if ($item['header']) {
                $item['path'] = $path;
                $item['name'] = wbFurlGenerate($item['header']);
                $item['url'] = $item['path'] . '/' . $item['name'];
                $level[$key] = $item;
            } else {
                unset($level[$key]);
            }
        }
        $out->fetch(['list' => $level]);
        return $out;
    }

    function path()
    {
        $app = &$this->app;
        $data = $app->vars('_post.data');
        $form = &$data['form'];
        $items = &$data['items'];
        $list = $app->itemList($form, ['filter' => ['_id' => ['$in' => array_keys($items)]]]);
        foreach ($list['list'] as $item) {
            $id = $item['id'];
            $vals = &$items[$id];
            $item['_sort'] = wbSortIndex($vals['i']);
            $form == 'pages' ? $item['path'] = $vals['p'] : null;
            $app->itemSave($form, $item, false);
        }
        $app->tableFlush($form);
        $this->app->shadow('/module/yonger/yonmap');
        echo json_encode('true');
        exit;
    }
}
