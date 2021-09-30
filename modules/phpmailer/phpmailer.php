<?php
class modPhpmailer
{

  public function __construct($dom)
  {
      $this->init($dom);
  }

function init()
{
    if (isset($_ENV["route"]["params"][0])) {
        $mode=$_ENV["route"]["params"][0];
        $call="phpmailer__{$mode}";
        if (is_callable($call)) {
            echo @$call();
        }
        die;
    } else {
        return phpmailer__checkout();
    }
}


}
?>
