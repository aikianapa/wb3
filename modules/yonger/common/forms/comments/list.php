<html>
<div class="m-3" id="yongerSpace">
    <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">{{_lang.header}}</h3>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/_new','html':'#yongerSpace modals'}"
            class="ml-auto order-2 float-right btn btn-primary">
            <img src="/module/myicons/item-select-plus-add.svg?size=24&stroke=FFFFFF" /> {{_lang.create}}
        </a>
    </nav>

    <table class="table table-striped table-hover tx-15">
        <thead>
            <tr>
                <th>Дата</th>
                <th>Тема</th>
                <th>Обращение</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="{{_form}}List">
            <wb-foreach wb="table={{_form}}&sort=_created:d&bind=cms.list.{{_form}}"
                wb-filter="{'login':'{{_sess.user.login}}' }">
                <tr>
                    <td>{{_created}}</td>
                    <td>{{subject}}</td>
                    <td>{{msg}}</td>
                    <td>{{active}}</td>
                    <td>
                        <a href="javascript:"
                            data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{id}}','html':'#yongerSpace modals'}"
                            class="d-inline">
                            <img src="/module/myicons/24/323232/content-edit-pen.svg">
                        </a>
                        <a href="javascript:"
                            data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{id}}','update':'cms.list.comments','html':'#yongerSpace modals'}"
                            class="d-inline">
                            <img src="/module/myicons/24/323232/trash-delete-bin.2.svg" class="d-inline">
                        </a>
                    </td>
                </tr>
            </wb-foreach>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
            </tr>
        </tfoot>
    </table>


    <modals></modals>
</div>

    <wb-lang>
        [ru]
        header = Комментарии
        search = Поиск
        create = Создать
        [en]
        header = Комментарии
        search = Search
        create = Создать
    </wb-lang>

</html>