<html>
<div class="modal fade effect-scale show removable" id="modalQuotesEdit" data-backdrop="static" tabindex="-1"
    role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header row">
                <div class="col-5">
                    <h5>{{_lang.quote}}</h5>
                </div>
                <div class="col-7">
                    <h5 class='header'></h5>
                </div>
                <i class="fa fa-close r-20 position-absolute cursor-pointer" data-dismiss="modal"
                    aria-label="Close"></i>
            </div>
            <div class="modal-body pd-20">
                <form class="row" method="post" id="{{_form}}EditForm">
                    <div class="col-lg-6">
                        <h5>{{_lang.info}}</h5>
                        <div class="form-group">
                            <label class="form-control-label">{{_lang.date}}</label>
                            <input type="datetime" wb-module="datetimepicker" name="_created" class="form-control"
                                placeholder="{{_lang.date}}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-control-label">{{_lang.name}}</label>
                            <input type="text" name="name" class="form-control" placeholder="{{_lang.name}}" required>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-control-label">{{_lang.email}}</label>
                                <input type="email" name="email" class="form-control" placeholder="{{_lang.email}}"
                                    required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-control-label">{{_lang.phone}}</label>
                                <input wb-module="mask" wb-mask="+7 (999) 999-99-99" name="phone" class="form-control"
                                    placeholder="{{_lang.phone}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">{{_lang.subject}}</label>
                            <input type="text" name="subject" class="form-control" placeholder="{{_lang.subject}}"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-control-label">{{_lang.msg}}</label>
                            <textarea name="message" rows="auto" class="form-control" placeholder="{{_lang.msg}}"
                                required></textarea>
                        </div>

                    </div>
                    <div class="col-lg-6">
                        <h5>{{_lang.appends}}</h5>
                        <div>
                            <wb-module name="attaches" wb="{
                                'module':'filepicker',
                                'mode':'multi',
                                'original': true
                                }" />
                        </div>

                        <h5>{{_lang.actions}}</h5>
                        <div class="form-group">
                            <a href="#" class="btn btn-outline-primary my-2">Обработать</a>
                            <a href="#" class="btn btn-outline-primary my-2">Назначить ответственного</a>
                            <a href="#" class="btn btn-outline-primary my-2">Пометить как дубль</a>
                            <a href="#" class="btn btn-outline-danger my-2">Пометьть как спам</a>
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
    date = Дата
    name = Имя
    email = Эл.почта
    phone = Телефон
    quote = Заявка
    subject = Тема
    msg = Обращение
    info = Информация
    appends = Вложения
    actions = Действия
</wb-lang>

</html>