<html>
<div class="chat-wrapper chat-wrapper-two">

  <div class="chat-sidebar">
    <div class="chat-sidebar-body" style="top:0;bottom:0;">
      <div class="flex-fill pd-y-20 pd-x-10">
        <div class="d-flex align-items-center justify-content-between pd-x-10 mg-b-10">
          <span class="tx-10 tx-uppercase tx-medium tx-color-03 tx-sans tx-spacing-1"><i class="fa fa-users"></i> Роли</span>

            <span data-toggle="tooltip" title="" data-original-title="New role">
              <a href="#" data-ajax="{'url':'/cms/ajax/form/users/role/_new','html':'.users-edit-modal'}">
                <i class="fa fa-plus"></i>
              </a>
            </span>

        </div>
        <nav id="{{_form}}ListRoles" class="nav flex-column nav-chat mg-b-20">
          <wb-foreach data-ajax="{'url':'/ajax/form/users/list/','filter':{'isgroup': 'on'},'bind':'cms.list.roles','render':'client'}">
            <span class="nav-link">
            <a href="#" data-ajax="{'url':'/ajax/form/users/list/','size':'10','filter':{ 'isgroup': { '$ne': 'on' },'role':'{{_id}}' },'bind':'cms.list.users','target':'#{{_form}}List','render':'client'}">
              {{name}}
            </a>
            <a href="#" data-ajax="{'url':'/cms/ajax/form/users/role/{{_id}}','html':'.users-edit-modal'}"
            class="pos-absolute r-10"><i class="fa fa-edit"></i></a>
          </span>
          </wb-foreach>
        </nav>
      </div>
    </div>
  </div>

  <div class="chat-content">

    <nav class="nav navbar navbar-expand-md col">
      <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">Пользователи</a>
      <button class="navbar-toggler order-2" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="wd-20 ht-20 fa fa-ellipsis-v"></i>
      </button>

      <div class="collapse navbar-collapse order-2" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#" data-ajax="{'target':'#{{_form}}List','filter_remove': 'active'}">Все
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
          <div class="form-group wd-200-f mg-r-5">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
              </div>
              <input class="form-control" type="search" placeholder="Поиск..." aria-label="Поиск..."
               data-ajax="{'target':'#{{_form}}List','filter_add':{'$or':[{ 'id' : {'$like' : '$value'} }, { 'first_name': {'$like' : '$value'} }, { 'last_name': {'$like' : '$value'} },{ 'phone': {'$like' : '$value'} },{ 'email': {'$like' : '$value'} } ]} }">
            </div>
          </div>
          <button class="btn btn-success" type="button" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/_new','html':'.users-edit-modal'}">Создать</button>
        </form>
      </div>
    </nav>


    <div class="list-group m-2" id="{{_form}}List">
      <wb-foreach data-ajax="{'url':'/ajax/form/users/list/','size':'10','filter':{ 'isgroup': { '$ne': 'on' } },'bind':'cms.list.users','render':'client'}">
        <div class="list-group-item d-flex align-items-center">
          {{#if !isgroup}}
            <div>
              <a href="javascript:" data-ajax="{'url':'/cms/ajax/form/users/edit/{{_id}}','html':'.users-edit-modal','modal':'#{{_form}}ModalEdit'}"
                class="tx-13 tx-inverse tx-semibold mg-b-0">
                <i class="fa fa-user-o"></i> {{first_name}} {{last_name}}
                <span class="badge badge-secondary"><i class="fa fa-users"></i> {{role}}</span>
                <span class="badge badge-default"> <i class="fa fa-id-badge"></i> {{_id}}</span>
              </a>
              <span class="d-block tx-11 text-muted">
                {{#if email}}
                  <nobr><i class="fa fa-envelope-o"></i> {{email}}</nobr>
                {{/if}}
                {{#if phone}}
                  <nobr><i class="fa fa-phone"></i> {{phone}}</nobr>
                {{/if}}
              </span>
            </div>
          {{else}}
            <div>
              <i class="fa fa-users"></i>
              <a href="javascript:" data-ajax="{'url':'/cms/ajax/form/users/edit/{{_id}}','html':'.users-edit-modal','modal':'#{{_form}}ModalEdit'}"
                class="tx-13 tx-inverse tx-semibold mg-b-0">{{_id}}</a>
            </div>
          {{/if}}

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

          <a href="javascript:" data-ajax="{'url':'/cms/ajax/form/users/edit/{{_id}}','html':'.users-edit-modal'}"
            class="pos-absolute r-40"><i class="fa fa-edit"></i></a>
          <div class="dropdown dropright pos-absolute r-10 p-0 m-0" style="line-height: normal;">
            <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
              aria-expanded="false">
              <i class="fa fa-ellipsis-v"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="#" data-ajax="{'url':'/cms/ajax/form/users/edit/{{_id}}','html':'.users-edit-modal'}">Изменить</a>
              <a class="dropdown-item" href="#">Переименовать</a>
              <a class="dropdown-item" href="javascript:"
                 data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{_id}}','update':'cms.list.users','html':'.users-edit-modal'}">Удалить</a>
            </div>
          </div>
        </div>

        {{#if pagination}}
          {{#if @last===@index}}

            <ul class="pagination mg-b-0 mt-3">
              {{#each pagination}}
                {{#if this.label=="prev" }}
                  <li class="page-item">
                    <a class="page-link page-link-icon" data-page="{{this.page}}" href="#"><i class="fa fa-chevron-left"></i></a>
                  </li>
                  {{elseif this.label == "next"}}
                  <li class="page-item">
                    <a class="page-link page-link-icon" data-page="{{this.page}}" href="#"><i class="fa fa-chevron-right"></i></a>
                  </li>
                {{else}}
                  <li class="page-item">
                    <a class="page-link" data-page="{{this.page}}" href="#">{{this.label}}</a>
                  </li>
                {{/if}}
              {{/each}}
            </ul>

          {{/if}}
        {{/if}}
      </wb-foreach>
    </div>
  </div>

</div>
<div class="users-edit-modal"></div>

</html>
