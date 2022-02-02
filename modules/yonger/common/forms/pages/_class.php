<?php
use Nahid\JsonQ\Jsonq;

class pagesClass extends cmsFormsClass {

function beforeItemShow(&$item) {
    isset($item['lang']) ? $lang = $item['lang'][$this->app->vars('_sess.lang')] : $lang = [];
    $item = (array)$lang + (array)$item;
    isset($item['header']) AND isset($item['header'][$_SESSION['lang']]) ? $item['header'] = $item['header'][$_SESSION['lang']] : null;
}

function beforeItemEdit(&$item)
{
    $tables =  wbListTables();
    foreach($tables as $k => $t) {
        if (substr($t, 0, 1) == '_' OR $t == 'pages') {
            unset($tables[$k]);
        }
    }
    $this->app->vars('_var.attaches', $tables);
}

function afterItemRead(&$item) {
    if (!$item) return;
    isset($item['blocks']) ? null : $item['blocks'] = [];
    isset($item['container']) ? null : $item['container'] = '';
    isset($item['id']) ? null : $item['id'] = '';
    if (in_array($item['id'],['_header','_footer'])) return;
    isset($item['name']) ? null : $item['name'] = $item['id'];
    isset($item['path']) ? null : $item['path'] = '';
    if ($item['path'] == '/') $item['path'] = '';
    if (isset($item['blocks'])) $item['template'] = '';
    $item['url'] == '' ? $item['url'] = $item['path'] . '/' .$item['name'] : null;
    $item['url'] == '/home' ? $item['url'] = '/' : null;
}

function beforeitemSave(&$item) {
    @$item['url'] = $item['path'] . '/' .$item['name'];
}

function afterItemSave($item) {
    $this->app->shadow($item['url']);
}

function list() {
    $app = &$this->app;
    $this->tables = $app->tableList();
    $this->jq = new Jsonq();
    $this->count = 0;
    $this->map = [];
    $out = $app->fromFile(__DIR__ . '/list.php');
    $this->tpl = $out->find('#pagesList');
    $this->list = $this->app->itemList('pages',['return'=>'id,name,_form,header,active,attach,attach_filter,url,path']);
    $this->list = $this->list['list'];
    foreach ($this->list as &$item) {
        isset($item['header']) and isset($item['header'][$_SESSION['lang']]) ? $item['header'] = $item['header'][$_SESSION['lang']] : null;
    }
    $res = $this->listNested();
    $app->putContents($app->vars('_env.dba').'/_yonmap.json', json_encode($this->map));
    $out->find('#pagesList')->replaceWith($res);
    echo $out;
}

private function listNested($path = '') {
    $this->count++;
    if ($this->count > 100) {
        return;
    }
    $out = $this->tpl->clone();
    $level = $this->app->json($this->list)->where('path', '=', $path)->get();
    $count = count($level);
    if (!$count) return '';
    $path > '' ? $out->removeAttr('id') : null;
    $out->fetch(['list'=>$level]);
    
    foreach($level as $item) {
        in_array($item['url'],['/','']) ? $url = '/home' : $url = $item['url'];
        unset($this->list[$item['id']]);
            $attach = (isset($item['attach']) AND $item['attach'] > ' ') ? true : false;
            @$res = $attach ? $this->listTable($item, $url) : $res = $this->listNested($url);
            $li = ($url == '/home') ? $out->find('li[data-path="/"]') : $out->find('li[data-path="'.$url.'"]');
            substr($item['id'],0,1) == '_' ? null : $this->map[md5($url)] = ['f'=>$item['_form'],'i'=>$item['id'],'u'=>$url];
            if ($res !== null) $li->append($res);
            $li->find('template')->remove();
            ($url == '/home') ? $li->find('.dd-handle')->addClass('dd-home')->removeClass('dd-handle')->inner(' ') : null;
            if ($attach) {
                $li->find('> ol .dd-add')->remove();
                $li->find('> ol .dd-handle')->remove();
                $li->find('> .dd-info .dd-add')->attr('data-inner', $item['attach']);
                $li->attr('data-inner', $item['attach']);
            }
    }
    $out->parent = $this;
    return $out;
}

private function listTable($item, $path = '') {
        $table = $item['attach'];
        $filter = (isset($item['attach_filter']) && $item['attach_filter'] > '') ? $item['attach_filter'] : [];
        $filter = json_decode($filter,true);

        $options = [
            'return'=>'id,name,_form,header,active',
            'filter'=> $filter
        ];
        $out = $this->tpl->clone();
        $level = $this->app->itemList($table, $options);
        $level = array_chunk($level['list'], 100);
        $level = $level[0];
        foreach ($level as $key => $item) {
            isset($item['name']) ? null : $item['name'] = null;
            isset($item['header']) ? null : $item['header'] = $item['name'];
            $item['_form'] = $table;
            if ($item['header']) {
                $item['path'] = $path;
                $item['name'] = wbFurlGenerate($item['header']);
                $item['url'] = $item['path'].'/'.$item['name'];
                $level[$key] = $item;
                $this->map[md5($item['url'])] = ['f'=>$item['_form'],'i'=>$item['id']];
            } else {
                unset($level[$key]);
            }
        }
    $out->fetch(['list'=>$level]);
    return $out;
}

function path() {
    $app = &$this->app;
    $data = $app->vars('_post.data');
    foreach(array_keys($data) as $id) {
        $item = $app->itemRead('pages', $id);
        if ($item) {
            $item['path'] = $data[$id];
            $app->itemSave('pages', $item, false);
        }
    }
    $app->tableFlush('pages');
}

}
?>