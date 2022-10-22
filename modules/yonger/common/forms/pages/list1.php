<html>
<link rel="stylesheet" href="/engine/lib/js/nestable/nestable.css">
<link rel="stylesheet" href="/engine/modules/yonger/tpl/assets/css/yonger.less">
<script wb-app>
wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'])
var yonline = Ractive.extend({
    isolated: false,
    template: $("#yonline").html(),
    data: {
        role: wbapp._session.user.role
    },
    on: {
        openurl(ev) {
            window.open($(ev.node).text(), '_blank');
        },
        edit(ev) {
            let data = $(ev.node).parents('[data-item]').data();
            wbapp.ajax({
                'url': '/cms/ajax/form/' + data.form + '/edit/' + data.item,
                'append': '#yongerPages modals'
            });
            $('#yongerPages').data('ev', ev)
        },
        copy(ev) {
            let data = $(ev.node).parents('[data-item]').data();
            data.form == 'pages' ? url = '/module/yonger/copypage/' : url = '/ajax/copy/' + data
                .form + '/' + data.item + '/';
            wbapp.ajax({
                'url': url,
                'item': data.item
            }, function(res) {
                if (res.data !== undefined && res.data.id !== undefined) {
                    res.data.ch = []
                    res.data.inner = data.inner
                    res.data.url = data.path + '/' + wbapp.furl(res.data.header)
                    ev.component.parent.splice('ch', data.idx + 1, 0, res.data)
                }
            });
        },
        switch (ev) {
            let data = $(ev.node).parents('[data-item]').data();
            let active = '';
            ev.get('active') == 'on' ? active = '' : active = 'on';
            wbapp.post('/api/v2/update/pages/' + data.item, {
                active: active
            }, function(res) {
                if (res.active !== undefined) ev.set('active', res.active)
            })
        },
        collapse(ev) {
            let data = $(ev.node).parent('[data-item]').data();
            $(ev.node).parent('[data-item]').addClass('dd-collapsed')
            wbapp.data('yonger.pagelist.exp_' + data.form + '_' + data.item, false)
            data.inner == "pages" ? null : ev.set('ch', []);

        },
        expand(ev) {
            let data = $(ev.node).parent('[data-item]').data();
            $(ev.node).parent('[data-item]').removeClass('dd-collapsed')
            wbapp.data('yonger.pagelist.exp_' + data.form + '_' + data.item, true)
            if (data.inner !== "pages") {
                options = "?&@size=200&@return=id,_id,_form,header,name,url"
                wbapp.post('/api/v2/list/' + data.inner + options, {}, function(res) {
                    $.each(res.result, function(i, item) {
                        item.ch = []
                        item.url = data.path + '/' + wbapp.furl(item.header)
                        ev.push('ch', item);
                    })
                })
            }
        },
        remove(ev) {
            let data = $(ev.node).parents('[data-item]').data();
            wbapp.confirm('Удаление', `Удалить запись ${data.item} из таблицы ${data.form} ?`, {
                    'bgcolor': 'danger'
                })
                .on('confirm', function() {
                    wbapp.post(`/api/v2/delete/${data.form}/${data.item}`, {}, function(res) {
                        if (res.error !== undefined && res.error == false) {
                            ev.component.parent.splice('ch', data.idx, 1)
                        }
                    });
                });
        }
    }
});
var yongerPages = new Ractive({
    el: '#yongerPages',
    template: $('#yongerPages').html(),
    components: {
        yonline: yonline
    },
    data: {
        root: {},
        role: wbapp._session.user.role
    },
    on: {
        init() {
            let that = this
            let nested = function(list, path) {
                let ch = []
                $.each(list, function(i, item) {
                    if (item !== undefined && item.path == path) {
                        if (wbapp.data('yonger.pagelist.exp_' + item._form + '_' + item.id) !==
                            true) {
                            item.dd_collapsed = "dd-collapsed"
                        } else {
                            item.dd_collapsed = ""
                        }
                        if (item.attach > "") {
                            item.inner = item.attach
                            item.dd_collapsed = "dd-collapsed"
                            item.ch = []
                        } else {
                            item.inner = "pages"
                            item.ch = nested(list, item.url)
                        }
                        ch.push(item)
                    }
                })
                return ch
            }
            wbapp.post('/api/v2/list/pages?&id!=[_header,_footer]', {}, function(res) {
                let root = []
                $.each(res, function(i, item) {
                    if (item !== undefined && item.path == "" && item.name == "home") {
                        res.splice(i, 1)
                        item.ch = nested(res, "")
                        item.dd_collapsed = ""
                        root.push(item);
                    }
                });
                yongerPages.set('root', root)
            })
        },
        pageadd() {
            wbapp.ajax({'url':'/cms/ajax/form/pages/edit/_new','html':'#yongerPages modals'})
        },
        header() {
            wbapp.ajax({'url':'/cms/ajax/form/pages/edit/_header','html':'#yongerPages modals'})
        },
        footer() {
            wbapp.ajax({'url':'/cms/ajax/form/pages/edit/_footer','html':'#yongerPages modals'})
        },
        render(ev) {
            $(document).off('wb-form-save')
            $(document).on('wb-form-save', function(ev, el) {
                let item = el.data.id
                let form = el.data._form
                let node = $('#yongerPages').data('ev').node
                let line = $('#yongerPages').data('ev').component;

                if (form !== "pages") {
                    el.data.url = $(node).parents(".dd-item").parents(".dd-item").data("path") + "/" +
                        wbapp.furl(el.data.header)
                } else {

                }
                line.set(el.data)
            })

            wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], '', function() {
                let changePath = async function(e, datapath = null) {
                    // передавать не только id, но и позицию в списке, записывая её в поле _sort
                    let ol = $(e).closest('ol');
                    let parent = $(ol).closest('.dd-item').attr('data-path');
                    datapath == null ? datapath = {
                        form: $(e).data('form'),
                        items: {}
                    } : null;
                    parent == undefined ? parent = '' : null;
                    parent == '/' ? parent = '/home' : null;
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
                        $(this).find('.dd-item[data-form="' + data.form + '"]').each(
                            function(i) {
                                changePath(this, datapath);
                            });
                    });
                    return datapath;
                }
                $('#yongerPagesTree').nestable('destroy')
                $('#yongerPagesTree').nestable({
                    maxDepth: 15,
                    callback: function(l, e) {
                        changePath(e).then(function(res) {
                            if (res) wbapp.post('/cms/ajax/form/pages/path', {
                                'data': res
                            });
                        });
                    }
                });
            })
        }
    }
})
</script>
<div id="yonline" class="d-none" wb-off>
    <li class="dd-item {{dd_collapsed}} row" data-idx="{{@index}}" data-item="{{id}}" data-name="{{name}}"
        data-path="{{url}}" data-form="{{_form}}" data-inner="{{inner}}">
        {{#if ch}}
        <button class="dd-collapse" data-action="collapse" type="button" on-click="collapse">Collapse</button>
        <button class="dd-expand" data-action="expand" type="button" on-click="expand">Expand</button>
        {{/if}}
        {{#if _form == "pages"}}{{#if inner != 'pages'}}
        <button class="dd-collapse" data-action="collapse" type="button" on-click="collapse">Collapse</button>
        <button class="dd-expand" data-action="expand" type="button" on-click="expand">Expand</button>
        {{/if}}{{/if}}
        <span class="dd-handle"></span>
        <span class="dd-text d-flex col-sm-9 ellipsis">
            <span>
                <span class="cursor-pointer" on-click="edit">{{header}}</span>
                <br>
                <span class="dd-path ellipsis cursor-pointer" on-click="openurl">
                    {{url}}
                </span>
            </span>
        </span>
        <span class="dd-info col-sm-3">
            {{#if ~/role == "admin"}}
            {{#if inner}}
            {{#if inner !== "pages"}}
            {{#if inner !== _form}}
            <img src="/module/myicons/24/0168fa/item-select-plus-add.svg" class="dd-add cursor-pointer">
            {{/if}}
            {{/if}}
            {{/if}}
            <img src="/module/myicons/24/7987a1/copy-paste-select-add-plus.svg" width="24" height="24"
                class="cursor-pointer" on-click="copy"> {{/if}}
            <img src="/module/myicons/24/7987a1/content-edit-pen.svg" width="24" height="24" class="cursor-pointer"
                on-click="edit"> {{#if ~/role == "admin"}} {{#if active == "on"}}
            <img src="/module/myicons/24/82C43C/power-turn-on-square.1.svg" width="24" height="24"
                class="cursor-pointer" on-click="switch"> {{else}}
            <img src="/module/myicons/24/FC5A5A/power-turn-on-square.svg" width="24" height="24" class="cursor-pointer"
                on-click="switch"> {{/if}}
            <img src="/module/myicons/24/FC5A5A/trash-delete-bin.2.svg" width="24" height="24" class="cursor-pointer"
                on-click="remove"> {{/if}}
        </span>
        <ol>
            {{#.ch}}
            <yonline></yonline>
            {{/.ch}}
        </ol>
    </li>
</div>


<div class="m-3" id="yongerPages" wb-off>
    <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">Страницы1</h3>
        <div class="ml-auto order-2 float-right">
            <button type="button" class="btn btn-secondary" on-click="header">
                <img src="/module/myicons/24/FFFFFF/menubar-arrow-up.svg" width="24" height="24" /> Шапка
            </button>
            <button type="button" class="btn btn-secondary" on-click="footer">
                <img src="/module/myicons/24/FFFFFF/menubar-arrow-down.svg" width="24" height="24" /> Подвал
            </button>
            <button type="button" class="btn btn-primary" on-click="pageadd">
                <img src="/module/myicons/24/FFFFFF/item-select-plus-add.svg" width="24" height="24" /> Добавить
                страницу
            </button>
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
            {{#.root}}
            <yonline></yonline>
            {{/root}}
        </ol>
    </div>
    <modals></modals>
</div>

</html>