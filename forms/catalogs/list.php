<html>
<div class="m-3" id="yongerCatalogs" wb-allow="admin">
    <div id="{{_form}}List" wb-off>

    <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">{{header}}</h3>
        <button class="navbar-toggler order-2" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <i class="wd-20 ht-20 fa fa-ellipsis-v"></i>
        </button>

        <div class="collapse navbar-collapse order-2" id="navbarSupportedContent">
            <form class="form-inline mg-t-10 mg-lg-0  ml-auto" onsubmit="return false;">
                <div class="form-group">
                    <input class="form-control mg-r-10 col-auto" type="search" placeholder="Поиск..."
                        aria-label="Поиск..." on-change="find">
                </div>
                <a href="#" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/_new','html':'modals'}"
                    class="ml-2 btn btn-primary">
                    <img src="/module/myicons/item-select-plus-add.svg?size=24&stroke=FFFFFF" /> Добавить
                </a>
            </form>
        </div>
    </nav>

        <ul class="list-group">
            {{#each result}}
            <div class="list-group-item d-flex align-items-center" data-id="{{.id}}">
                <div>
                    <a href="javascript:"
                        data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','html':'modals'}"
                        class="tx-13 tx-inverse tx-semibold mg-b-0">{{_id}}</a>
                    <span class="d-block tx-11 text-muted">{{name}}&nbsp;</span>
                </div>

                <div class="custom-control custom-switch pos-absolute r-80">
                    {{#if active=='on' }}
                    <input type="checkbox" class="custom-control-input" name="active" checked
                        id="{{_form}}SwitchItemActive{{ @index }}"
                        onchange="wbapp.save($(this),{'table':'{{_form}}','id':'{{_id}}','field':'active'})">
                    {{/if}}
                    {{#if active !='on' }}
                    <input type="checkbox" class="custom-control-input" name="active"
                        id="{{_form}}SwitchItemActive{{ @index }}"
                        onchange="wbapp.save($(this),{'table':'{{_form}}','id':'{{_id}}','field':'active'})">
                    {{/if}}
                    <label class="custom-control-label" for="{{_form}}SwitchItemActive{{ @index }}">&nbsp;</label>
                </div>
                <div class="pos-absolute r-10 p-0 m-0" style="line-height: normal;">
                    <a href="javascript:"
                        data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','html':'modals'}"
                        class=" d-inline">
                        <svg class="mi mi-content-edit-pen.svg" size="24" stroke="323232" wb-on
                            wb-module="myicons"></svg>
                    </a>
                    <div class="dropdown dropright d-inline">
                        <a class="cursor-pointer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg class="mi mi-trash-delete-bin.2 d-inline" size="24" stroke="dc3545" wb-on wb-module="myicons"></svg>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" on-click="remove">
                                <span class="fa fa-trash tx-danger"></span> Удалить</a>
                            <a class="dropdown-item" href="#">
                                <span class="fa fa-close tx-primary"></span> Отмена</a>
                        </div>
                    </div>
                </div>
            </div>
            {{/each}}
        </ul>
        {{#~/pages }} {{#if ~/pages != 1 }}
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mg-b-0 mg-t-10">
                {{#each pagination}} {{#if ~/page == .page}}
                <li class="page-item active">
                    <a class="page-link" data-page="{{.page}}" on-click="setPage" href="#">
                        {{.label}}
                    </a>
                </li>
                {{/if}} {{#if ~/page != .page}}
                <li class="page-item">
                    <a class="page-link" data-page="{{.page}}" on-click="setPage" href="#">
                        {{#if .label == "prev"}}
                        <svg class="mi mi-interface-essential-181 d-inline" size="18" stroke="0168fa" wb-on wb-module="myicons"></svg>
                        {{/if}} {{#if .label == "next"}}
                        <svg class="mi mi-interface-essential-35 d-inline" size="18" stroke="0168fa" wb-on wb-module="myicons"></svg>
                        {{/if}}
                        {{#if .label != "prev"}}{{#if .label != "next"}}{{.label}}{{/if}}{{/if}}
                    </a>
                </li>
                {{/if}} {{/each}}
            </ul>
        </nav>
        {{/if}} {{/pages}}
    </div>
    <script>
    var api = "/api/v2"
    var form = "{{_form}}"
    var base = api + `/list/${form}?&@size=999999&@sort=_sort`
    var list = new Ractive({
        el: "#{{_form}}List",
        template: $("#{{_form}}List").html(),
        data: {
            base: base,
            result: [],
            pagination: [],
            user: wbapp._session.user,
            header: "{{_lang.catalogs}}"
        },
        on: {
            init() {
                let base = this.get("base");
                wbapp.post(`${base}`, {}, function(data) {
                    list.set("result", data.result);
                    list.set("pagination", data.pagination);
                    list.set("page", data.page);
                    list.set("pages", data.pages);
                    $('#{{_form}}List .list-group').sortable({
                        update: function(ev, line) {
                            let data = {}
                            $(ev.target).children().each(function(i, li) {
                                data[i] = $(li).data('id')
                            })
                            wbapp.post(`/api/v2/func/${form}/sort`, data)
                        }
                    });
                })
                wbapp.get('/form/docs/fldsetsel', function(res) {
                    wbapp.data('catalogs.fldsets', res);
                })
            },
            switch (ev) {
                let data = $(ev.node).data();
                let active = '';
                ev.get('active') == 'on' ? active = '' : active = 'on';
                wbapp.post('/api/v2/update/{{_form}}/' + data.item, {
                    active: active
                }, function(res) {
                    if (res.active !== undefined) ev.set('active', res.active)
                })
            },
            setData(ev, data) {
                list.set("result", data.result);
                list.set("pagination", data.pagination);
                list.set("page", data.page);
                list.set("pages", data.pages);
            },
            setPage(ev) {
                let page = $(ev.node).attr("data-page");
                this.fire("loadPage", page)
                return false
            },
            remove(ev) {
                let id = $(ev.node).parents('[data-id]').attr('data-id');
                let result = list.get("result")
                let page = list.get("page") * 1
                let pages = list.get("pages") * 1
                delete result[id]
                if (Object.keys(result).length == 0 && pages > 0 && page >= pages) page--
                wbapp.post(`${api}/delete/${form}/${id}`, {}, function(data) {
                    if (data.error == false) {
                        list.fire("loadPage", page)
                    }
                });
            },
            find(ev) {
              let find = $(ev.node).val();
                wbapp.post(`${base}`, {
                  filter: {
                    '$or': [
                      {'_id': {'$like':find}},
                      {'name': {'$like':find}}
                    ]
                  }
                }, function(data) {
                    list.set("result", data.result);
                    list.set("pagination", data.pagination);
                    list.set("page", data.page);
                    list.set("pages", data.pages);
                    $('#{{_form}}List .list-group').sortable({
                        update: function(ev, line) {
                            let data = {}
                            $(ev.target).children().each(function(i, li) {
                                data[i] = $(li).data('id')
                            })
                            wbapp.post(`/api/v2/func/${form}/sort`, data)
                        }
                    });
                })
            },
            loadPage(ev, page) {
                wbapp.post(`${base}&@page=${page}`, {}, function(data) {
                    list.fire("setData", null, data)
                })
            }
        }
    })
    $(document).undelegate("#{{_form}}EditForm", 'wb-form-save');
    $(document).delegate("#{{_form}}EditForm", 'wb-form-save', function(ev, res) {
        if (res.params.item !== undefined && res.data !== undefined) {
            list.set("result." + res.data.id, res.data);
        }
    })
    </script>
</div>
    <wb-lang>
        [en]
        catalogs = Catalogs
        [ru]
        catalogs = Справочники
    </wb-lang>
</html>