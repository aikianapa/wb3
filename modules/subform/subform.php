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
        $dom->attr('name') > '' ? $dom->params->name = $dom->attr('name') : null;
        $sub->attr('data-params', base64_encode(json_encode($dom->params)));

        if ($form == 'null' and $mode == 'null') {
            $inner= '';
        } else if ($mode == null OR $mode == 'null' OR $mode == '') {
            $inner= '';
        } else if ($form == null) {
            $inner = '<div class="alert alert-warning">Form not defined: please set form in params!</div>';
        } else {
            $subform = $app->getForm($form, $mode);
            $selector ? $subform = $subform->find($selector) : null;
            $subform->fetch($dom->item);
            $inner = $subform->outer();
        }

        if ($dom->attr('name') > '') {
            $sub->find('.mod-subform-inner')->inner($inner);
            //$sub->find('.mod-subform-inner')->inner('<form>'.$sub->find('.mod-subform-inner')->inner().'</form>');
            $sub->append('<textarea style="display:none" type="json" class="mod-subform-data" name="'.$dom->attr('name').'"></textarea>');
        } else {
            $sub->find('.mod-subform-inner')->inner($inner);
        }

        


        $dom->after($sub);
        $dom->remove();


    }
}
?>