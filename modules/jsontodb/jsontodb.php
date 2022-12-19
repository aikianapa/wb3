<?php
class modJsontodb
{
    public function __construct($app)
    {
        $this->app = $app;
        if (substr($app->route->mode,-5) == '.json') {
            $res = $this->todb($app);
        } else {
            $res = $this->init($app);
        }
        echo $res;
        exit;
    }
    public function init()
    {
        $app = &$this->app;
        $ui = $app->fromFile(__DIR__.'/jsontodb_ui.php');
        $files = $app->ListFiles($app->vars('_env.dba'));
        foreach ($files as $i => $file) {
            if (substr($file,0,1) == '_') unset($files[$i]);
            if (substr($file,-5) !== '.json') unset($files[$i]);
        }
        $ui->fetch(['files'=>$files]);
        return $ui->outer();
    }

    private function todb() {
        $app = &$this->app;
        $files = $app->ListFiles($app->vars('_env.dba'));
        if (!in_array($app->route->mode,$files)) $this->error('Нет такого файла');
        $form = substr($app->route->mode,0,-5);
        $drv = $app->SetDb($form);
        if ( $app->_db->driver == $drv) {
            $this->error('Нельзя конвертировать одинаковые типы базы');
        }
        $listJ = $app->_db->itemList($form);
        $listD = $drv->itemList($form);
        if ($listD['count'] > 0) {
            $this->error('Очистите коллекцию перед конвертацией');
        }
        ob_start();
        foreach($listJ['list'] as $item) {
            $drv->itemSave($form, $item, false);
            echo $item['_id'].'<br>';
            ob_flush();
        }
        $drv->tableFlush($form);
        ob_end_clean();
        return "The End";
    }

    private function error($msg = null) {
        $msg == null ? $msg = "Error!" : null;
        echo $msg;
        exit;
    }

}
?>
