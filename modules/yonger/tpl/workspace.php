<html>
<body wb-if='in_array({{_sess.user.role}},explode(",","{{_sett.modules.yonger.allow}}"))'>
<wb-include wb="{'src':'ws_glob.php'}" wb-if=' "{{_route.subdomain}}" == "" OR "{{_sett.modules.yonger.standalone}}" == "on" ' />
<wb-include wb="{'src':'ws_site.php'}" wb-if=' "{{_route.subdomain}}" > ""  AND "{{_sett.modules.yonger.standalone}}" !== "on" ' />
</body>
</html>