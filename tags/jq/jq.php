<?php
use Adbar\Dot;

// <wb-jq wb="$dom->find('.pagination')->addClass('mt-40')" /> //

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
        if ($that->params('render') == 'client') {
          $wb = $that->find("wb-snippet,wb-include");
          foreach($wb as $inc) {
              $inc->copy($that);
              $inc->attr('wb-render','client');
              $inc->fetchParams();
              $inc->fetchNode();
          }
          $inner = &$that;
        } else {
          $inner = $app->fromString('<html>'.$that->inner().'</html>');
          $inner->copy($that);
          $inner->fetch();
        }
        if ( $that->params( 'html' ) ) {
            $that->parents('html')->find( $that->params( 'html' ) )->html($inner->inner());
        } else if ( $that->params( 'append' ) ) {
            $that->parents('html')->find( $that->params( 'append' ) )->append($inner->inner());
        } else if ( $that->params( 'prepend' ) ) {
            $that->parents('html')->find( $that->params( 'prepend' ) )->prepend($inner->inner());
        } else if ( $that->params( 'after' ) ) {
            $that->parents('html')->find( $that->params( 'after' ) )->after($inner->inner());
        } else if ( $that->params( 'before' ) ) {
            $that->parents('html')->find( $that->params( 'before' ) )->before($inner->inner());
        } else {
            $jqs = explode( ';', $that->params->wb );
            $dom = &$that->parent;

            foreach ((array)$jqs as $i => $jq) {
                $jq = ltrim($jq);
                if (substr($jq, 0, 6) == '$dom->') {
                    try {
                        @eval($jq.';');
                    } catch (Exception $err) {
                        echo 'Unknown result in the tag: '. $tag;
                    }
                    $ch = $dom->children();
                    foreach ($ch as $c) {
                        $c->copy($dom);
                        $c->fetch();
                    }
                } else if ($jq > '') {
                    echo 'Error: wb-jq command was start at $dom->';
                    echo '\n<br/>'. $tag;
                }
            }
        }
        $that->remove();
    }
}
?>
