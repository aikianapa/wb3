<?php
require $_ENV["route"]['path_engine'].'/modules/phpmailer/vendor/phpmailer/phpmailer/src/Exception.php';
require $_ENV["route"]['path_engine'].'/modules/phpmailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require $_ENV["route"]['path_engine'].'/modules/phpmailer/vendor/phpmailer/phpmailer/src/SMTP.php';

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
	if (is_callable("phpmailer__checkout") {
        return phpmailer__checkout();
	} else {
	    return false;
	}
    }
}


}
?>
