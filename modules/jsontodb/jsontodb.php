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
        die;
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
        if ( $app->_db->driver == $app->db->driver) {
            $this->error('Нельзя конвертировать одинаковые типы базы');
        }
        $listJ = $app->_db->itemList($form);
        $listD = $app->db->itemList($form);
        if ($listD['count'] > 0) {
            $this->error('Очистите коллекцию перед конвертацией');
        }
        ob_start();
        foreach($listJ['list'] as $item) {
            $app->db->itemSave($form, $item, false);
            echo $item['_id'].'<br>';
            ob_flush();
        }
        $app->db->tableFlush($form);
        ob_end_clean();
        return "The End";
    }

    private function error($msg = null) {
        $msg == null ? $msg = "Error!" : null;
        echo $msg;
        die;
    }

}
?>
<?php


class modJson2db___ {
    public function __construct($app)
    {

    }
}
?>