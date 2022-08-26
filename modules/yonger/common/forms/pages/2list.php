<html>
<div class="m-3" id="yongerSpace">
    <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">Страницы</h3>
        <div class="ml-auto order-2 float-right" wb-disallow="content">
            <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_header','html':'modals'}"
                class="btn btn-secondary">
                <img src="/module/myicons/24/FFFFFF/menubar-arrow-up.svg" width="24" height="24" /> Шапка
            </a>
            <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_footer','html':'modals'}"
                class="btn btn-secondary">
                <img src="/module/myicons/24/FFFFFF/menubar-arrow-down.svg" width="24" height="24" /> Подвал
            </a>
            <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_new','html':'modals'}" class="btn btn-primary">
                <img src="/module/myicons/24/FFFFFF/item-select-plus-add.svg" width="24" height="24" /> Добавить1
                страницу
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
                            'bind': 'cms.list.pages',
                            'filter': {'_site' : {'$in': [null,'{{_sett.site}}']}, 'id': {'$nin':['_header','_footer']}}
                }">
                <li class="dd-item row" data-item="{{id}}" data-name="{{name}}" data-path="{{url}}"
                    data-form="{{_form}}" data-inner="pages">
                    <span class="dd-handle"></span>
                    <span class="dd-text d-flex col-sm-9 ellipsis">
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
                            <img src="/module/myicons/24/0168fa/item-select-plus-add.svg" class="dd-add cursor-pointer"
                                wb-allow="admin">
                            <img src="/module/myicons/24/7987a1/copy-paste-select-add-plus.svg" width="24" height="24"
                                class="dd-copy" wb-allow="admin">
                            <img src="/module/myicons/24/7987a1/content-edit-pen.svg" width="24" height="24"
                                class="dd-edit">
                            <img src="/module/myicons/24/{{_var.stroke}}/power-turn-on-square.1.svg"
                                class="dd-active cursor-pointer" wb-allow="admin">
                            <img src="/module/myicons/24/FC5A5A/trash-delete-bin.2.svg" width="24" height="24"
                                class="dd-remove" wb-allow="admin">
                        </form>
                    </span>
                </li>
            </wb-foreach>
                <wb-jq wb="$dom->find('template:not(#pagesList)')->remove()" />
        </ol>
    </div>

    <script wb-app>
    wbapp.loadStyles(['/engine/lib/js/nestable/nestable.css']);
    wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], '', function() {

        if (yonger.listPages !== undefined) return;

        yonger.listPages = function() {

            var datapath = []; // для передачи списка при смене путей

            $(document).undelegate('#yongerPagesTree li', 'mouseover');
            $(document).delegate('#yongerPagesTree li', 'mouseover', function(e) {
                $('#yongerPagesTree li').removeClass('hover');
                e.stopPropagation();
                $(this).addClass('hover');
            });

            $(document).undelegate('#yongerPagesTree li', 'mouseout');
            $(document).delegate('#yongerPagesTree li', 'mouseout', function(e) {
                e.stopPropagation();
                $(this).removeClass('hover');
            });

            $(document).undelegate('#yongerPagesTree .dd-active', 'mouseout');
            $(document).delegate('#yongerPagesTree .dd-active', 'mouseout', function(e) {
                e.stopPropagation();
                $(this).removeClass('hover');
            });

            $(document).undelegate('#yongerPagesTree .dd-remove', wbapp.evClick);
            $(document).delegate('#yongerPagesTree .dd-remove', wbapp.evClick, function(e) {
                let data = $(this).parents('[data-item]').data();
                wbapp.confirm('Удаление', `Удалить запись ${data.item} из таблицы ${data.form} ?`, {
                        'bgcolor': 'danger'
                    })
                    .on('confirm', function() {
                        wbapp.ajax({
                            'url': '/ajax/rmitem/' + data.form + '/' + data.item +
                                '?_confirm',
                            'update': 'cms.list.pages',
                            'html': 'modals'
                        });
                    });
                e.stopPropagation();
            });

            $(document).undelegate('#yongerPagesTree .dd-path', wbapp.evClick);
            $(document).delegate('#yongerPagesTree .dd-path', wbapp.evClick, function(e) {
                e.stopPropagation();
                let url = document.location.origin + $(this).attr('data-path');
                let target = md5(url);
                window.open(url, target).focus();
                e.stopPropagation();
            });

            $(document).undelegate('#yongerPagesTree .dd-active', wbapp.evClick);
            $(document).delegate('#yongerPagesTree .dd-active', wbapp.evClick, function(e) {
                e.stopPropagation();
                let id = $(e.currentTarget).parents('[data-item]').attr('data-item');
                let form = $(e.currentTarget).parents('[data-form]').attr('data-form');
                $(e.currentTarget).parent('form').find('[name=active]').trigger('click');
                wbapp.save($(e.currentTarget), {
                    'table': form,
                    'id': id,
                    'update': 'cms.list.pages',
                    'silent': 'true'
                })
            });

            $(document).undelegate('#yongerPagesTree li[data-item] .dd-edit', wbapp.evClick);
            $(document).delegate('#yongerPagesTree li[data-item] .dd-edit', wbapp.evClick, function(e) {
                e.stopPropagation();
                let data = $(this).parents('[data-item]').data();
                wbapp.ajax({
                    'url': '/cms/ajax/form/' + data.form + '/edit/' + data.item,
                    'append': 'modals'
                });
            });

            $(document).undelegate('#yongerPagesTree li[data-item] .dd-add', wbapp.evClick);
            $(document).delegate('#yongerPagesTree li[data-item] .dd-add', wbapp.evClick, function(e) {
                e.stopPropagation();
                let data = $(this).parents('[data-item]').data();
                wbapp.ajax({
                    'url': '/cms/ajax/form/' + data.inner + '/edit/_new',
                    'append': 'modals'
                });
            });

            $(document).undelegate('#yongerPagesTree li[data-item] .dd-copy', wbapp.evClick);
            $(document).delegate('#yongerPagesTree li[data-item] .dd-copy', wbapp.evClick, function(e) {
                e.stopPropagation();
                let data = $(this).parents('[data-item]').data();
                data.form == 'pages' ? url = '/module/yonger/copypage/' : url = '/ajax/copy/' + data
                    .form + '/' + data.item + '/';
                wbapp.ajax({
                    'url': url,
                    'item': data.item,
                    'update': 'cms.list.pages'
                });
            });

            $(document).off('bind-cms.list.pages');
            $(document).on('bind-cms.list.pages', function() {
                $('#yongerPagesTree').nestable('destroy')
                $('#yongerPagesTree').nestable({
                    maxDepth: 5,
                    callback: function(l, e) {
                        changePath(e).then(function(res) {
                            if (res) wbapp.post('/cms/ajax/form/pages/path', {
                                'data': res
                            });
                        });
                    }
                });
            });

            var changePath = async function(e, datapath = null) {
                // передавать не только id, но и позицию в списке, записывая её в поле _sort
                let ol = $(e).closest('ol');
                let parent = $(ol).closest('.dd-item').attr('data-path');
                datapath == null ? datapath = {
                    form: $(e).data('form'),
                    items: {}
                } : null;
                if (parent == undefined) {
                    parent = '';
                }
                if (parent == '/') {
                    parent = '/home'
                }
                $(ol).find(`> .dd-item`).each(function(i) {
                    let data = $(this).data();
                    let path = parent + '/' + data.name;
                    let that = this;
                    $(this).find('.dd-path').text(path).attr('data-path', path);
                    $(this).attr('data-path', path);
                    datapath.items[data.item] = {
                        'i': i,
                        'p': parent
                    };
                    $(this).find('.dd-item[data-form="' + data.form + '"]').each(function(
                    i) {
                        changePath(this, datapath);
                    });
                });
                return datapath;
            }
            $(document).find('modals').html('');
            $(document).trigger('bind-cms.list.pages');
        }
        yonger.listPages();
    })
    </script>
</div>
</html>