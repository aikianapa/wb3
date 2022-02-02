<html>
<div class="m-3" id="yongerSpace">
        <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">Страницы</h3>
        <div class="ml-auto order-2 float-right" wb-disallow="content">
        <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_header','html':'modals'}" class="btn btn-secondary">
            <img src="/module/myicons/24/FFFFFF/menubar-arrow-up.svg" width="24" height="24" /> Шапка
        </a>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_footer','html':'modals'}" class="btn btn-secondary">
            <img src="/module/myicons/24/FFFFFF/menubar-arrow-down.svg" width="24" height="24" /> Подвал
        </a>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_new','html':'modals'}" class="btn btn-primary">
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
                        <wb-foreach wb="{'from':'list',
                            'render':'server',
                            'sort':'url',
                            'bind': 'cms.list.pages',
                            'filter': {'_site' : {'$in': [null,'{{_sett.site}}']}, 'id': {'$nin':['_header','_footer']}}
                }">
                <li class="dd-item row" data-item="{{id}}" data-name="{{name}}" data-path="{{url}}" data-form="{{_form}}" data-inner="pages">
                    <span class="dd-handle"></span>
                    <span class="dd-text d-none d-sm-flex col-sm-9 ellipsis">
                        <span>{{header}}
                            <br>
                                <span class="dd-path ellipsis" data-path="{{url}}">
                                    {{url}}
                                </span>
                        </span>
                    </span>
                    <span class="dd-info col-sm-3">
                            <form method="post" class="text-right m-0">
                                <wb-var wb-if='"{{active}}" == ""' stroke="FC5A5A" else="82C43C" />
                                <input type="checkbox" name="active" class="d-none">
                                <img src="/module/myicons/24/{{_var.stroke}}/power-turn-on-square.1.svg" class="dd-active cursor-pointer" wb-allow="admin">
                                <img src="/module/myicons/24/323232/item-select-plus-add.svg" class="dd-add cursor-pointer" wb-allow="admin">
                                <img src="/module/myicons/24/323232/copy-paste-select-add-plus.svg" width="24" height="24" class="dd-copy" wb-allow="admin">
                                <img src="/module/myicons/24/323232/content-edit-pen.svg" width="24" height="24" class="dd-edit">
                                <img src="/module/myicons/24/323232/trash-delete-bin.2.svg" width="24" height="24" class="dd-remove" wb-allow="admin">
                            </form>
                    </span>
                </li>
            </wb-foreach>
        </ol>
    </div>

    <script wb-app>
    wbapp.loadStyles(['/engine/lib/js/nestable/nestable.css']);
    wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], '', function() {

        if (yonger.listPages !== undefined) return;

        yonger.listPages = function() {

        var datapath = [] ; // для передачи списка при смене путей

        $(document).delegate('#yongerPagesTree li','mouseover',function(e) {
                $('#yongerPagesTree li').removeClass('hover');
                e.stopPropagation();
                $(this).addClass('hover');
        });

        $(document).delegate('#yongerPagesTree li','mouseout',function(e) {
            e.stopPropagation();
            $(this).removeClass('hover');
        });
            
        $(document).delegate('#yongerPagesTree .dd-active','mouseout',function(e) {
            e.stopPropagation();
            $(this).removeClass('hover');
        });

        $(document).delegate('#yongerPagesTree .dd-remove',wbapp.evClick,function(e) {
            let data = $(this).parents('[data-item]').data();
            wbapp.confirm('Удаление',`Удалить запись ${data.item} из таблицы ${data.form} ?`,{'bgcolor':'danger'})
            .on('confirm',function(){
                wbapp.ajax({'url':'/ajax/rmitem/'+data.form+'/'+data.item+'?_confirm','update':'cms.list.pages','html':'modals'});
            });
            e.stopPropagation();
        });

        $(document).delegate('#yongerPagesTree .dd-path',wbapp.evClick,function(e){
            e.stopPropagation();
            let url = document.location.origin + $(this).attr('data-path');
            let target = md5(url);
            window.open(url, target).focus();
            e.stopPropagation();
        });

        $(document).delegate('#yongerPagesTree .dd-active',wbapp.evClick,function(e){
            e.stopPropagation();
            let id = $(e.currentTarget).parents('[data-item]').attr('data-item');
            let form = $(e.currentTarget).parents('[data-form]').attr('data-form');
            $(e.currentTarget).parent('form').find('[name=active]').trigger('click');
            wbapp.save($(e.currentTarget),{'table':form,'id':id,'update':'cms.list.pages','silent':'true'})
        });

        $(document).delegate('#yongerPagesTree li[data-item] .dd-edit',wbapp.evClick,function(e){
            e.stopPropagation();
            let data = $(this).parents('[data-item]').data();
            wbapp.ajax({'url':'/cms/ajax/form/'+data.form+'/edit/'+data.item,'append':'modals'});
        });

        $(document).delegate('#yongerPagesTree li[data-item] .dd-add',wbapp.evClick,function(e){
            e.stopPropagation();
            let data = $(this).parents('[data-item]').data();
            wbapp.ajax({'url':'/cms/ajax/form/'+data.inner+'/edit/_new','append':'modals'});
        });

        $(document).delegate('#yongerPagesTree li[data-item] .dd-copy',wbapp.evClick,function(e){
            e.stopPropagation();
            let data = $(this).parents('[data-item]').data();
            data.form == 'pages' ? url = '/module/yonger/copypage/' : url = '/ajax/copy/'+data.form+'/'+data.item+'/';
            wbapp.ajax({'url':url,'item':data.item,'update':'cms.list.pages'});
        });

        $(document).on('bind-cms.list.pages',function(){
            $('#yongerPagesTree').nestable({
                maxDepth: 3,
                beforeDragStop: function(l,e, p){
                    datapath = {};
                    changePath(e,p).then(function(res){
                        if (res !== false) wbapp.post('/cms/ajax/form/pages/path',{'data':datapath});
                    });
                }
            });
        });

        var changePath = async function (e,p) {
            let self = $(e).attr('data-item');
            let name = $(e).attr('data-name');
            let selfpath = $(e).attr('data-path');
            let parent = $(p).closest('.dd-item').find('.dd-path').attr('data-path');
            if (parent == undefined) {parent = '';} 
            if (parent == '/') {parent = '/home'}
            if (selfpath == parent) return false;

            let path = parent + '/' + name;
            datapath[self] = parent;
            $(e).children('.dd-info').find('.dd-path')
                .text(path)
                .attr('data-path',path);
                $(e).find('ol.dd-list .dd-item').each(await function(){
                        changePath(this,e);
                });
        }
        $(document).find('modals').html('');
        $(document).trigger('bind-cms.list.pages');
        }
        yonger.listPages();
    })

    </script>

</html>