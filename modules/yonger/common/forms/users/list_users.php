<html>

<div class="m-3" id="yongerSpace">
    <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">{{_lang.users}}</h3>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/users/list_roles/','html':'.content-body'}"
            class="ml-auto order-2 float-right btn btn-secondary">
            <img src="/module/myicons/24/FFFFFF/users-circle.svg" /> {{_lang.roles}}
        </a>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/users/edit/_new','html':'#yongerSpace modals'}"
            class="ml-2 order-2 float-right btn btn-primary">
            <img src="/module/myicons/24/FFFFFF/users-15.svg" /> {{_lang.create}}
        </a>
    </nav>
    <div class="yonger-nested mb-1">
        <span class="bg-light">
            <div class="header p-2">
                <span clsss="row">
                    <div class="col-3">
                        <input type="search" class="form-control" placeholder="{{_lang.search}}">
                    </div>
                </span>
            </div>
        </span>

        <table class="table table-striped table-hover tx-15">
            <thead>
                <tr>
                    <th></th>
                    <th>Имя</th>
                    <th>Контакт</th>
                    <th>Роли</th>
                    <th>Создан</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody id="usersList">
                <wb-foreach wb="table=users&sort=_created:d&bind=cms.list.users"
                    wb-filter="{'isgroup':'' }">
                    <tr>
                        <td>
                            <div class="avatar">
                                <img data-src="/thumbc/50x50/src{{avatar.0.img}}" class="rounded">
                            </div>
                        </td>
                        <td>{{first_name}} {{last_name}}</td>
                        <td class="tx-11">{{wbPhoneFormat({{phone}})}}<br>{{email}}</td>
                        <td>{{role}}</td>
                        <td>{{_created}}</td>
                        <td class="text-right">
                            <div class="custom-control custom-switch d-inline">
                                <input type="checkbox" class="custom-control-input" name="active"
                                    id="{{_form}}SwitchItemActive{{_idx}}"
                                    onchange="wbapp.save($(this),{'table':'{{_form}}','id':'{{_id}}','field':'active','silent':true})">
                                <label class="custom-control-label"
                                    for="{{_form}}SwitchItemActive{{_idx}}">&nbsp;</label>
                            </div>
                            <a href="javascript:"
                                data-ajax="{'url':'/cms/ajax/form/users/edit/{{id}}','html':'#yongerSpace modals'}"
                                class="d-inline">
                                <img src="/module/myicons/content-edit-pen.svg?size=24&stroke=323232">
                            </a>
                            <a href="javascript:"
                                data-ajax="{'url':'/ajax/rmitem/users/{{id}}','update':'cms.list.users','html':'#yongerSpace modals'}"
                                class="d-inline">
                                <img src="/module/myicons/trash-delete-bin.2.svg?size=24&stroke=323232"
                                    class="d-inline">
                            </a>
                        </td>
                    </tr>
                </wb-foreach>
            </tbody>
            <tfoot><tr><td></td></tr></tfoot>
        </table>
    </div>
    <modals></modals>
</div>

<wb-lang>
    [ru]
    users = Пользователи
    search = Поиск пользователя
    create = Создать пользователя
    roles = Роли
    [en]
    users = Users
    search = Search user
    create = Create user
    roles = Roles
</wb-lang>

</html>