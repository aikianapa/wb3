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
                <i class="fa fa-close r-20 position-absolute cursor-pointer" data-dismiss="modal" aria-label="Close"></i>
            </div>
            <div class="modal-body pd-20">
                <div class="row">
                    <div class="col-5 col-lg-4">
                        <form id="{{_form}}EditForm">
                            <div wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">

                                <div class="form-group row">
                                    <div class="col-12 mt-1">
                                        <div class="btn btn-info btn-block p-2 mb-0 cursor-pointer pagelink">
                                            <img data-src="/module/myicons/link-big.svg?size=20&stroke=FFFFFF"> {{_route.scheme}}://{{_route.hostname}}<span class="path"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-4 form-control-label">Наименование</label>
                                    <div class="col-lg-8">
                                        <input type="hidden" name="path" wb-enabled="admin">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text p-1">
                                                    <input name="active" wb-module="swico">
                                                </span>
                                            </div>
                                            <input type="text" name="name" class="form-control" wb="module=smartid" required wb-enabled="admin">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-4 form-control-label">Заголовок</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="header" class="form-control" placeholder="Заголовок" wb="module=langinp" required>
                                    </div>
                                </div>

            <div class="form-group row">
                <label class="col-lg-4 form-control-label">Шаблон</label>
                <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text p-1" onclick="yonger.pagePresetSelect()">
                            <img data-src="/module/myicons/interface-essential-138.svg?size=24&stroke=323232" width="24" height="24">
                        </span>
                    </div>
                    <input class="form-control" type="text" name="preset" placeholder="{{_lang.preset}}" autocomplete="off">
                    <div class="input-group-append" onclick="yonger.pagePresetSave()">
                        <span class="input-group-text p-1"><img data-src="/module/myicons/floppy-save.svg?size=24&stroke=323232" width="24" height="24"></span>
                    </div>
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

<div class="modal effect-slide-in-right left w-50" id="modalPagesEditBlocks" data-backdrop="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <input type="search" class="form-control mg-r-20" placeholder="{{_lang.search}}...">
                <i class="fa fa-close cursor-pointer" data-dismiss="modal" aria-label="Close"></i>
            </div>
            <div class="modal-body p-0 pb-5 scroll-y">
                <div class="list-group" id="{{_form}}EditFormListBlocks">
                    <wb-foreach wb="ajax=/module/yonger/blocklist&render=client&bind=yonger.blocks">
                        <a class="list-group-item list-group-item-action" href="javascript:void(0)" data-name="{{name}}" onclick="yonger.yongerPageBlockAdd('{{id}}')">
                            <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">{{header}}</h6>
                            <span class="d-block tx-11 text-muted">{{name}}</span>
                        </a>
                    </wb-foreach>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="yonPresetSelect">
    <div class="list-group">
        {{#each presets}}
        <a href="javascript:void(0)" class="list-group-item text-dark" data-name='{{name}}' data-id='{{id}}'>
            <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">{{name}}</h6>
            <span class="d-block tx-11 text-muted">{{id}}</span>
        </a>
        {{/each}}
    </div>
</template>

<script wb-app>
    let timeout = 50;
    // ==============
    yonger.pageEditor = function() {
        let $form = $('#{{_form}}EditForm');
        $form.delegate('[name=path]', 'change', function() {
            let path = $(this).val() + '/';
            $form.find('.path').html(path);
            $form.find('[name=name]').trigger('change');
        });
        $form.delegate('[name=name]', 'change keyup', function() {
            let path = $form.find('[name=path]').val() + '/';
            let name = $(this).val();
            if (path == '/' && name == 'home') name = '';
            $form.find('.path').html(path + name);
        });
        $form.find('[name=path]').trigger('change');

        $form.find('.pagelink').on(wbapp.evClick, function() {
            let url = $(this).text();
            let target = md5(url);
            window.open(url, target).focus();
        })
    }

    // ===============
    yonger.pagePresetSelect = function() {
        let yonpresetselect = wbapp.postSync('/module/yonger/presets/list/');
        let $modal = $(wbapp.tpl('wb.modal').html);
        let tpl = wbapp.tpl('#yonPresetSelect').html;
        $modal
            .attr('data-backdrop','true')
            .removeClass('fade')
            .addClass('effect-slide-in-right left w-50 removable')
            .modal('show');
        $modal.find('.modal-header').prepend('<input type="search" class="form-control">');
        $modal.find('.modal-body').addClass('p-0 pb-5 scroll-y').html(tpl);
        let list = $modal.list = yonpresetselect;

        $modal.ractive = Ractive({
            target: $modal.find('.modal-body'),
            data: {
                presets: list
            },
            template: tpl
        })
        
        $modal.delegate('.modal-header input','keyup',function(){
            let regex = $(this).val();
            let list = $modal.list;
            if (regex > ' ') {
                regex = new RegExp(regex,"gi");
                list = [];
                $.each($modal.list,function(i,item){
                    let str = item.name+' '+item.id;
                    str.match(regex) ? list.push(item) : null;
                });
            }
            $modal.ractive.reset({presets: list})
        })

        $modal.delegate('.list-group-item','click',function(){
            let name = $(this).data('name');
            let prid = $(this).data('id');
            $("#{{_form}}EditForm [name=preset]").val(name).data('id',prid);
            wbapp.storage('yonger.page.blocks',$modal.list[prid].blocks);
            $modal.modal('hide');
        })
    }

    yonger.pagePresetSave = function() {
        let blocks = wbapp.storage('yonger.page.blocks');
        let data = [];
        let name = $("#{{_form}}EditForm [name=preset]").val();
        let prid = wbapp.furl(name);
        prid = strtolower(str_replace('_','-',prid));
        if (!name) return;
        let save = function() {
            $.each(blocks,function(i,block){
                data.push({
                    'id': wbapp.newId()
                    ,'active': block.active
                    ,'block_id': block.block_id
                    ,'block_class': block.block_class
                    ,'name': block.name
                    ,'header': block.header
                    ,'form': block.form
                    ,'container': block.container
                })
            })
            wbapp.post('/module/yonger/presets/save/',{'name':name,'blocks':data},function(data){
                console.log(data);
            });
        }
        let yonpresetselect = wbapp.postSync('/module/yonger/presets/list/');
        console.log(prid, yonpresetselect);
        if (yonpresetselect[prid] !== undefined) {
            wbapp.confirm("{{_lang.preset_save}}",`{{_lang.preset_confirm}} ${name} ?`)
                .on('confirm',()=>{save()})
                .on('cancel',()=>{});
        } else {
            save();
        }
    }

    yonger.pageEditor();
</script>
    <wb-lang>
        [ru]
        header = Редактирование страницы
        search = Поиск
        preset = Имя шаблона
        preset_save = Сохранение шаблона
        preset_confirm = Перезаписать уже имеющийся шаблон
        [en]
        header = Page edit
        search = Search
        preset = Preset name
        preset_save = Preset save
        preset_confirm = Preset is already exixst. Owerwrite?
    </wb-lang>

</html>