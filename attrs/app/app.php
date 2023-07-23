<?php
class attrApp
{
    public function __construct($dom)
    {
        $dom->is('script') ? $dom->attr('type','text/wbapp') : null;
    }
}