<html>
<div class="modal effect-scale show removable" id="modalPagesEdit" data-backdrop="static" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header row">
                <div class="col-4">
                    <h5 wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">{{_lang.header}}</h5>
                    <h5 wb-if="'{{_route.id}}' == '_header'">Шапка сайта</h5>
                    <h5 wb-if="'{{_route.id}}' == '_footer'">Подвал сайта</h5>
                </div>
                <div class="col-8">
                    <h5 class='header'></h5>
                </div>
                <i class="fa fa-close r-20 position-absolute cursor-pointer" data-dismiss="modal"
                    aria-label="Close"></i>
            </div>
            <div class="modal-body pd-20">
                <div class="row">
                    <div class="col-5 col-lg-4">
                        <form id="{{_form}}EditForm">
                            <input type="hidden" name="id" value="{{_route.id}}" wb-if="'{{_route.id}}' == '_header' OR '{{_route.id}}' == '_footer'">
                            <div wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">
                                <div class="form-group row mb-2">
                                    <div class="col-12">
                                        <div class="btn btn-info btn-block p-2 mb-0 cursor-pointer pagelink">
                                            <img data-src="/module/myicons/link-big.svg?size=20&stroke=FFFFFF">
                                            {{_route.scheme}}://{{_route.hostname}}<span class="path"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row mb-2" wb-allow="admin">
                                    <label class="col-lg-4 form-control-label">Путь</label>
                                    <!--wb-module wb="module=yonger&mode=pageselect" /-->
                                    <div class="col-lg-8">
                                        <input name="path" wb="module=yonger&mode=pageselect" class="form-control"
                                            readonly />
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label class="col-lg-4 form-control-label">Наименование</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text p-1">
                                                    <input name="active" wb-module="swico">
                                                </span>
                                            </div>
                                            <input type="text" name="name" class="form-control" wb="module=smartid"
                                                required wb-enabled="admin">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row mb-2" wb-allow="admin">
                                    <label class="col-lg-4 form-control-label">Пункт меню</label>
                                    <div class="col">
                                        <input type="text" name="menu_title" class="form-control"
                                            placeholder="Пункт меню" wb="module=langinp">
                                    </div>
                                    <div class="col-auto py-1">
                                        <input name="menu" wb-module="swico" data-size="30">
                                    </div>
                                </div>

                                <div class="form-group row mb-2">
                                    <label class="col-lg-4 form-control-label">Заголовок</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="header" class="form-control" placeholder="Заголовок"
                                            wb="module=langinp" required>
                                    </div>
                                </div>

                                <div wb-if="'{{_sett.devmode}}'=='on'">
                                    <div class="divider-text cursor-pointer"
                                        onclick="$(this).next().toggleClass('d-none')">Присоединённый раздел > </div>
                                    <div class="form-group row d-none mb-2">
                                        <label class="col-lg-4 form-control-label">Раздел</label>
                                        <div class="col-lg-8">
                                            <select class="form-control" placeholder="{{_lang.attach}}" name="attach">
                                                <wb-foreach wb-from="_var.attaches" wb-tpl="false">
                                                    <option value="{{_val}}">{{_val}}</option>
                                                </wb-foreach>
                                            </select>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <input type="text" name="attach_filter" class="form-control"
                                                placeholder="{{_lang.filter}}">
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <wb-module wb="module=yonger&mode=structure" />
                        </form>
                    </div>

                    <div class="col-7 col-lg-8">
                        <div id="yongerBlocksForm">
                            <form method="post">

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
                <wb-include wb="{'form':'common_formsave.php'}" />
            </div>
        </div>
    </div>
</div>

<script wb-app remove>
yonger.pageEditor = function() {
    let $form = $('#{{_form}}EditForm');
    $form.undelegate('[name=path]', 'change');
    $form.delegate('[name=path]', 'change', function() {
        let path = $(this).val() + '/';
        $form.find('.path').html(path);
        $form.find('[name=name]').trigger('change');
    });
    $form.undelegate('[name=name]', 'change keyup');
    $form.delegate('[name=name]', 'change keyup', function() {
        let path = $form.find('[name=path]').val() + '/';
        let name = $(this).val();
        if (path == '/' && name == 'home') name = '';
        $form.find('.path').html(path + name);
    });
    $form.find('[name=path]').trigger('change');

    $form.find('.pagelink').off(wbapp.evClick);
    $form.find('.pagelink').on(wbapp.evClick, function() {
        let url = $(this).text();
        let target = md5(url);
        window.open(url, target).focus();
    })
}

yonger.pageEditor();
</script>
<wb-lang>
    [ru]
    header = Редактирование страницы
    search = Поиск
    attach = Присоединить раздел
    filter = "Фильтр (json)"
    [en]
    header = Page edit
    search = Search
    attach = Attach division
    filter = "Filter (json)"
</wb-lang>

</html>