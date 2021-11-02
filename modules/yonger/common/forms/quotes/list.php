<html>
<div class="m-3" id="yongerQuotes">
    <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">{{_lang.quotes}}</h3>
        <a href="#" data-ajax="{'url':'/cms/ajax/form/quotes/edit/_new','html':'#yongerQuotes modals'}"
            class="ml-auto order-2 float-right btn btn-primary">
            <img src="/module/myicons/item-select-plus-add.svg?size=24&stroke=FFFFFF" /> {{_lang.newQuote}}
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
        <tbody id="quotesList">
            <wb-foreach wb="table=quotes&sort=_created:d&bind=cms.list.quotes&size={{_sett.page_size}}"
                wb-filter="{'login':'{{_sess.user.login}}' }">
                <tr>
                    <td>{{_created}}</td>
                    <td>{{subject}}</td>
                    <td>{{message}}</td>
                    <td>{{status}}</td>
                    <td>
                        <a href="javascript:"
                            data-ajax="{'url':'/cms/ajax/form/quotes/edit/{{id}}','html':'#yongerQuotes modals'}"
                            class="d-inline">
                            <img src="/module/myicons/content-edit-pen.svg?size=24&stroke=323232">
                        </a>
                        <a href="javascript:"
                            data-ajax="{'url':'/ajax/rmitem/quotes/{{id}}','update':'cms.list.quotes','html':'#yongerQuotes modals'}"
                            class="d-inline">
                            <img src="/module/myicons/trash-delete-bin.2.svg?size=24&stroke=323232" class="d-inline">
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
<script wb-app>

</script>
<wb-lang>
    [ru]
    quotes = Заявки
    search = Поиск
    newQuote = Новая заявка
    [en]
    quotes = Quotes
    search = Search
    newQuote = New quote
</wb-lang>

</html>