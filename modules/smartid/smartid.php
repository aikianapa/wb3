<?php

class modSmartid {
    public function __construct( &$dom ) {
        return $this->init( $dom );
    }

    public function init( &$dom ) {

        if ( !$dom->is( 'input' ) ) {
            $dom->replace( "<span class='form-control text-danger'>SmartID Error: required &lt;input&gt; tag</span>" );
            return;
        }
        $dom->attr('data-params',json_encode($dom->params));
        if ( $dom->params("furl") ) $dom->attr( 'data-furl', $dom->params->furl );
        $dom->addClass( 'wb-smartid' );
        $dom->attr( 'required', 'true' );
        $dom->removeAttr( 'wb' );
        $dom->after( '<script type="text/wbapp">wbapp.loadScripts(["/engine/modules/smartid/smartid.js"],"smartid-js");</script>' );
        return $dom;
    }
}
?>
