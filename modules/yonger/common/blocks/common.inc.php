<edit>
<div class="pr-4 yonger-block-common">
    <div class="row">
        <div class="form-group col-md-3 col-sm-6">
            <label class="form-control-label">{{_lang.name}}</label>
            <input type="text" class="form-control" name="header" placeholder="{{_lang.name}}">
        </div>
        <div class="form-group col-md-3 col-sm-6">
            <label class="form-control-label">#id</label>
            <input type="text" class="form-control" name="block_id" placeholder="id#">
        </div>
        <div class="form-group col-md-3 col-sm-6">
            <label class="form-control-label">.class</label>
            <input type="text" class="form-control" name="block_class" placeholder=".class">
        </div>
        <div class="form-group col-md-3 col-sm-6 mb-0">
            <div class="form-group row">
                <div class="col-12">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" name="active"
                            id="switchActive-{{_sess.order_id}}">
                        <label class="custom-control-label nobr"
                            for="switchActive-{{_sess.order_id}}">{{_lang.active}}</label>
                    </div>
                </div>

                <div class="col-12">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" name="container"
                            id="switchContainer-{{_sess.order_id}}">
                        <label class="custom-control-label nobr"
                            for="switchContainer-{{_sess.order_id}}">{{_lang.container}}</label>
                    </div>
                    <button type="button" class="btn pos-absolute tx-14 r-40 py-1 btn-secondary" id="yongerEditorBtnEdit">
                        <img src="/module/myicons/20/FFFFFF/pen-edit-create.2.svg">
                        {{_lang.editor}}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <hr class="mt-0">
</div>

<wb-lang>
    [ru]
    name = "Название"
    id = "#ID"
    class = "Class"
    active = "Отображать блок"
    container = "В контейнере"
    template = Шаблон
    editor = Редактор
[en]
    name = "Name"
    id = "#ID"
    class = "Class"
    active = "Show block"
    container = "Container"
    template = Template
    editor = Editor
</wb-lang>
</edit>