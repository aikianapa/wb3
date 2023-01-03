<?php

    /*
    В этом файле можно добалять фукции проекта, которые будут доступны во всех php файлах проекта и шаблонах
    */


    @include_once(__DIR__ . '/engine/modules/yonger/common/scripts/functions.php');

    function beforeShow($out)
    {
        $out = yongerLinks($out);
        return $out;
    }
    
?>