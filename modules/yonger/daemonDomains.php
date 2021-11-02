<?php
$hosts = __DIR__ .'/../../sites/hosts/';
while ($hosts) {
$domain = trim(file_get_contents($hosts.'/.domainname'));
$list = scandir($hosts);
foreach($list as $name) {
    if (is_file($hosts.'/'.$name) && $name !== '.domainname') {
        $subdomain = $name.'.'.$domain;
        exec("echo '127.0.0.1   {$subdomain}' >> /etc/hosts");
        unlink($hosts.'/'.$name);
        echo $subdomain.PHP_EOL;
    }
}
sleep(1);
}
?>