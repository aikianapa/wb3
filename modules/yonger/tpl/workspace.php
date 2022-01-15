<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
<meta wb-if='!wbCheckAllow("{{_sett.modules.yonger.allow}}")' http-equiv="refresh" content="0;URL=/signin" />
</head>
<body wb-if='wbCheckAllow("{{_sett.modules.yonger.allow}}")'>
<wb-include wb="{'src':'ws_glob.php'}" wb-if=' "{{_route.subdomain}}" == "" OR "{{_sett.modules.yonger.standalone}}" == "on" ' />
<wb-include wb="{'src':'ws_site.php'}" wb-if=' "{{_route.subdomain}}" > ""  AND "{{_sett.modules.yonger.standalone}}" !== "on" ' />
</body>
</html>