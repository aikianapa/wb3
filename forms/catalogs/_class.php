<?php
class catalogsClass extends cmsFormsClass {

    function sort()
    {
        $data = $this->app->vars('_post');
        $res = ['error'=>true];
        foreach ($data as $sort => $item) {
            $this->app->itemSave('catalogs', [
                    'id'=>$item,
                    '_sort' => wbSortIndex($sort)
                ], false);
            $res = ['error'=>false];
        }
        $this->app->tableFlush('catalogs');
        header("Content-type:application/json");
        echo json_encode($res);
        exit;
    }

}
?>
