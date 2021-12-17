<?php
use Nahid\JsonQ\Jsonq;

class pagesClass extends cmsFormsClass {

function beforeItemShow(&$item) {
    isset($item['lang']) ? $lang = $item['lang'][$this->app->vars('_sess.lang')] : $lang = [];
    $item = (array)$lang + (array)$item;
    isset($item['header'])  AND isset($item['header'][$_SESSION['lang']]) ? $item['header'] = $item['header'][$_SESSION['lang']] : null;
}

function afterItemRead(&$item) {
    isset($item['blocks']) ? null : $item['blocks'] = [];
    isset($item['container']) ? null : $item['container'] = '';
    isset($item['id']) ? null : $item['id'] = '';
    if (in_array($item['id'],['_header','_footer'])) return;
    isset($item['name']) ? null : $item['name'] = '';
    isset($item['path']) ? null : $item['path'] = '';
    if ($item['name'] == '') $item['name'] = $item['id'];
    if ($item['path'] == '/') $item['path'] = '';
    if (isset($item['blocks'])) $item['template'] = '';
    $item['url'] = $item['path'] . '/' .$item['name'];
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
    $this->jq = new Jsonq();
    $this->count = 0;
    $out = $app->fromFile(__DIR__ . '/list.php');
    $this->tpl = $out->find('#pagesList');
    $this->list = $this->app->itemList('pages');
    $this->list = $this->list['list'];
    $res = $this->listNested();
    $out->find('#pagesList')->replaceWith($res);
    echo $out->fetch();
}

function listNested($path = '') {
    $this->count++;

    if ($this->count > 100) {
        return;
    }
    $level = $this->app->json($this->list)->where('path','=',$path)->get();
    $count = count($level);
    if (!$count) return '';

    $out = $this->tpl->clone();
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
            
            $li->append($res);

    }
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
