<?php

class usersClass extends cmsFormsClass {
    function beforeItemSave(&$item) {
        isset($item['email']) ? $item['email'] = strtolower($item['email']) : null;
        isset($item['phone']) ? $item['phone'] = preg_replace("/\D/", '', $item['phone']) : null;
    }
}
?>
