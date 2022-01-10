<?php

// Author: oleg_frolov@mail.ru

class modYonPageSelect
{
    public function __construct(&$obj)
    {
        if (wbIsDom($obj)) {
            if (isset($obj->done)) return;
            $obj->done = true;
            $this->dom = &$obj;
            $this->app = &$obj->app;
            $this->getui();
        } else {
            header('Content-Type: application/json; charset=utf-8');
            $this->app = &$obj;
            echo $this->app->jsonEncode($this->list());
        }
    }
    
    private function getui() {
        $ui = $this->app->fromFile(__DIR__.'/yonpageselect_ui.php');
        $class = $this->app->attrToArray($this->dom->attr('class'));
        foreach($class as $i => $c) {
            if ($c == 'col' || substr($c,0,4) == 'col-') {
                $ui->find('.input-group')->addClass($c);
                unset($class[$i]);
            }
        }
        $this->dom->attr('class',implode(' ',$class));
        $this->dom->params('url') > '' ? $this->dom->attr('data-url', $this->dom->params('url')) : null;
        $ui->find('.input-group > input')->remove();
        $ui->find('.input-group')->append($this->dom->outer());
        $this->dom->after($ui);
        $this->dom->remove();
    }

    public function list() {
        if ($this->app->vars('_env.cache.yonpageselect')) {
            $list = $this->app->vars('_env.cache.yonpageselect');
        } else {
            $list = $this->app->itemList('pages', [
                'sort' => 'url',
                'return' => 'url,header',
                'filter'=> ['active'=>'on','_site' => ['$in'=> [null,$this->app->vars('_sett.site')]], 'id'=> ['$nin'=>['_header','_footer']]]
            ]);
            $list = array_values($list['list']);
            array_walk($list, function(&$item,$key){
                $item['header'] = $item['header'][$this->app->vars('_sess.lang')];
            });
            $this->app->vars('_env.cache.yonpageselect', $list);
        }
        return $list;
    }
}
