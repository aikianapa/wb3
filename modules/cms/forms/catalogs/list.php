<html>

<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:"><i class="ri-node-tree"></i> {{_lang.catalogs}}</a>
  <button class="navbar-toggler order-2" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <i class="wd-20 ht-20 fa fa-ellipsis-v"></i>
  </button>

  <div class="collapse navbar-collapse order-2" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="#" data-ajax="{'target':'#{{_form}}List','filter_remove': 'active'}">Все
          <span class="sr-only">(current)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-ajax="{'target':'#{{_form}}List','filter_remove': 'active','filter_add':{'active':'on'}}">Активные</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-ajax="{'target':'#{{_form}}List','filter_remove': 'active','filter_add':{ 'active': { '$ne': 'on' } } }">Скрытые</a>
      </li>
    </ul>
    <form class="form-inline mg-t-10 mg-lg-0">

          <input class="form-control search-header" type="search" placeholder="Поиск..." aria-label="Поиск..."
           data-ajax="{'target':'#{{_form}}List','filter_add':{'$or':[{ '_id' : {'$like' : '$value'} }, { 'name': {'$like' : '$value'} }]} }">

      <div class="form-group">
        <button class="btn btn-success" type="submit" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/_new','html':'.{{_form}}-edit-modal'}">Создать</button>
      </div>
    </form>
  </div>
</nav>


<div class="list-group m-2" id="{{_form}}List">
  <wb-foreach data-ajax="{'url':'/ajax/form/{{_form}}/list/','bind':'cms.list.{{_form}}','render':'client','size':'{{_sett.page_size}}'}">
    <div class="list-group-item d-flex align-items-center">
      <div>
        <a href="javascript:" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','html':'.{{_form}}-edit-modal'}"
          class="tx-13 tx-inverse tx-semibold mg-b-0">{{_id}}</a>
        <span class="d-block tx-11 text-muted">{{name}}&nbsp;</span>
      </div>

      <div class="custom-control custom-switch pos-absolute r-80">
        {{#if active=='on' }}
          <input type="checkbox" class="custom-control-input" name="active" checked id="{{_form}}SwitchItemActive{{ @index }}"
            onchange="wbapp.save($(this),{'table':'{{_form}}','id':'{{_id}}','field':'active'})">
        {{/if}}
        {{#if active !='on' }}
          <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}SwitchItemActive{{ @index }}"
            onchange="wbapp.save($(this),{'table':'{{_form}}','id':'{{_id}}','field':'active'})">
        {{/if}}
        <label class="custom-control-label" for="{{_form}}SwitchItemActive{{ @index }}">&nbsp;</label>
      </div>

      <a href="javascript:" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','html':'.{{_form}}-edit-modal'}"
        class="pos-absolute r-40"><i class="fa fa-edit"></i></a>
      <div class="dropdown dropright pos-absolute r-10 p-0 m-0" style="line-height: normal;">
        <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
          aria-expanded="false">
          <i class="fa fa-ellipsis-v"></i>
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="#" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','html':'.{{_form}}-edit-modal'}">Изменить</a>
          <a class="dropdown-item" href="#">Переименовать</a>
          <a class="dropdown-item" href="javascript:" data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{_id}}','update':'cms.list.{{_form}}','html':'.{{_form}}-edit-modal'}">Удалить</a>
        </div>
      </div>
    </div>
  </wb-foreach>
  <wb-jq wb="{'append':'#{{_form}}List template','render':'client'}" >
    <wb-snippet wb-name="pagination"/>
  </wb-jq>
</div>
<div class="{{_form}}-edit-modal">

</div>
<wb-lang>
[en]
catalogs = Catalogs
[ru]
catalogs = Справочники
</wb-lang>

</html>
