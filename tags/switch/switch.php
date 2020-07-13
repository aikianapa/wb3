<?php
use Adbar\Dot;

class tagSwitch {
    public function __construct( &$dom ) {
        return $this->switch( $dom );
    }

    public function switch( &$dom ) {
        $app = $dom->app;
        $switch = $app->fromFile( __DIR__ . '/switch_ui.php' );
        $switch->copy( $dom );
        if ( $dom->attr( 'name' ) > '' ) $switch->find( 'input[name]' )->attr( 'name', $dom->attr( 'name' ) );
        $switch->fetch();
        $dom->after( $switch );
        $dom->remove();
    }
}
?>