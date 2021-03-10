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
        $dom->params('form') > '' ? $form = $dom->params('form') : null;
        $dom->params('mode') > '' ? $mode = $dom->params('mode') : null;
        $dom->params('selector') > '' ? $selector = $dom->params('selector') : null;
        $sub->attr('data-params', base64_encode(json_encode($dom->params)));
        if ($form == 'null' AND $mode == 'null') {
            $sub->find('.mod-subform-inner')->html('');
        } else if ($form == null OR $mode == null) {
            $sub->find('.mod-subform-inner')->inner('<div class="alert alert-warning">Form not defined: please set form and mode params!</div>');
        } else {
            $subform = $app->getForm($form, $mode);
            $selector ? $subform = $subform->find($selector) : null;
            $sub->find('.mod-subform-inner')->inner($subform);
        }
        $dom->after($sub);
        $dom->remove();


    }
}
?>