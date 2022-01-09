<?php

// Author: oleg_frolov@mail.ru

class modYonPageSelect
{
    public function __construct(&$obj)
    {
        if (wbIsDom($obj)) {
            $this->dom = &$obj;
            $this->app = &$obj->app;
            $this->getui();
        } else {
            header('Content-Type: application/json; charset=utf-8');
            $this->app = $obj;
            echo $this->app->jsonEncode($this->list());
        }
    }
    
    private function getui() {
        $ui = file_get_contents(__DIR__.'/yonpageselect_ui.php');
        $this->dom->addClass('yonpageselect');
        $this->dom->after($ui);
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
