<div class="divider-text" wb-if='"{{_route.subdomain}}" > ""  AND "{{_sett.modules.yonger.standalone}}" !== "on"'>{{_route.subdomain}}</div>
<hr wb-if='"{{_route.subdomain}}" == "" AND "{{_sett.modules.yonger.standalone}}" !== "on"'>
<ul class="nav nav-aside" wb-tree="from=_sett.cmsmenu.data&branch=aside&parent=false">
    <wb-var allow="on" wb-if='"{{data.allow}}" == "" OR {{in_array({{_sess.user.role}},{{explode(",",{{data.allow}})}})*1}}' else="" />
    <li wb-if='"{{_lvl}}"=="1" && "{{active}}"=="on" AND "{{_var.allow}}"=="on"'>
        <div class="mg-y-20">{{data.label}}</div>
    </li>
    <wb-var sub="with-sub" wb-if="'{{_lvl}}'=='2' AND '{{count({{children}})}}' > '0'" else=""/>
    <li wb-if='"{{_lvl}}" > "1" && "{{active}}"=="on" AND "{{_var.allow}}"=="on"'
        class="nav-item {{_var.sub}}">
        <a href="#" data-ajax="{{data.ajax}}" class="nav-link">
            <svg wb-if="'{{data.icon}}'>''" class="mi mi-{{data.icon}}" wb-module="myicons"></svg>
            <span>{{data.label}}</span>
        </a>
    </li>
</ul>

<div wb-if='( "{{_route.subdomain}}" == "" AND "{{_sett.modules.yonger.standalone}}" !== "on" )'>
    <ul class="nav nav-aside">
        <li>
            <div class="mg-y-20">Система</div>
        </li>

        <li class="nav-item">
            <a href="#" data-ajax="{'url':'/module/yonger/_settings/','html':'.content-body'}" class="nav-link">
                <svg class="mi mi-setting-edit-filter-gear.1" wb-module="myicons"></svg>
                <span>Настройки</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" data-ajax="{'url':'/module/yonger/support','html':'.content-body'}" class="nav-link">
                <svg class="mi mi-protection-06" wb-module="myicons"></svg>
                <span>Поддержка</span>
            </a>
        </li>
    </ul>


    <div class="card bg-primary text-white text-center p-3">
        <blockquote class="blockquote mb-0">
            <p>Хочешь больше возможностей?</p>
            <div class="blockquote-footer text-white">
            Используй Yonger Pro
            </div>
        </blockquote>
        <a href="#" class="btn btn-secondary mt-3">ПОДКЛЮЧИТЬ</a>
    </div>
</div>