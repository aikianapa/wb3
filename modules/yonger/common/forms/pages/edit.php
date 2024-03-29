<html>
<div class="modal effect-scale show removable" id="modalPagesEdit" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
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
                <i class="cursor-pointer fa fa-close r-20 position-absolute" data-dismiss="modal" aria-label="Close"></i>
            </div>
            <div class="modal-body pd-20">
                <div class="row">
                    <div class="col-5 col-lg-4 scroll-y modal-h">
                        <form id="{{_form}}EditForm">
                            <input type="hidden" name="id" value="{{_route.id}}" wb-if="'{{_route.id}}' == '_header' OR '{{_route.id}}' == '_footer'">
                            <div wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">
                                <div class="mb-2 form-group row">
                                    <div class="col-12">
                                        <div class="p-2 mb-0 cursor-pointer btn btn-info btn-block pagelink">
                                            <svg class="d-inline mi mi-link-big" size="24" stroke="FFFFFF" wb-module="myicons"></svg>
                                            {{_route.scheme}}://{{_route.hostname}}<span class="path"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion">
                                    <h6>Основное</h6>
                                    <div>
                                        <div class="mb-2 form-group row" wb-allow="admin">
                                            <label class="col-lg-4 form-control-label">Путь</label>
                                            <!--wb-module wb="module=yonger&mode=pageselect" /-->
                                            <div class="col-lg-8">
                                                <input name="path" wb="module=yonger&mode=pageselect" class="form-control" readonly />
                                            </div>
                                        </div>
                                        <div class="mb-2 form-group row">
                                            <label class="col-lg-4 form-control-label">Наименование</label>
                                            <div class="col-lg-8">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="p-1 input-group-text">
                                                            <input name="active" wb-module="swico">
                                                        </span>
                                                    </div>
                                                    <wb-if wb-if="'{{name}}' !== 'home'">
                                                        <input type="text" name="name" class="form-control" wb="module=smartid" required wb-enabled="admin">
                                                    </wb-if>
                                                    <input type="text" class="form-control" value="{{name}}" disabled readonly wb-if="'{{name}}' == 'home'">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-2 form-group row">
                                            <label class="col-lg-4 form-control-label">Заголовок</label>
                                            <div class="col-lg-8">
                                                <input type="text" name="header" class="form-control" placeholder="Заголовок" wb="module=langinp">
                                            </div>
                                        </div>
                                    </div>
                                    <h6 wb-allow="admin">Пункт меню</h6>
                                    <div wb-allow="admin">
                                        <div class="mb-2 form-group row">
                                            <div class="col-auto">
                                                <input name="menu" wb-module="swico">
                                            </div>
                                            <div class="col">
                                                Отображать пункт в меню
                                            </div>
                                        </div>
                                        <div class="mb-2 form-group row">
                                            <div class="col">
                                                <input type="text" name="menu_title" class="form-control" placeholder="Пункт меню" wb="module=langinp">
                                            </div>
                                        </div>
                                        <div class="mb-2 form-group row">
                                            <div class="col">
                                                <input type="text" name="menu_icon" class="form-control" placeholder="Иконка меню" wb="module=myicons">
                                            </div>
                                        </div>
                                    </div>
                                    <h6>Присоединённый раздел</h6>
                                    <div>
                                        <div class="mb-2 form-group row">
                                            <label class="col-lg-4 form-control-label">Раздел</label>
                                            <div class="col-lg-8">
                                                <select class="form-control" placeholder="{{_lang.attach}}" name="attach">
                                                    <wb-foreach wb-from="_var.attaches" wb-tpl="false">
                                                        <option value="{{_val}}">{{_val}}</option>
                                                    </wb-foreach>
                                                </select>
                                            </div>
                                            <div class="mt-2 col-12">
                                                <input type="text" name="attach_filter" class="form-control" placeholder="{{_lang.filter}}">
                                            </div>
                                            <div class="mt-2 col-12">
                                                <input type="text" name="attach_form" class="form-control" placeholder="{{_lang.form}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <wb-module wb="module=yonger&mode=structure" />
                        </form>
                    </div>

                    <div class="col-7 col-lg-8 scroll-y modal-h">
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
    <script wb-app remove>
        yonger.pageEditor = function() {
            let $form = $('#{{_form}}EditForm');
            $form.undelegate('[name=path]', 'change');
            $form.delegate('[name=path]', 'change', function() {
                let path = $(this).val() + '/';
                $form.find('.path').html(path);
                $form.find('[name=name]').trigger('change');
                $form.find('.accordion').accordion({
                    heightStyle: 'content'
                });
                setTimeout(function() {
                    let blocks = ypbrBlocks.get('blocks')
                    $.each(blocks, function(i, block) {
                        if (block.active == 'on' && block.name == 'seo' && block.alturl !==
                            undefined && block.alturl > ' ') {
                            $form.find('.path').html(block.alturl)
                        }
                    })
                }, 100)
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
            yonger.pageEditor.changePath = function() {
                $form.find('[name=path]').trigger('change');
            }
        }

        yonger.pageEditor();
    </script>
</div>

<wb-lang>
    [ru]
    header = Редактирование страницы
    search = Поиск
    attach = Присоединить раздел
    filter = "Фильтр (json)"
    form = "Форма редактирования (edit)"
    [en]
    header = Page edit
    search = Search
    attach = Attach division
    filter = "Filter (json)"
    form = "Edit form (edit)"
</wb-lang>

</html>