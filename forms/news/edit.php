<html>
<div class="modal fade effect-scale show removable" id="modalNewsEdit" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-close wd-20" data-dismiss="modal" aria-label="Close"></i>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}SwitchItemActive" onchange="$('#{{_form}}ValueItemActive').prop('checked',$(this).prop('checked'));">
                    <label class="custom-control-label" for="{{_form}}SwitchItemActive">Активирован</label>
                </div>
            </div>
            <div class="modal-body pd-20">

                <form id="{{_form}}EditForm">
                    <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}ValueItemActive">
                    <div class="form-group row">
                        <div class="input-group col-sm-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                            </div>
                            <input type="datetimepicker" wb="module=datetimepicker&type=datetimepicker" name="date" class="form-control" placeholder="Дата">
                        </div>

                        <div class="col-sm-4">
                            <div class="custom-control custom-radio d-inline mr-3">
                              <input type="radio" id="customRadio2" name="type" value="news" class="custom-control-input" checked>
                              <label class="custom-control-label" for="customRadio2">{{_lang.news}}</label>
                            </div>
                            <div class="custom-control custom-radio d-inline mr-3">
                              <input type="radio" id="customRadio1" name="type" value="article" class="custom-control-input">
                              <label class="custom-control-label" for="customRadio1">{{_lang.article}}</label>
                            </div>
                        </div>


                    </div>


                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#{{_form}}EditForm-tab1" role="tab" aria-controls="{{_form}}EditForm-tab1" aria-selected="true">{{_lang.main}}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#{{_form}}EditForm-tab3" role="tab" aria-controls="{{_form}}EditForm-tab3" aria-selected="false">{{_lang.seo}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#{{_form}}EditForm-tab4" role="tab" aria-controls="{{_form}}EditForm-tab4" aria-selected="false">{{_lang.images}}</a>
                        </li>
                    </ul>
                    <div class="tab-content p-3">
                        <div class="tab-pane fade show active" id="{{_form}}EditForm-tab1" role="tabpanel" aria-labelledby="{{_form}}EditForm-tab1">



                                <div class="form-group row">
                                    <label class="col-sm-2 form-control-label">{{_lang.header}}</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="header" placeholder="{{_lang.header}}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 form-control-label">Тэги</label>
                                    <div class="col-sm-10">
                                        <wb-module wb="{'module':'tagsinput'}" class="form-control" name="tags" placeholder="Тэги" />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-12 form-control-label">Текст</label>
                                    <div class="col-12">
                                        <wb-module wb="{'module':'editor'}" name="text" />
                                    </div>
                                </div>




                        </div>

                        <div class="tab-pane fade" id="{{_form}}EditForm-tab3" role="tabpanel" aria-labelledby="{{_form}}EditForm-tab3">
                                <wb-include wb="form=common&mode=seo" />
                        </div>
                        <div class="tab-pane fade" id="{{_form}}EditForm-tab4" role="tabpanel" aria-labelledby="{{_form}}EditForm-tab4">
                            <wb-module wb="module=filepicker&mode=multi" name="images" />
                        </div>
                    </div>




                </form>

            </div>
            <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
                <wb-include wb="{'form':'common_formsave.php'}" />
            </div>
        </div>
    </div>
</div>
<wb-lang>
[ru]
main = Основное
prop = Свойства
seo = Оптимизация
header = Заголовок
images = Изображения
news = Новость
article = Статья
[en]
main = Main
prop = Properties
seo = SEO
header = Header
images = Images
news = News
article = Article
</wb-lang>
</html>
