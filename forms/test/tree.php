<html>
<wb-var auto="auto" wb-if='"{{_route.subdomain}}" == ""' />
<nav class="nav nav__list d-flex align-items-center" wb-tree="from=_sett.cmsmenu.data&branch=top&parent=false">
    <!--wb-var allow="on" wb-if='"{{data.allow}}" == "" OR {{in_array({{_sess.user.role}},{{explode(",",{{data.allow}})}})*1}}' else="" /-->
    {{in_array({{_sess.user.role}},{{explode(",",{{data.allow}})}})*1}}
    <wb-var allow="on" wb-if='"{{data.allow}}" == "" OR {{in_array({{_sess.user.role}},{{explode(",",{{data.allow}})}})*1}}' else="" />
    <a href="javascript:void(0);" data-ajax="{{data.ajax}}" class="nav-link nobr d-flex align-items-center mg-r-10" wb-if='"{{_lvl}}"=="1" && "{{active}}"=="on" AND "{{_var.allow}}"=="on"' data-ajax="{'url':'/module/yonger/listSites','html':'.content-body'}">
        <div class="nav__icon d-flex align-items-center justify-content-center">
            <svg wb-if="'{{data.icon}}'>''" class="mi mi-{{data.icon}}" wb-module="myicons"></svg>
            </svg>
        </div>
        <span class='d-none d-lg-inline'>{{data.label}}</span>
    </a>
</nav>

</html>