<html>

<link rel="stylesheet" href="/engine/lib/js/nestable/nestable.css">
<link rel="stylesheet" href="/engine/modules/yonger/tpl/assets/css/yonger.less">
<script type="module">
    import Sortable from '/engine/js/sortable.core.esm.js'
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
            newitem(ev) {
                let data = $(ev.node).parents('[data-item]').data();
                data.editor > '' ? null : data.editor = 'edit'
                wbapp.ajax({
                    'url': '/cms/ajax/form/' + data.inner + '/' + data.editor + '/_new',
                    'append': '#yongerPages modals'
                });
                $('#yongerPages').data('ev', ev)
            },
            newpage(ev) {
                wbapp.ajax({
                    'url': '/cms/ajax/form/pages/edit/_new',
                    'html': '#yongerPages modals'
                })
            },
            edit(ev) {
                let data = $(ev.node).parents('[data-item]').data();
                data.editor > '' ? null : data.editor = 'edit'
                wbapp.ajax({
                    'url': '/cms/ajax/form/' + data.form + '/' + data.editor + '/' + data.item,
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
                if (data.inner !== "pages") {
                    let childs = ev.get('ch');
                    let list = []
                    $(childs).each(function(i, item) {
                        item._form == 'pages' ? list.push(item) : null;
                    })
                    ev.set('ch', list);
                }

            },
            expand(ev) {
                let $parent = $(ev.node).parent('[data-item]')
                let data = $parent.data();
                $parent.removeClass('dd-collapsed')
                wbapp.data('yonger.pagelist.exp_' + data.form + '_' + data.item, true)
                if (data.inner !== "pages") {
                    var options = "?&@size=200&@sort=_sort&@return=id,_id,_form,header,name,url,blocks,active,_sort"
                    let post = {}
                    if (data.filter > '') {
                        if (typeof data.filter == 'object') {
                            post = {
                                filter: data.filter
                            }
                        } else {
                            eval('post = {filter: ' + data.filter + '}');
                        }
                    }
                    wbapp.post('/api/v2/list/' + data.inner + options, post, function(res) {
                        $.each(res.result, function(i, item) {
                            item.ch = []
                            item.url = data.path + '/' + wbapp.furl(item.header)
                            item.menu = ''
                            yongerPages.alturl(item)
                            ev.push('ch', item);
                        })
                        yongerPages.sortable($parent.find('ol')[0])
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
            root: [],
            role: wbapp._session.user.role
        },
        on: {
            init() {
                let that = this
                wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], '', function() {
                    that.update()
                })
            },
            treeInit() {
                let changePath = async function(e, datapath = null) {
                    // передавать не только id, но и позицию в списке, записывая её в поле _sort
                    let ol = $(e).closest('ol');
                    let parent = $(ol).closest('.dd-item').attr('data-path');
                    datapath == null ? datapath = {
                        form: $(e).data('form'),
                        items: {}
                    } : null;
                    parent == undefined ? parent = '' : null;
                    parent == '/home' ? parent = '' : null;
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
                $('#yongerPagesTree').nestable({
                    maxDepth: 15,
                    callback: function(l, e) {
                        changePath(e).then(function(res) {
                            if (res) wbapp.post('/api/v2/func/pages/path', {
                                'data': res
                            });
                        });
                    }
                });
            },
            update() {
                let nested = function(list, path) {
                    let ch = []
                    $.each(list, function(i, item) {
                        yongerPages.alturl(item);
                        if (item !== undefined && item.path == path) {
                            if (wbapp.data('yonger.pagelist.exp_' + item._form + '_' + item.id) !==
                                true) {
                                item.dd_collapsed = "dd-collapsed"
                            } else {
                                item.dd_collapsed = ""
                            }
                            item.menu ? null : item.menu = ''
                            item.inner = "pages"
                            item.ch = nested(list, item.url)
                            if (item.attach > "") {
                                item.inner = item.attach
                                item.dd_collapsed = "dd-collapsed"
                                //    item.ch = []
                            }
                            ch.push(item)
                        }
                    })
                    return ch
                }
                wbapp.post(
                    '/api/v2/list/pages?&id!=[_header,_footer]&@sort=_sort&@return=_id,_form,id,name,header,url,path,active,menu,attach,attach_filter,attach_form,blocks', {},
                    function(res) {
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
                        yongerPages.fire('treeInit')
                    })
            },
            pageadd() {
                wbapp.ajax({
                    'url': '/cms/ajax/form/pages/edit/_new',
                    'html': '#yongerPages modals'
                })
            },
            header() {
                wbapp.ajax({
                    'url': '/cms/ajax/form/pages/edit/_header',
                    'html': '#yongerPages modals'
                })
            },
            footer() {
                wbapp.ajax({
                    'url': '/cms/ajax/form/pages/edit/_footer',
                    'html': '#yongerPages modals'
                })
            },
            complete(ev) {
                $(document).off('wb-form-save')
                $(document).on('wb-form-save', function(e, el) {
                    let root = yongerPages.get('root')
                    let item = el.data.id
                    let form = el.data._form
                    let curr = $('#yongerPages').data('ev')
                    // тут проблема, как правильно разместить запись в дереве
                    if (form == 'pages' && el.params.item == null) {
                        yongerPages.update()
                    } else {
                        if (curr) {
                            let node = $('#yongerPages').data('ev').node
                            let line = $('#yongerPages').data('ev').component;
                            let data = $(node).parents(".dd-item").parents(".dd-item").data()
                            if (form !== "pages" && data.form == 'pages') {
                                line.parent.fire('expand')
                            } else {
                                if (el.data.path == data.path) {
                                    line.set(el.data)
                                } else {
                                    yongerPages.fire('update')
                                }
                            }
                        }
                    }
                    $('#yongerPages').data('ev', undefined)
                })
            },
            find(ev) {
                let find = $(ev.node).val()
                let list = $(yongerPages.el).find('#pagesList')
                if (find == '') {
                    $(list).find('li').removeClass('d-none')
                    return
                } else {
                    $(list).find('li').addClass('d-none');
                }
                $(list).find('li').each(function() {
                    let string = $(this).text()
                    let flag = false
                    eval(`flag = string.match(/${find}/gi)`)
                    if (flag) {
                        $(this).removeClass('d-none')
                        if ($(this).parents('li.dd-collapsed')) {
                            $(this).parents('li.dd-collapsed').removeClass('dd-collapsed')
                        }
                    }
                })
            }
        },
        alturl(item) {
            if (item.blocks !== undefined) {
                $.each(item.blocks, function(i, block) {
                    if (block.name == 'seo' && block.alturl !== undefined && block.alturl > '') {
                        item.url = block.alturl
                    }
                })
            }
            delete item.blocks
        },
        sortable(el) {
            let self = this
            Sortable.create(el, {
                onEnd: function(ev) {
                    let list = []
                    let form = null
                    $(el).children('[data-item]').each((i, item) => {
                        list.push($(item).attr('data-item'))
                        form = $(item).attr('data-form')
                    })
                    if (form !== null && list.length) {
                        $.post('/form/pages/sort', {
                            form: form,
                            list: list
                        }, (data) => {

                        })
                    }
                }
            });
        }
    })
</script>
<div id="yonline" class="d-none" wb-off>
    <li class="dd-item {{dd_collapsed}} row" data-idx="{{@index}}" data-item="{{id}}" data-name="{{name}}" data-path="{{url}}" data-form="{{_form}}" data-editor="{{attach_form}}" data-inner="{{inner}}" data-filter="{{attach_filter}}">
        {{#if ch}}{{#if inner == 'pages'}}
                <button class="dd-collapse" data-action="collapse" type="button" on-click="collapse">Collapse</button>
                <button class="dd-expand" data-action="expand" type="button" on-click="expand">Expand</button>
            {{/if}}{{/if}} {{#if _form == "pages"}}{{#if inner != 'pages'}}
                <button class="dd-collapse" data-action="collapse" type="button" on-click="collapse">Collapse</button>
                <button class="dd-expand" data-action="expand" type="button" on-click="expand">Expand</button>
            {{/if}}{{/if}} {{#if _form == 'pages'}}
            <span class="dd-handle"></span>
        {{else}}
            <span class="pos-absolute t-5 l--10">
                <svg class="mi mi-dots" size="24" stroke="7987a1" wb-on wb-module="myicons"></svg>
            </span>
        {{/if}}
        <span class="dd-text d-flex col-sm-9 ellipsis">
            <span>
                {{#if header !== ""}}
                    <span class="cursor-pointer" on-click="edit">{{header}}</span>
                {{else}}
                    <span class="cursor-pointer tx-gray-400" on-click="edit">Без заголовка</span>
                {{/if}}
                <br>
                <span class="dd-path {{menu}} ellipsis cursor-pointer" on-click="openurl">
                    {{url}}
                </span>
            </span>
        </span>
        <span class="dd-info col-sm-3">
            {{#if ~/role == "admin"}} {{#if inner}} {{#if inner !== "pages"}} {{#if inner !== _form}}
                            <div class="dropdown d-inline">
                                <svg class="cursor-pointer dropdown-toggle mi mi-item-select-plus-add" size="24" stroke="0168fa" wb-on wb-module="myicons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></svg>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" on-click="newpage">Новая страница</a>
                                    <a class="dropdown-item" href="#" on-click="newitem">Новая запись</a>
                                </div>
                            </div>

                        {{/if}} {{/if}} {{/if}}
                <svg class="cursor-pointer mi mi-copy-paste-select-add-plus" size="24" stroke="7987a1" wb-on wb-module="myicons" on-click="copy"></svg>{{/if}}
            <svg class="cursor-pointer mi mi-content-edit-pen.svg" size="24" stroke="7987a1" wb-on wb-module="myicons" on-click="edit"></svg>{{#if ~/role == "admin"}} {{#if active == "on"}}
                    <svg class="cursor-pointer mi mi-power-turn-on-square.1" size="24" stroke="82C43C" wb-on wb-module="myicons" on-click="switch"></svg>
                {{else}}
                    <svg class="cursor-pointer mi mi-power-turn-on-square" size="24" stroke="FC5A5A" wb-on wb-module="myicons" on-click="switch"></svg>{{/if}}
                <svg class="cursor-pointer mi mi-trash-delete-bin.2" size="24" stroke="FC5A5A" wb-on wb-module="myicons" on-click="remove"></svg>{{/if}}
        </span>
        {{#if ch}}
            <ol>
                {{#.ch}}
                    <yonline></yonline>
                {{/.ch}}
            </ol>
        {{/if}}
    </li>
</div>


<div class="m-3" id="yongerPages" wb-off>
    <nav class="px-3 py-2 nav navbar navbar-expand-md col t-0 position-sticky bg-light rounded-10 z-index-150 ">
        <h3 class="order-1 tx-bold tx-spacing--2">Страницы</h3>
        <button class="order-2 navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="true" aria-label="Toggle navigation">
            <i class="wd-20 ht-20 fa fa-ellipsis-v"></i>
        </button>
        <div class="order-3 ml-auto collapse d-md-block" id="navbarSupportedContent">
            <input type="search" class="mb-1 form-control d-inline-block rounded-10 wd-200" placeholder="Поиск" on-keyup="find">
            <div class="d-xs-block d-sm-inline-block">
                <button type="button" class="mb-1 btn btn-secondary" on-click="header">
                    <svg class="mi mi-menubar-arrow-up" size="24" stroke="FFFFFF" wb-on wb-module="myicons"></svg>
                    <span class="d-none d-sm-inline">Шапка</span>
                </button>
                <button type="button" class="mb-1 btn btn-secondary" on-click="footer">
                    <svg class="mi mi-menubar-arrow-down" size="24" stroke="FFFFFF" wb-on wb-module="myicons"></svg>
                    <span class="d-none d-sm-inline">Подвал</span>
                </button>
                <button type="button" class="mb-1 btn btn-primary" on-click="pageadd">
                    <svg class="mi mi-item-select-plus-add" size="24" stroke="FFFFFF" wb-on wb-module="myicons"></svg>
                    <span class="d-none d-sm-inline">Создать</span>
                </button>
            </div>
        </div>
    </nav>

    <div id="yongerPagesTree" class="dd yonger-nested">
        <ol id="pagesList" class="dd-list">
            {{#.root}}
                <yonline></yonline>
                {{/root}}
        </ol>
    </div>
    <modals></modals>
</div>

</html>