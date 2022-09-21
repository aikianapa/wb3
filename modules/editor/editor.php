<?php
class modEditor {
  function __construct($dom) {
      
      $this->init($dom);
  }
  public function init($dom) {
	if (!isset($dom->app)) return;
        $editor = $dom->app->vars('_sett.modules.editor.editor');
        $editor > '' ? null : $editor = 'jodit';
        $dom->params->module = $editor;
        $dom->app->module($editor, $dom);
  }
}
?>