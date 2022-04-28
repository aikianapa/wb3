<html>

<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:"><i class="ri-node-tree"></i> {{_lang.catalogs}}</a>
  <button class="navbar-toggler order-2" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
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

      <input class="form-control search-header" type="search" placeholder="Поиск..." aria-label="Поиск..." data-ajax="{'target':'#{{_form}}List','filter_add':{'$or':[{ '_id' : {'$like' : '$value'} }, { 'name': {'$like' : '$value'} }]} }">

      <div class="form-group">
        <button class="btn btn-success" type="button" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/_new','html':'.{{_form}}-edit-modal'}">Создать</button>
      </div>
    </form>
  </div>
</nav>


<div class="list-group m-2" id="{{_form}}List">
  <wb-foreach wb="{'ajax':'/api/query/{{_form}}/','bind':'cms.list.{{_form}}.result','render':'client','size':'{{_sett.page_size}}'}">
    <div class="list-group-item d-flex align-items-center">
      <div>
        <a href="javascript:" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','html':'.{{_form}}-edit-modal'}" class="tx-13 tx-inverse tx-semibold mg-b-0">{{_id}}</a>
        <span class="d-block tx-11 text-muted">{{name}}&nbsp;</span>
      </div>

      <div class="custom-control custom-switch pos-absolute r-80">
        {{#if active=='on' }}
          <input type="checkbox" class="custom-control-input" name="active" checked id="{{_form}}SwitchItemActive{{ @index }}" onchange="wbapp.save($(this),{'table':'{{_form}}','id':'{{_id}}','field':'active'})">
        {{/if}}
        {{#if active !='on' }}
          <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}SwitchItemActive{{ @index }}" onchange="wbapp.save($(this),{'table':'{{_form}}','id':'{{_id}}','field':'active'})">
        {{/if}}
        <label class="custom-control-label" for="{{_form}}SwitchItemActive{{ @index }}">&nbsp;</label>
      </div>
      <div class="pos-absolute r-10 p-0 m-0" style="line-height: normal;">
        <a href="javascript:" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','update':'cms.list.{{_form}}','html':'modals'}" class=" d-inline"><img src="/module/myicons/24/323232/content-edit-pen.svg" width="24" height="24"></a>
        <a href="javascript:" data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{_id}}','update':'cms.list.{{_form}}','html':'modals'}" class=" d-inline"><img src="/module/myicons/24/323232/trash-delete-bin.2.svg" width="24" height="24"></a>
      </div>
    </div>
  </wb-foreach>
  <wb-jq wb="{'append':'#{{_form}}List template','render':'client'}">
    <wb-snippet wb-name="pagination" />
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