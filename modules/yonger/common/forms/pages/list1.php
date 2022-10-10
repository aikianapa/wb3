<html>
<link rel="stylesheet" href="/engine/lib/js/nestable/nestable.css">
               <style>
   .sane {
    //background: #fc3; /* Цвет фона */
   // border: 2px solid black; /* Параметры рамки */
  //  padding: 20px; /* Поля вокруг текста */
    margin-top: 4%; /* Отступ сверху */
   }
  </style>
<script wb-app>
var yongerSpace = new Ractive({
    el: '#yongerSpace',
    template: $('#yongerSpace').html(),
    data: {
        root: {},
        branch: {},
        role: wbapp._session.user.role
    },
    on: {
        init() {
            let that = this
            wbapp.post('/api/v2/list/pages?&id!=[_header,_footer]&path=',{},function(res){
                yongerSpace.set('root',res) 
            })
        },
        render(ev) {
            console.log(ev);
        }
    }
})
</script> 
<div class="m-3" id="yongerSpace" wb-off>
    <nav class="nav navbar navbar-expand-md col">
        <h3 class="tx-bold tx-spacing--2 order-1">Страницы1</h3>
        <div class="ml-auto order-2 float-right">
            <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_header','html':'modals'}"
                class="btn btn-secondary">
                <img src="/module/myicons/24/FFFFFF/menubar-arrow-up.svg" width="24" height="24" /> Шапка
            </a>
            <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_footer','html':'modals'}"
                class="btn btn-secondary">
                <img src="/module/myicons/24/FFFFFF/menubar-arrow-down.svg" width="24" height="24" /> Подвал
            </a>
            <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/edit/_new','html':'modals'}" class="btn btn-primary">
                <img src="/module/myicons/24/FFFFFF/item-select-plus-add.svg" width="24" height="24" /> Добавить
                страницу
            </a>
        </div>
    </nav>

    <div id="yongerPagesTree" class="dd yonger-nested">
        <span class="bg-light">
            <div class="header p-2">
                <span clsss="row">
                    <div class="col-3">
                        <input type="search" class="form-control" placeholder="Поиск страницы"
                            data-ajax="{'target':'#{{_form}}List','filter_add':{'$or':[{ 'header': {'$like' : '$value'} }, { 'url': {'$like' : '$value'} }  ]} }">
                    </div>
                </span>
            </div>

        </span>
        <ol id="pagesList" class="dd-list">
            {{#each root}}
                <li class="dd-item row" data-item="{{id}}" data-name="{{name}}" data-path="{{url}}"
                    data-form="{{_form}}" data-inner="pages">
                    <span class="dd-handle"></span>
                    <span class="dd-text d-flex col-sm-9 ellipsis">
                        <span>{{header}}
                            <br>
                            <span class="dd-path ellipsis" data-path="{{url}}">
                                {{url}}
                            </span>
                        </span>
                    </span>
                    <span class="dd-info col-sm-3">

 <div class="sane">
   <form method="post" class="text-right m-0">
                            <wb-var wb-if='"{{active}}" == ""' stroke="FC5A5A" else="82C43C" />
                            <input type="checkbox" name="active" class="d-none">
                            <img src="/module/myicons/24/0168fa/item-select-plus-add.svg" class="dd-add cursor-pointer"
                                wb-allow="admin">
                            <img src="/module/myicons/24/7987a1/copy-paste-select-add-plus.svg" width="24" height="24"
                                class="dd-copy" wb-allow="admin">
                            <img src="/module/myicons/24/7987a1/content-edit-pen.svg" width="24" height="24"
                                class="dd-edit">
                            <img src="/module/myicons/24/{{_var.stroke}}/power-turn-on-square.1.svg"
                                class="dd-active cursor-pointer" wb-allow="admin">
                            <img src="/module/myicons/24/FC5A5A/trash-delete-bin.2.svg" width="24" height="24"
                                class="dd-remove" wb-allow="admin">
                        </form>
  </div>
                    </span>
                </li>
            {{/each}}
        </ol>
    </div>
</div>
</html>