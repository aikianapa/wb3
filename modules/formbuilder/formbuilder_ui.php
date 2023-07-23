<html>
<div class="chat-wrapper chat-wrapper-two" id="modFormBuilder">
    <div class="chat-sidebar">

        <div class="chat-sidebar-header d-flex align-items-center p-3">
            <a href="javascript:void(0);" class="mr-2"
                data-toggle="tooltip" data-trigger="hover" title="Настройки"
                data-ajax="{'url':'/module/formbuilder/_settings/','html':'.chat-content-body'}">
                    <svg class="mi-settings-switches-square-2 size-26" wb-module="myicons"></svg>
                </a>

            <a href="javascript:void(0);" data-ajax="{
                    'url':'/module/formbuilder/',
                    'html':'.content-body'
                    }" data-toggle="tooltip" data-trigger="hover" title="Дизайнер">
                    <svg class="mi-design-47 size-26" wb-module="myicons"></svg>
            </a>

        </div><!-- chat-sidebar-header -->

        <!-- start sidebar body -->
        <div class="chat-sidebar-body">


            <!-- required bootstrap js and fontawesome -->
            <!-- add 'accordion' snippet in css -->


            <div id="modFormbuilderFormType" class="accordion w-100">
                <div class="card mb-0">
                    <wb-foreach wb-from="modset.prop.data">
                        <div class="card-header collapsed" data-toggle="collapse" href="#collapse-{{id}}">
                            <b class="cursor-pointer card-title">
                                <svg class="mi-settings.2 size-20" wb-module="myicons"></svg>
                                {{name}}
                            </b>
                        </div>
                        <div id="collapse-{{id}}" class="card-body collapse p-2" data-parent="#modFormbuilderFormType">
                            <ul class="list-group {{_parent.id}}">
                                <wb-foreach wb-from="children">
                                    <li class="list-group-item" data-toggle="popover"
                                        data-url="/module/formbuilder/snipview/{{_parent.id}}/{{id}}">
                                        {{name}}
                                    </li>
                                </wb-foreach>
                            </ul>
                        </div>
                    </wb-foreach>
                </div>
            </div>
        </div>
    </div><!-- chat-sidebar -->

    <div class="chat-content">
        <div class="chat-content-body">
            <div class="focus-menu position-absolute">
                <i class="fa fa-level-up" data="uplevel" title="Level Up" data-toggle="tooltip"
                    data-position="bottom"></i>
                <i class="fa fa-arrow-circle-up" data="up"></i>
                <i class="fa fa-arrow-circle-down" data="down"></i>
                <i class="fa fa-gear" data="sett"></i>
                <i class="fa fa-edit" data="edit"></i>
                <i class="fa">|</i>
                <i class="fa fa-trash" data="trash"></i>
            </div>
            <div class="focus-box position-absolute"></div>
            <div class="hover-box position-absolute"></div>
            <iframe id="modFormbuilderView" width="100%" frameborder="0" src="/module/formbuilder/loadstate/">

            </iframe>
        </div><!-- chat-content-body -->

        <div class="modal right" data-backdrop="static" role="dialog" id="modFormbuilderEditor">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <button type="button" class="btn btn-primary btn-sm"><i class="fa fa-save"></i>
                            Сохранить</button>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <wb-module wb="module=codemirror"></wb-module>
                    </div>
                </div>
            </div>
        </div>

        <div class="off-canvas off-canvas-right" id="modFormbuilderPanel">
            <a href="#" class="close" onclick="$(this).parent().toggleClass('show');"><i class="fa fa-close"></i></a>
            <div class="pd-25 ht-100p tx-13">
                <h6 class="tx-inverse mg-t-50 mg-b-25">Здесь будет редактор свойств</h6>
                <p class="mg-b-25">Royalty free means you just need to pay for rights to use the item once per end
                    product. You don't need to pay additional or ongoing fees for each person who sees or uses it.</p>
                <a href="" class="btn btn-primary btn-block">Learn More</a>
            </div>
        </div>
    </div><!-- chat-content -->
</div><!-- chat-wrapper -->
<script type="text/wbapp">
    wbapp.loadStyles(["/engine/modules/formbuilder/css/formbuilder.less?{{_env.new_id}}"]);
    wbapp.loadScripts([
    "/engine/modules/formbuilder/formbuilder.js?{{_env.new_id}}"
    ],"formbuilder-js");
</script>


</html>