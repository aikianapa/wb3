<html>
<div class="modal fade removable" id="{{_form}}_{{_mode}}" data-show="true" data-keyboard="false" data-backdrop="true"
    role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-close wd-20" data-dismiss="modal" aria-label="Close"></i>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}SwitchItemActive"
                        onchange="$('#{{_form}}ValueItemActive').prop('checked',$(this).prop('checked'));">
                    <label class="custom-control-label" for="{{_form}}SwitchItemActive">Активирован</label>
                </div>
            </div>
            <div class="modal-body">

                <form id="{{_form}}EditForm" class="form-horizontal" role="form">
                    <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}ValueItemActive">
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">{{_lang.name}}</label>
                        <div class="col-sm-10"><input type="text" class="form-control" name="id"
                                placeholder="{{_lang.name}}" wb-module="smartid" required></div>
                    </div>

                    <div class="nav-active-primary">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" href="#{{_form}}Descr"
                                    data-toggle="tab">{{_lang.prop}}</a></li>
                            <li class="nav-item"><a class="nav-link" href="#{{_form}}Text"
                                    data-toggle="tab">{{_lang.content}}</a></li>
                            <li class="nav-item"><a class="nav-link" href="#{{_form}}Images"
                                    data-toggle="tab">{{_lang.images}}</a></li>
                        </ul>
                    </div>
                    <div class="tab-content  p-a m-b-md">
                        <br />
                        <div id="{{_form}}Descr" class="tab-pane fade show active" role="tabpanel">

                            <div class="form-group row">
                                <label class="col-sm-2 form-control-label">{{_lang.header}}</label>
                                <div class="col-sm-10"><input type="text" class="form-control" name="header"
                                        placeholder="{{_lang.header}}"></div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 form-control-label">{{_lang.descr}}</label>
                                <div class="col-sm-10"><input type="text" class="form-control" name="descr"
                                        placeholder="{{_lang.descr}}"></div>
                            </div>
                        </div>

                        <div id="{{_form}}Text" class="tab-pane fade" role="tabpanel">
                            <wb-module wb-module="jodit" name="text" />
                        </div>
                        <div id="{{_form}}Images" class="tab-pane fade" role="tabpanel">
                            <wb-module wb="module=filepicker&mode=multi" wb-path="/uploads/{{_form}}/{{_item}}/"
                                name="images" />
                        </div>
                    </div>
                </form>


            </div>
            <div class="modal-footer">
                <wb-include wb="{'form':'common_formsave.php'}" />
            </div>

        </div>
    </div>
</div>

<wb-lang>
    [en]
    title = "Edit item"
    name = "Item name"
    header = "Header"
    descr = "Description"
    visible = "Visible"
    keywords = "Keywords"
    prop = "Properties"
    content = "Content"
    images = "Images"
    [ru]
    title = "Редактирование записи"
    name = "Имя записи"
    header = "Заголовок"
    descr = "Описание"
    visible = "Отображать"
    keywords = "Ключевые слова"
    prop = "Характеристики"
    content = "Контент"
    images = "Изображения"
</wb-lang>

</html>