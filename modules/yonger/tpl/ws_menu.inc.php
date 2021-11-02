<wb-var auto="auto" wb-if='"{{_route.subdomain}}" == ""' />
<nav class="nav nav__list d-flex align-items-center" wb-tree="from=_sett.cmsmenu.data&branch=top&parent=false">
    <wb-var allow="on" wb-if='"{{data.allow}}" == "" OR {{in_array({{_sess.user.role}},{{explode(",",{{data.allow}})}})*1}}' else="" />
    <a href="javascript:void(0);" data-ajax="{{data.ajax}}" class="nav-link nobr d-flex align-items-center mg-r-10"
        wb-if='"{{_lvl}}"=="1" && "{{active}}"=="on" AND "{{_var.allow}}"=="on"'
        data-ajax="{'url':'/module/yonger/listSites','html':'.content-body'}">
        <div class="nav__icon d-flex align-items-center justify-content-center">
            <svg wb-if="'{{data.icon}}'>''" class="mi mi-{{data.icon}}" wb-module="myicons"></svg>
            </svg>
        </div>
        <span class='d-none d-lg-inline'>{{data.label}}</span>
    </a>
</nav>

<meta wb-jq='$dom->prev()->find(".nav-link:last-child")->addClass("btn btn-sm btn-dashed");' />


<nav class="nav d-flex align-items-center">
    <a href="#" class="nav-link">
        <svg class="mi mi-search-loap" wb-module="myicons"></svg>
    </a>
    <a href="#" class="nav-link">
        <svg class="mi mi-bell-notification" wb-module="myicons"></svg>
    </a>
    <a href="#" data-toggle="dropdown" class="dropdown-link ml-4">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm mg-r-8">
                <img data-src="/thumb/36x36/src{{_sess.user.avatar[0].img}}" class="img rounded-circle" alt="" />
            </div>
            <div>
            <span class="tx-color-01 tx-semibold d-block lh--9">{{_sess.user.first_name}}</span>
            <span class="tx-gray-300 tx-10 d-block lh--9">{{_sess.user.role}}</span>
            </div>
            <span class="d-none d-md-inline"><svg class="mi mi-arrows-diagrams-08" wb-module="myicons"></svg></span>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-right tx-13">
        <a data-ajax="{'url':'/cms/ajax/form/users/profile','html':'.content-body'}" class="dropdown-item">
        <svg class="mi mi-users-06" wb-module="myicons"></svg> Профиль
        </a>
        <div class="dropdown-divider"></div>
        <a href="/signout" class="dropdown-item">
        <svg class="mi mi-login-enter-arrow" wb-module="myicons"></svg> Выход
        </a>
    </div>
    
</nav>