<?php
class tagMeta
{
  public function __construct(&$dom)
  {
    return $this->meta($dom);
  }

  public function meta(&$dom)
  {
    $app = &$dom->app;
    $modules = $app->vars('_sett.modules');
    $mods = $app->dot($modules);
    foreach ($modules as $mn => $ms) {
      $private = $mods->get("{$mn}._private");
      $mods->get("{$mn}._private", null);
      if ($private > '') {
        if ($private == '_all') {
          $mods->set("{$mn}", null);
        } else {
          $flds = explode(',', $private);
          foreach ($flds as $fld) {
            if ($fld > '') $mods->set("{$mn},{$fld}", null);
          }
        }
      }
    }
    $sett = $app->vars('_sett');
    $sett['modules'] = $mods->get();

    $meta = [
      'sess' => $app->vars('_sess'),
      'sett' => $sett,
      'rout' => $app->vars('_route')
    ];
    $meta = base64_encode(json_encode($meta));
    $dom->after("<meta name='wbmeta' content='{$meta}'/>");
    $dom->remove();
  }
}
