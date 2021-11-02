<div class="aside-loggedin d-flex flex-wrap align-items-center justify-content-between" wb-if='"{{_sett.modules.yonger.standalone}}" !== "on"'>
    <span>сайты</span>
    <button class="mg-t-10 mg-l-auto btn btn-light text-dark bd-0 w-100"
        data-ajax="{'url':'/module/yonger/createSite','html':'.content-body'}">
        <svg class="mi mi-brush-edit-create-sqaure" wb-module="myicons"></svg>
        <span>создать сайт</span>
    </button>
</div>
<wb-include wb="{'src':'aside_menu.inc.php'}" />