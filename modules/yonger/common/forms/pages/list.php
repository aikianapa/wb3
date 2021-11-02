<html>
<div class="m-3" id="yongerSpace">
        <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">Страницы</h3>
        <div class="ml-auto order-2 float-right">
        <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_header','html':'#yongerSpace modals'}" class="btn btn-secondary">
            <img src="/module/myicons/24/FFFFFF/menubar-arrow-up.svg" width="24" height="24" /> Шапка
        </a>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_footer','html':'#yongerSpace modals'}" class="btn btn-secondary">
            <img src="/module/myicons/24/FFFFFF/menubar-arrow-down.svg" width="24" height="24" /> Подвал
        </a>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_new','html':'#yongerSpace modals'}" class="btn btn-primary">
            <img src="/module/myicons/24/FFFFFF/item-select-plus-add.svg" width="24" height="24" /> Добавить страницу
        </a>
        </div>
    </nav>
    
    <div id="yongerPagesTree" class="dd yonger-nested">
        <span class="bg-light">
            <div class="header p-2">
                <span clsss="row">
                <div class="col-3">
                <input type="search" class="form-control" placeholder="Поиск страницы"
                data-ajax="{'target':'#{{_form}}List','filter_add':{'$or':[{ 'header': {'$like' : '$value'} }, { 'url': {'$like' : '$value'} }  ]} }">
                </div>
                </span>
            </div>
            
        </span>
        <ol id="pagesList" class="dd-list">
                        <wb-foreach wb="{'ajax':'/api/query/{{_form}}/',
                            'render':'server',
                            'bind':'cms.list.{{_form}}',
                            'sort':'date:d',
                            'size':'{{_sett.page_size}}',
                            'from':'list',
                            'filter': {'_site':'{{_sett.site}}'}
                }">
                <li class="dd-item row" data-item="{{id}}" data-name="{{name}}">
                    <span class="dd-handle"><img src="/module/myicons/20/000000/dots-2.svg"  width="20" height="20"/></span>
                    <span class="dd-text d-none d-sm-flex col-sm-6 ellipsis">
                    <span>{{header}}</span>
                    </span>
                    <span class="dd-info col-sm-6">
                        <span class="row">
                            <span class="dd-path col-6 ellipsis" data-path="{{url}}">
                                <img data-src="/module/myicons/link-big.svg?size=24&stroke=000000">
                                {{url}}
                                <span class="d-block d-sm-none tx-10 tx-muted">{{header}}</span>
                            </span>
                            <form method="post" class="col-6 text-right m-0">
                                <wb-var wb-if='"{{active}}" == ""' stroke="FC5A5A" else="82C43C" />
                                <input type="checkbox" name="active" class="d-none">
                                <img src="/module/myicons/24/{{_var.stroke}}/power-turn-on-square.1.svg" class="dd-active cursor-pointer">
                                <img src="/module/myicons/24/323232/copy-paste-select-add-plus.svg" width="24" height="24" class="dd-copy">
                                <img src="/module/myicons/24/323232/content-edit-pen.svg" width="24" height="24" class="dd-edit">
                                <img src="/module/myicons/24/323232/trash-delete-bin.2.svg" width="24" height="24" class="dd-remove">
                            </form>
                        </span>
                    </span>
                </li>
            </wb-foreach>
        </ol>
    </div>
    <modals></modals>
    <script wb-app>
    wbapp.loadStyles(['/engine/lib/js/nestable/nestable.css']);
    wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], '', function() {

        var datapath = [] ; // для передачи списка при смене путей

        $('#yongerPagesTree').delegate('li','mouseover',function(e) {
                $('#yongerPagesTree li').removeClass('hover');
                e.stopPropagation();
                $(this).addClass('hover');
        });

        $('#yongerPagesTree').delegate('li','mouseout',function(e) {
            e.stopPropagation();
            $(this).removeClass('hover');
        });
            
        $('#yongerPagesTree').delegate('.dd-active','mouseout',function(e) {
            e.stopPropagation();
            $(this).removeClass('hover');
        });

        $('#yongerPagesTree').delegate('.dd-remove',wbapp.evClick,function(e) {
            let item = $(this).parents('[data-item]').attr('data-item')
            wbapp.ajax({'url':'/ajax/rmitem/{{_form}}/'+item,'update':'cms.list.pages','html':'#yongerSpace modals'});
            e.stopPropagation();
        });

        $('#yongerPagesTree').delegate('.dd-path',wbapp.evClick,function(e){
            e.stopPropagation();
            let url = document.location.origin + $(this).attr('data-path');
            let target = md5(url);
            window.open(url, target).focus();
            e.stopPropagation();
        });

        $('#yongerPagesTree').delegate('.dd-active',wbapp.evClick,function(e){
            e.stopPropagation();
            let id = $(e.currentTarget).parents('[data-item]').attr('data-item');
            $(e.currentTarget).parent('form').find('[name=active]').trigger('click');
            wbapp.save($(e.currentTarget),{'table':'pages','id':id,'update':'cms.list.pages','silent':'true'})
        });

        $('#yongerPagesTree').delegate('li[data-item] .dd-edit',wbapp.evClick,function(e){
            e.stopPropagation();
            let item = $(this).parents('[data-item]').attr('data-item')
            wbapp.ajax({'url':'/cms/ajax/form/pages/edit/'+item,'html':'#yongerSpace modals'});
        });

        $('#yongerPagesTree').delegate('li[data-item] .dd-copy',wbapp.evClick,function(e){
            e.stopPropagation();
            let item = $(this).parents('[data-item]').attr('data-item')
            wbapp.ajax({'url':'/module/yonger/copypage/','item':item,'update':'cms.list.pages'});
        });

        $(document).on('bind-cms.list.pages',function(){
            $('#yongerPagesTree').nestable({
                maxDepth: 3,
                beforeDragStop: function(l,e, p){
                    datapath = {};
                    changePath(e,p).then(function(){
                        wbapp.post('/cms/ajax/form/pages/path',{'data':datapath});
                    });
                }
            });
        });

        var changePath = async function (e,p) {
            let self = $(e).attr('data-item');
            let name = $(e).attr('data-name');
            let parent = $(p).closest('.dd-item').find('.dd-path').attr('data-path');
            if (parent == undefined) {parent = '';} 
            if (parent == '/') {parent = '/home'}
            let path = parent + '/' + name;
            datapath[self] = parent;
            $(e).children('.dd-info').find('.dd-path')
                .text(path)
                .attr('data-path',path);
                $(e).find('ol.dd-list .dd-item').each(await function(){
                        changePath(this,e);
                });
        }
        $(document).trigger('bind-cms.list.pages');
    })
    </script>

</html>