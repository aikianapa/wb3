<?php
use Adbar\Dot;

class tagJq {
    public function __construct( &$dom ) {
        return $this->jq( $dom );
    }

    public function jq( &$that ) {
        if ( $that->is( ':root' ) ) $that->rootError();
        if ( !$that->params ) {
            $that->remove();
            return;
        }
        $app = $that->app;

        $tag = htmlentities( $that->outer() );
        $tag = str_replace( '&amp;gt;', '>', $tag );
        $tag = str_replace( '&gt;&lt;/wb-jq&gt;', '/>', $tag );
        if ( $tag == '' ) return;
        
            $inner = $app->fromString('<html>'.$that->inner().'</html>');
            $inner->copy($that);
            $inner->fetch();
        
        
        if ( $that->params( 'html' ) ) {
            $that->parent()->find( $that->params( 'html' ) )->html($inner->inner());
        } else if ( $that->params( 'append' ) ) {
            $that->parent()->find( $that->params( 'appendto' ) )->appendto($that->inner());
        } else if ( $that->params( 'after' ) ) {
            $that->parent()->find( $that->params( 'after' ) )->after($that->inner());
        } else if ( $that->params( 'before' ) ) {
            $that->parent()->find( $that->params( 'before' ) )->before($that->inner());
        } else {
            $jq = $that->params->wb;
            $jq = explode( ';', $jq );
            $jq = $jq[0].';';
            if ( substr( $jq, 0, 6 ) == '$dom->' ) {
                $dom = $that->parent;
                try {
                    @eval( $jq );
                } catch( Exception $err ) {
                    echo 'Unknown result in the tag: '. $tag;
                }
                $ch = $dom->children();
                foreach ( $ch as $c ) {
                    $c->copy( $dom );
                    $c->fetch();
                }

            } else {
                echo 'Error: wb-jq command was start at $dom->';
                echo '\n<br/>'. $tag;
            }
        }
        $that->remove();
    }
}
?>
