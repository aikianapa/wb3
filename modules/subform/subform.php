<?php
class modSubform
{
    public function __construct($dom)
    {
        $this->init($dom);
    }
    public function init($dom)
    {
        $app = $dom->app;
        $sub = $app->fromFile(__DIR__ . '/subform_ui.php');
        $form = null;
        $mode = null;
        $selector = null;
        $dom->params('watch') > '' ? $sub->attr('data-watch', $dom->params('watch')) : null;
        $dom->params('form') > '' ? $form = $dom->params('form') : null;
        $dom->params('mode') > '' ? $mode = $dom->params('mode') : null;
        $dom->params('selector') > '' ? $selector = $dom->params('selector') : null;
        if ($form == null AND $mode == null) {
            $sub->inner('');
        } else if ($form == null OR $mode == null) {
            $sub->inner('<div class="alert alert-warning">Form not defined: please set form and mode params!</div>');
        } else {
            $subform = $app->getForm($form, $mode);
            $selector ? $subform = $subform->find($selector) : null;
            $sub->children('.mod-subform')->inner($subform);
        }
        $dom->after($sub);
        $dom->remove();


    }
}
?>