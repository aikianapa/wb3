<?php
class tagMeta {
  public function __construct(&$dom) {
      return $this->meta($dom);
  }

  public function meta(&$dom) {
        $meta = [
            'sess' => $dom->app->vars('_sess'),
            'sett' => $dom->app->vars('_sett'),
            'rout' => $dom->app->vars('_route')
        ];
        $meta = base64_encode(json_encode($meta));
        $dom->after("<meta name='wbmeta' content='{$meta}'/>");
        $dom->remove();
  }

}
