<?php
class modBsform
{
    // TailwindCSS auto form 
    public $app;
    public $dom;
    private $control;
    private $checks;
    private $stop;

    function __construct($dom)
    {
        $this->app = &$dom->app;
        $this->dom = &$dom;
        $this->stop = '--bsform';
        $this->init();
    }

    function init()
    {
        $this->control = 'form-control';
        $this->checks = 'd-flex w-100p';
        $inputs = $this->dom->find("input,textarea,select");
        $this->dom->attr('id') > '' ? null : $this->dom->attr('id',$this->app->newId('_','form'));
        foreach ($inputs as $inp) {
            if (!$inp->hasClass($this->stop) && !$inp->parent('.input-group')->length && !$inp->parent('.form-group')->length) {
                $tag = $inp->tag();
                method_exists($this, $tag) ? $this->$tag($inp) : null;
                $inp->addClass($this->stop);
            }
        }
    }

    function wrap(&$inp) {
        if ($inp->hasClass($this->stop)) return;
        $label = $inp->prev('label');
        if ($label->length && !$label->attr('class')) {
            $label->addClass('col-sm-3');
        }
        $inp->wrap("<div class='my-1 row align-items-center'></div>");
        $label->length ? $label->prependTo($inp->parent('div')) : null;
        $inp->wrap("<div class='col'></div>");
    }

    function input(&$inp, $self = false)
    {
        if ($inp->hasClass($this->stop)) return;
        if ($inp->parent('.input-group')->length) return;
        if ($inp->parent('.form-group')->length) return;
        if (in_array($inp->attr('type'), ['checkbox', 'radio'])) {
            $tagtype = $inp->tag()."[type={$inp->attr('type')}]";
            $inp->addClass('wb-20 ht-20 mr-2');
            $sub = $inp->next($tagtype);
            if (!$self) {
                $this->wrap($inp);
                $inp->wrap("<div class='{$this->checks}'</div>");
            }
            $inp->addClass($this->stop);
            $self == false ? $self = $inp : null;
            if ($sub->length && $self !== false) {
                $this->input($sub, $self);
                $sub->addClass($this->stop);
                $sub->appendTo($self->parent());
            } 
        } else {
            $this->wrap($inp);
            $inp->addClass($this->control);
        }
    }

    function textarea(&$inp)
    {
        $inp->addClass($this->control);
        $this->wrap($inp);
    }
    function select(&$inp)
    {
        $inp->addClass($this->control);
        $this->wrap($inp);
        $inp->wrap("<div class='d-block w-100p'></div>");
    }
}
?>