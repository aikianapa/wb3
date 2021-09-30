<?php

class usersClass extends cmsFormsClass {
    function beforeItemSave(&$item) {
        if (isset($item['phone'])) {
            $item['phone'] = preg_replace("/\D/", '', $item['phone']);
        }
    }
}
?>
