<?php
class yongerPage {

    public function __construct($dom = null)
    {
        $this->app = &$_ENV['app'];
        $this->dom = &$dom;
    }

    function list() {
        $list = [];
        $ready = [];
        $files = $this->app->listFiles($this->app->route->path_app.'/blocks');
        foreach($files as $file) {
            $name = basename($file,'.php');
            if ($name !== 'common.inc') {
                $form = $this->app->fromFile($this->app->route->path_app.'/blocks/'.$file);
                $form = $form->find('edit');
                $form->find('*:not(wb-lang)')->remove();
                $form->fetch();
                $header = $form->attr('header');
                $id = $this->app->newId();
                $list[$id] = ['id'=>$id,'header'=>$header,'name'=>$name,'file'=>$file,'path'=>'/_app_/blocks/'.$file];
                $ready[] = $name;
            }
        };

        $files = $this->app->listFiles(__DIR__.'/common/blocks');
        foreach($files as $file) {
            $name = basename($file,'.php');
            if ($name !== 'common.inc' && !in_array($name,$ready)) {
                $form = $this->app->fromFile(__DIR__.'/common/blocks/'.$file);
                $form = $form->find('edit');
                $form->find('*:not(wb-lang)')->remove();
                $form->fetch();
                $header = $form->attr('header');
                $id = $this->app->newId();
                $list[$id] = ['id'=>$id,'header'=>$header,'name'=>$name,'file'=>$file,'path'=>'/_yonger_/common/blocks/'.$file];
                $ready[] = $name;
            }
        }
        $list = $this->app->arraySort($list,'name');
        
        if ($this->dom == null) return $list;
        $res = $this->app->fromFile(__DIR__.'/common/forms/pages/struct.php');
        $res->fetch(['blocks'=>$this->dom->item['blocks']]);
        return $res->outer();
    }

    function blockfind($name) {
        $ta = $this->app->route->path_app."/blocks/{$name}.php";
        $ty = __DIR__."/common/blocks/{$name}.php";
        is_file($ta) && !isset($file) ? $file = $ta : null;
        is_file($ty) && !isset($file) ? $file = $ty : null;
        if (!isset($file)) $file = null;
        return $file;
    }

    function blockform($item = []) {
        if (is_string($item)) $item=['form'=>$item];
        if (!isset($item['form'])) return;
        $file = $item['form'];
        strpos(' '.$file,'_yonger_') ? $file = str_replace('/_yonger_',__DIR__,$file) : null;
        strpos(' '.$file, '_app_') ? $file = str_replace('/_app_',$this->app->route->path_app, $file) : null;
        is_file($file) ? null : $file = __DIR__ .'/common/blocks/'.$file;
        $out = $this->app->fromString('<html>'.file_get_contents($file).'</html>');

        $out->find('view')->remove();
        $out->path = dirname($file);

        $out->fetch($item);
        if ($out->find('edit')->length > 1) {
            $edit = $out->find('edit');
            $i=1;
            foreach($edit as $ed) {
                if ($i>1) {
                    $ed->after($ed->inner());
                    $ed->remove();
                }
                $i++;
            }
        }
        return $out->find('edit')->outer();
    }

    function blockview($file = null) {
        if ($file == null) return;
        $src = $file;
        strpos(' '.$file,'_yonger_') ? $file = str_replace('/_yonger_',__DIR__,$file) : null;
        strpos(' '.$file, '_app_') ? $file = str_replace('/_app_',$this->app->route->path_app, $file) : null;
        !is_file($file) ? $file = __DIR__ . '/common/blocks/'.$file : null; // если не прописане полный путь
        if (!is_file($file)) {
            $out = $this->app->fromString("<span><!-- Block not found: {$src} --></span>");
        } else {
            $out = $this->app->fromFile($file);
            $out = $out->find('view');
        }
        return $out;
    }

    function blockedit($file = null) {
        if ($file == null) return;
        $src = $file;
        strpos(' '.$file,'_yonger_') ? $file = str_replace('/_yonger_',__DIR__,$file) : null;
        strpos(' '.$file, '_app_') ? $file = str_replace('/_app_',$this->app->route->path_app, $file) : null;
        !is_file($file) ? $file = __DIR__ . '/common/blocks/'.$file : null; // если не прописане полный путь
        if (!is_file($file)) {
            $out = $this->app->fromString("<span><!-- Block not found: {$src} --></span>");
        } else {
            $out = $this->app->fromFile($file);
            $out = $out->find('edit');
        }
        return $out;
    }


}
?>