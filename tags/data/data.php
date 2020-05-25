<?php
use Adbar\Dot;
class tagData {
  public function __construct(&$dom) {
      return $this->data($dom);
  }

  public function data(&$dom) {
    $data = new Dot();
    if ($dom->is(":root")) $dom->rootError();
    if ($dom->params("table") AND $dom->params("item")) {
        $dom->item = wbItemRead($dom->params->table, $dom->params->item);
    } else if ($dom->params("json")) {
        $dom->item = $dom->params("json");
    } else if (!$dom->params("field")) {
        $dom->item = [];
    }
    if ($dom->params("field")) {
        $data = new Dot();
        $data->setReference($dom->item);
        $dom->item = $data->get($dom->params("field"));
        if (!((array)$dom->item === $dom->item)) $dom->item = [$dom->params("field") => $dom->item];
    }
    $dom->fetch();
    $dom->unwrap("wb-data");

    return $dom;
  }
}
?>
