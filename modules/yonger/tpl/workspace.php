<html>
<wb-include wb="{'src':'ws_glob.php'}" wb-if=' "{{_route.subdomain}}" == "" OR "{{_sett.modules.yonger.standalone}}" == "on" ' />
<wb-include wb="{'src':'ws_site.php'}" wb-if=' "{{_route.subdomain}}" > ""  AND "{{_sett.modules.yonger.standalone}}" !== "on" ' />
</html>