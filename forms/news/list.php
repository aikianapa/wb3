<html>
<div class="chat-wrapper chat-wrapper-two">
    <div class="chat-sidebar">
        <div class="chat-sidebar-body" style="top:0;bottom:0;">
            <div class="flex-fill pd-y-20 pd-x-10">
                <div class="d-flex align-items-center justify-content-between pd-x-10 mg-b-10">
                    <span class="tx-10 tx-uppercase tx-medium tx-color-03 tx-sans tx-spacing-1">
                      <i class="ri-newspaper-line"></i> {{_lang.category}}</span>
                </div>
                <nav id="{{_form}}ListRoles" class="nav flex-column nav-chat mg-b-20">
                    <span class="nav-link">
                        <a href="#"
                            data-ajax="{'url':'/ajax/form/{{_form}}/list/','size':'10','bind':'cms.list.{{_form}}','target':'#{{_form}}List','render':'client'}" >
                            {{_lang.all}}
                        </a>
                    </span>
                    <wb-foreach wb-json="[{'id':'news'},{'id':'article'}]">
                        <span class="nav-link">
                            <a href="#"
                                data-ajax="{'url':'/ajax/form/{{_form}}/list/','size':'10','filter':{ 'type': '{{id}}' },'bind':'cms.list.{{_form}}','target':'#{{_form}}List','render':'client'}">
                                {{ _lang.{{id}} }}
                            </a>
                        </span>
                    </wb-foreach>
                </nav>
            </div>
        </div>
    </div>

    <div class="chat-content">
        <nav class="nav navbar navbar-expand-md col">
            <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">{{_lang.news}} & {{_lang.article}}</a>
            <button class="navbar-toggler order-2" type="button" data-toggle="collapse"
                data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <i class="wd-20 ht-20 fa fa-ellipsis-v"></i>
            </button>

            <div class="collapse navbar-collapse order-2" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#"
                            data-ajax="{'target':'#{{_form}}List','filter_remove': 'active'}">Все
                            <span class="sr-only">(current)</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"
                            data-ajax="{'target':'#{{_form}}List','filter_remove': 'active','filter_add':{'active':'on'}}">Активные</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"
                            data-ajax="{'target':'#{{_form}}List','filter_remove': 'active','filter_add':{ 'active': { '$ne': 'on' } } }">Скрытые</a>
                    </li>
                </ul>
                <form class="form-inline mg-t-10 mg-lg-0">

                    <input class="form-control search-header" type="search" placeholder="Поиск..." aria-label="Поиск..."
                        data-ajax="{'target':'#{{_form}}List','filter_add':{'$or':[{ '_id' : {'$like' : '$value'} }, { 'header': {'$like' : '$value'} }]} }">


                    <div class="form-group">
                        <button class="btn btn-success" type="submit"
                            data-ajax="{'url':'/cms/ajax/form/news/edit/_new','html':'.news-edit-modal'}"> <i class="fa fa-plus mr-2"></i>{{_lang.create}}</button>
                    </div>
                </form>
            </div>
        </nav>


        <div class="list-group m-2" id="{{_form}}List">
            <wb-foreach
                wb="{'ajax':'/ajax/form/{{_form}}/list/','bind':'cms.list.{{_form}}','render':'client','sort':'date:d','size':'{{_sett.page_size}}'}">
                <div class="list-group-item d-flex align-items-center">
                    <div data-ajax="{'url':'/cms/ajax/form/news/edit/{{_id}}','html':'.news-edit-modal'}" class="w-100">
                        <a href="javascript:" class="tx-13 tx-inverse tx-semibold mg-b-0">{{date}}</a>
                        {{#if type == "article"}}
                        <span class="badge badge-secondary"><i class="ri-group-line"></i> {{_lang.article}}</span>
                        {{else}}
                        <span class="badge badge-primary"><i class="ri-group-line"></i> {{_lang.news}}</span>
                        {{/if}}
                        <span class="d-block tx-11 text-muted">{{header}}&nbsp;</span>
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

                    <a href="javascript:"
                        data-ajax="{'url':'/cms/ajax/form/news/edit/{{_id}}','html':'.news-edit-modal'}"
                        class="pos-absolute r-40"><i class="fa fa-edit"></i></a>
                    <div class="dropdown dropright pos-absolute r-10 p-0 m-0" style="line-height: normal;">
                        <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#"
                                data-ajax="{'url':'/cms/ajax/form/news/edit/{{_id}}','html':'.news-edit-modal'}">{{_lang.edit}}</a>
                            <a class="dropdown-item" href="javascript:"
                                data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{_id}}','update':'cms.list.{{_form}}','html':'.{{_form}}-edit-modal'}">{{_lang.remove}}</a>
                        </div>
                    </div>
                </div>
            </wb-foreach>
            <wb-jq wb="{'append':'#{{_form}}List template','render':'client'}">
                <wb-snippet wb-name="pagination" />
            </wb-jq>
        </div>
        <div class="{{_form}}-edit-modal">
        </div>
    </div>
</div>

<script>
function ajaxModalShow(params, data) {
    $(params.modal).modal('show');
}
</script>
<wb-lang>
    [en]
    category = Category
    find = Search
    edit = Edit
    remove = Remove
    news = News
    article = Atricle
    create = Create
    all = All
    [ru]
    news_and_art = "Новости и статьи"
    category = Категория
    find = Поиск
    edit = Изменить
    remove = Удалить
    create = Создать
    news = Новости
    article = Статьи
    all = Все
</wb-lang>


</html>