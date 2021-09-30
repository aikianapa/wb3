<?php
class modSwitch {

    public function __construct( &$dom ) {
        return $this->switch( $dom );
    }

    public function switch( &$dom ) {
        $app = $dom->app;
        $switch = $app->fromFile( __DIR__ . '/switch_ui.php' );
        $switch->copy( $dom );
        $name = $dom->attr("name");
        $value = $dom->attr("value");
        if ($name == "" AND $dom->params('name') > "") $name = $dom->params('name');
        if ($value == "" AND $dom->params('value') > "") $value = $dom->params('value');
        if ($dom->params('label')) $switch->find('label span')->inner($dom->params('label'));
        $inp = $switch->find('input');
        $dom->attrsCopy($inp);
        $inp->attr("name",$name);
        $inp->attr("value",$value);
        $switch->fetch();
        $dom->after( $switch );
        $dom->remove();
    }
}

?>