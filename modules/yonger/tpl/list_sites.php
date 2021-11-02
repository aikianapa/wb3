<html>
<div class="m-3">
    <h4 class="py-2">
        Мои сайты
    </h4>
    <table class="table table-striped table-hover tx-14">
        <thead>
            <tr>
                <th>Наименование</th>
                <th>Идентификатор</th>
                <th>Создан</th>
                <th>Действие</th>
            </tr>

        </thead>
        <tbody id="yongerListSites">
            <wb-foreach wb="table=sites&sort=_created:d" wb-filter="{'login':'{{_sess.user.login}}'}">
                <tr data-id="{{id}}">
                    <td class="w-50">
                        <a href="#" onclick="yonger.siteWorkspace('{{id}}');">
                            {{name}}
                        </a>
                    </td>
                    <td>{{id}}</td>
                    <td>{{date("d.m.Y H:i",strtotime({{_created}}))}}</td>
                    <td>
                        <a href="#" onclick="yonger.siteWorkspace('{{id}}');">
                            <svg class="mi mi-brush-edit-create-sqaure" wb-module="myicons"></svg>
                        </a>
                        <a href="#"  onclick="yonger.siteRemove('{{id}}');">
                            <svg class="mi mi-trash-delete-bin-2" wb-module="myicons"></svg>
                        </a>
                    </td>
                </tr>
            </wb-foreach>

        </tbody>
    </table>
</div>
</html>