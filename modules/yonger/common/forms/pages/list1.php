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
                <img src="/module/myicons/24/FFFFFF/item-select-plus-add.svg" width="24" height="24" /> Добавить
                страницу
            </a>
        </div>
    </nav>


    <div id="yongerPagesTree" class="dd yonger-nested">
        <template id="tplYongerPagesTree">
            <ol id="pagesList" class="dd-list">
                {{#each list}}
                    <li class="dd-item dd-collapsed row" data-item="{{.id}}" data-index="{{.index}}" data-path="{{.url}}" data-form="{{._form}}" data-inner="pages" on-click="branchToggle">
                        <span class="dd-handle"></span>
                        <span class="dd-text d-flex col-sm-9 ellipsis">
                            <span>{{.header}}
                                <br>
                                <span class="dd-path ellipsis" data-path="{{.url}}">
                                    {{.url}}
                                </span>
                            </span>
                        </span>
                        <span class="dd-info col-sm-3">
                            <form method="post" class="text-right m-0">
                                <wb-var wb-if='"{{.active}}" == ""' stroke="FC5A5A" else="82C43C" />
                                <input type="checkbox" name="active" class="d-none">
                                {{#if ~/role == "admin"}}
                                <img src="/module/myicons/24/0168fa/item-select-plus-add.svg" class="dd-add cursor-pointer">
                                <img src="/module/myicons/24/7987a1/copy-paste-select-add-plus.svg" width="24" height="24" class="dd-copy">
                                {{/if}}
                                <img src="/module/myicons/24/7987a1/content-edit-pen.svg" width="24" height="24" class="dd-edit">
                                {{#if ~/role == "admin"}}
                                <img src="/module/myicons/24/{{_var.stroke}}82C43C/power-turn-on-square.1.svg" class="dd-active cursor-pointer">
                                <img src="/module/myicons/24/FC5A5A/trash-delete-bin.2.svg" width="24" height="24" class="dd-remove">
                                {{/if}}
                            </form>
                        </span>
                        {{#if .list}}
                            <ol class="dd-list">
                            </ol>
                        {{/if}}
                    </li>
                {{/each}}
            </ol>
        </template>
    </div>

    <script wb-app>
    wbapp.loadStyles(['/engine/lib/js/nestable/nestable.css']);
    wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], '', function() {
        function listPages(el = '#yongerPagesTree', index = 'list', path = '') {
            var tpl = wbapp.tpl('#tplYongerPagesTree').html
            if (index !== 'list') {
                tpl = $(tpl).html();
            }
            new Ractive({
                target: el,
                template: tpl,
                data: {
                    lang: wbapp._settings.locale,
                    role: wbapp._session.user.role,
                    list: {},
                    data: []
                },
                on: {
                    init: function() {
                        if (index == 'list') {
                            this.fire('getList')
                        } else {
                            this.fire('getBranch',index, path)
                        }
                    },
                    getList() {
                        let that = this
                        wbapp.post('/api/v2/list/pages', {}, async function(res) {
                                $.each(res,function(i,item){
                                    if (substr(item.id,0,1) !== '_') {
                                        if (typeof item.header == 'object') {
                                            item.header = item.header[wbapp._settings.locale]
                                        }
                                        that.push('data', item) 
                                    }
                                })
                                $('#yongerPagesTree')[0].data = that.get('data')
                                that.fire('getBranch',index, path)
                                $('#yongerPagesTree').nestable('destroy')
                                $('#yongerPagesTree').nestable({maxDepth: 5})
                        })
                    },
                    getBranch(ev, idx = '', path = "") {
                        let that = this
                        let data = $('#yongerPagesTree')[0].data
                        $.each(data, async function(i,item){
                            if (item.path == path) {
                                let index = idx+'.'+item.id
                                item.index = index
                                console.log(index, item);
                                that.set(index,item)
                                that.fire('getBranch',index+'.list',item.url)
                            }
                        })
                    },
                    branchToggle(ev) {
                        let data = $(ev.node).data()
                        let index = data.index + ".list"
                        if ($(ev.original.target).is('.dd-expand')) {
                            console.log(index,data.path);
                            listPages(`#yongerPagesTree li[data-index="${data.index}"] > .dd-list`, index, data.path)
                        }
                    }
                }
            })
        }
        listPages();
    })
    </script>
</div>

</html>