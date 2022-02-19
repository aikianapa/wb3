<?php

class modWbworker
{
    protected $clients;


    public function server()
    {
        @$res = exec('php '. __DIR__ .'/wbworkerd.php status');
        if (strpos($res, 'not run')) {
            @exec('php '. __DIR__ .'/wbworkerd.php start -d');
        } else {
            // ChatServer is running;
        }
    }
}
?>