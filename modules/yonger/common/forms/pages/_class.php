<?php
use Nahid\JsonQ\Jsonq;

class pagesClass extends cmsFormsClass {

function beforeItemShow(&$item) {
    isset($item['lang']) ? $lang = $item['lang'][$this->app->vars('_sess.lang')] : $lang = [];
    $item = (array)$lang + (array)$item;
    isset($item['header']) AND isset($item['header'][$_SESSION['lang']]) ? $item['header'] = $item['header'][$_SESSION['lang']] : null;
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
    $out = $app->fromFile(__DIR__ . '/list.php');
    $this->tpl = $out->find('#pagesList');
    $this->list = $this->app->itemList('pages',['return'=>'id,name,_form,header,active,url,path']);
    $this->list = $this->list['list'];
    foreach ($this->list as &$item) {
        isset($item['header']) and isset($item['header'][$_SESSION['lang']]) ? $item['header'] = $item['header'][$_SESSION['lang']] : null;
    }
    //$app->vars('_post',[]); // фикса для правильной отработки обновлений
    $res = $this->listNested();
    $firstlvl = $res->find('> li.dd-item');
    foreach($firstlvl as $li) {
        $name= $li->attr('data-name');
        in_array($name, $this->tables) ? $li->attr('data-inner', $name) : null;
    }
    $out->find('#pagesList')->replaceWith($res);
    echo $out;
}

function listNested($path = '') {
    $this->count++;

    if ($this->count > 100) {
        return;
    }
    $out = $this->tpl->clone();
    $table = substr($path, 1);
    $page = true;
    if (in_array($table, $this->tables)) {
        $page = false;
        $level = $this->app->itemList($table,['return'=>'id,name,_form,header,active']);
        $level = array_chunk($level['list'],100);
        $level = $level[0];
        foreach($level as $key => $item) {
            isset($item['name']) ? null : $item['name'] = null;
            isset($item['header']) ? null : $item['header'] = $item['name'];
            $item['_form'] = $table;
            if ($item['header']) {
                $item['path'] = $path;
                $item['name'] = wbFurlGenerate($item['header']);
                $item['url'] = $item['path'].'/'.$item['id'].'/'.$item['name'];
                $level[$key] = $item;
            } else {
                unset($level[$key]);
            }
        }
    } else {
        $level = $this->app->json($this->list)->where('path', '=', $path)->get();
    }
    $count = count($level);
    if (!$count) return '';
    $path > '' ? $out->removeAttr('id') : null;
    $out->fetch(['list'=>$level]);
    
    foreach($level as $item) {
        in_array($item['url'],['/','']) ? $url = '/home' : $url = $item['url'];
        unset($this->list[$item['id']]);
            $res = $this->listNested($url);
            if ($url == '/home') {
                $li = $out->find('li[data-path="/"]');
            } else {
                $li = $out->find('li[data-path="'.$url.'"]');
            }
            if ($res !== null) $li->append($res);
            if ($item['_form']!=='pages') $li->find('.dd-add')->remove();
            $li->find('template')->remove();
    }
    $out->parent = $this;
    return $out;
}

function path() {
    $app = &$this->app;
    $data = $app->vars('_post.data');
    foreach(array_keys($data) as $id) {
        $item = $app->itemRead('pages', $id);
        $item['path'] = $data[$id];
        $app->itemSave('pages',$item,false);
    }
    $app->tableFlush('pages');
}

}
?>