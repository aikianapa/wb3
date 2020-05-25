<?php
use Adbar\Dot;
class tagIf {
  public function __construct(&$dom) {
      return $this->if($dom);
  }

  public function if(&$dom) {
      if ($dom->is(":root")) $dom->rootError();
      $res = false;
      $app = &$dom->app;
      $item = $dom->item;
      $tpl = $app->fromString($dom->inner());
      if ($dom->params("orm") > "") {
          $orm = $dom->params->orm."->count()";
          $res = $app->db->itemList([$item], $options = ["orm"=>$orm]);
      } else if ($dom->params("wb") > "") {
          $res = wbEval( $dom->params("wb") );
      }
      $else = $dom->find("wb-else");
      if ($res) {
          $else->remove();
      } else {
          $else->fetch();
          $dom->inner($else->inner());
      }
      $dom->unwrap("wb-if");
      return $dom;
  }
}
?>
