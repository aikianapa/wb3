<html>
<wb-var ypb="ypb_{{wbNewId()}}"/>
<div class="form-group row">

    <nav class="nav navbar col">
        <h5 class="order-1">{{_lang.structure}}</h5>

        <div class="dropdown dropright order-2 d-block">
            <div class="btn-group" role="group">
                <!--a href="#" id="{{_var.ypb}}Seo" class="btn btn-sm btn-outline-secondary nobr"
                wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">{{_lang.seo}}</a>
                <a href="#" id="{{_var.ypb}}Code" class="btn btn-sm btn-outline-secondary nobr"
                wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">{{_lang.code}}</a-->
                <a href="#" id="{{_var.ypb}}Add" class="btn btn-sm btn-outline-secondary nobr">
                    <img src="/module/myicons/20/323232/text-item-list-add-plus.svg" /> {{_lang.addblk}}
                </a>
            </div>
        </div>


    </nav>
    <div class="col-12">
        <div id="{{_var.ypb}}" class="dd yonger-nested pl-3">
            <ul class="dd-list" id="{{_var.ypb}}_yonblocks">
                <wb-foreach wb="bind=yonger.{{_var.ypb}}.blocks&render=client">
                    <li class="dd-item row" data-id="{{id}}" data-form="{{form}}" data-name="{{name}}">
                        <span class="dd-handle"><img src="/module/myicons/20/323232/dots-2.svg" /></span>
                        <span class="dd-text lh-10 col ellipsis">
                            <div class="lh-5">{{header}}</div>
                            <div class="lh-5 tx-gray tx-normal tx-11">{{name}}</div>
                        </span>
                        <span class="dd-info col-auto">
                            <span class="row">
                                <div method="post" class="col-12 text-right m-0 nobr">
                                    {{#if active=='on'}}
                                    <img src="/module/myicons/24/82C43C/power-turn-on-square.1.svg"
                                        class="dd-active on cursor-pointer">
                                    {{else}}
                                    <img src="/module/myicons/24/FC5A5A/power-turn-on-square.1.svg"
                                        class="dd-active cursor-pointer" />
                                    {{/if}}
                                    <img src="/module/myicons/24/323232/copy-paste-select-add-plus.svg"
                                        class="dd-copy cursor-pointer">
                                    <img src="/module/myicons/24/323232/content-edit-pen.svg"
                                        class="dd-edit cursor-pointer">
                                    <img src="/module/myicons/24/323232/trash-delete-bin.2.svg"
                                        class="dd-remove cursor-pointer">
                                </div>
                            </span>
                        </span>
                    </li>
                </wb-foreach>
            </ul>
            <textarea type="json" name="blocks" class="d-none"></textarea>
        </div>
    </div>
</div>

<template id="yongerModalBlocksList">
    <div class="modal effect-slide-in-right left w-50" id="modalBlocksList" data-backdrop="true" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <input type="search" class="form-control mg-r-20" placeholder="{{_lang.search}}...">
                    <i class="fa fa-close cursor-pointer" data-dismiss="modal" aria-label="Close"></i>
                </div>
                <div class="modal-body p-0 pb-5 scroll-y">
                    <div class="list-group">
                        {{#each blocks}}
                        <a class="list-group-item list-group-item-action" href="javascript:void(0)" data-name="{{name}}" data-id="{{id}}">
                            <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">{{header}}</h6>
                            <span class="d-block tx-11 text-muted">{{name}}</span>
                        </a>
                        {{/each}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script wb-app remove>
"use strict"
yonger.pageBlocks = function() {
    let target = '{{target}}';
    let $blockform = $(target + ' > form');
    let $blocks = $('#{{_var.ypb}} [name=blocks]');
    let $modal = $blockform.parents('.modal');
    let $current;
    let timeout = 50;
    if ($blocks.val() == '') $blocks.val('null');

    let data = json_decode($blocks.val(), true);
    $.each(data, function(i, item) {
        if (item.id == undefined) delete data[i];
    });
    wbapp.storage('yonger.{{_var.ypb}}.blocks', data);

    $(document).delegate('#{{_var.ypb}}_yonblocks', 'wb-render-done', function(ev, data) {
        ev.stopPropagation();
        if (!$current) $('#{{_var.ypb}}').find('li.dd-item:first .dd-edit').trigger('click');
        let id = $('#{{_var.ypb}}').data('current');
        $('#{{_var.ypb}}').find('li.dd-item[data-id="' + id + '"]').addClass('active');
    })

    $('#{{_var.ypb}}').nestable({
        maxDepth: 0,
        beforeDragStop: function(l, e, p) {
            let data = {};
            setTimeout(() => {
                $('#{{_var.ypb}} .dd-item').each(function() {
                    let id = $(this).attr('data-id');
                    data[id] = wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id);
                });
                wbapp.storage('yonger.{{_var.ypb}}.blocks', data);
                $blocks.text(json_encode(wbapp.storage('yonger.{{_var.ypb}}.blocks')));
            }, timeout);

        }
    });

    $(document).delegate('#{{_var.ypb}}Add',wbapp.evClick,function(){
        let $blockslist = $(wbapp.tpl('#yongerModalBlocksList').html);
        let tpl = $blockslist.find('.list-group').html();
        wbapp.ajax({'url':'/module/yonger/blocklist'},function(data){
            let ractive = wbapp.ractive($blockslist.find('.list-group'), tpl, {blocks:data.data});
            $blockslist.modal('show');
            $blockslist.list = data.data;
            $blockslist.find('.modal-header input').focus();
            $blockslist
                .delegate('.modal-header input','keyup',function(){
                let regex = $(this).val().replace("/", "\\/");
                regex = new RegExp(regex,"gi");
                let list = {};
                $.each($blockslist.list,function(i,item){
                    let str = item.name+' '+item.header;
                    str.match(regex) ? list[item.id] = item : null;
                });
                ractive.set('blocks',list)
            })
            .delegate('.list-group-item',wbapp.evClick,function(){
                    let bid = $(this).data('id');
                    let block = $blockslist.list[bid];
                    if (block.file == undefined) return;
                    let id = wbapp.furl(substr(block.file,0,-4));
                    if (block.file == 'seo.php' && substr(block.path,0,10) == '/_yonger_/') id = name = 'seo';
                    if (block.file == 'code.php' && substr(block.path,0,10) == '/_yonger_/') id = name = 'code';


                    if (!in_array(id, ['seo','code']) && wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id)) {
                        let i = 0;
                        let flag = false;
                        while( flag == false ) {
                            i++;
                            let suf = id + '_' + i;
                            if (!wbapp.storage('yonger.{{_var.ypb}}.blocks.' + suf)) {
                                flag = true;
                                id = suf;
                            }
                        }
                    }
                    if ($('#{{_var.ypb}}').find('li.dd-item[data-id="'+id+'"]').length) {
                        $('#{{_var.ypb}}').find('li.dd-item[data-id="'+id+'"] .dd-edit').trigger('click');
                        return;
                    }

                    let data = {
                        'id': id,
                        'header': block.name,
                        'name': block.name,
                        'form': block.path,
                        'active': 'on'
                    }
                    wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id, data);
                    setTimeout(() => {
                        $(document).find('#{{_var.ypb}} [name=blocks]').text(json_encode(wbapp.storage('yonger.{{_var.ypb}}.blocks')));
                        $(document).find('#{{_var.ypb}} [name=blocks]').trigger('change');
                        $('#{{_var.ypb}}').find('li.dd-item:last .dd-edit').trigger('click');
                    }, 100);
                    $blockslist.modal('hide');
            });
        })
    })


    wbapp.on('yongerBlockEditorSave', function(e,d){
        let form = $current.attr('data-form');
        let id = $current.attr('data-id')
        blockEdit(id);
    });


    $blockform.undelegate(':input[name]:not(.wb-unsaved)', 'change');
    $blockform.delegate(':input[name]:not(.wb-unsaved)', 'change', function() {
        if ($('#{{_var.ypb}}').data('current') !== undefined) blockSave();
    })


    $modal.delegate('#yongerEditorBtnEdit',wbapp.evClick,function(){
        if ($('#{{_var.ypb}}').data('current') !== undefined) {
            let form = $current.attr('data-form');
            wbapp.post('/module/yonger/editblock/',{'form':form},function(data){
                $(document).find('modals').append(data);
                $(document).find('#yongerBlockEditor').data('form',form);
                $(document).find('#yongerBlockEditor').modal('show');
            });
        }
    })


    $('#{{_var.ypb}}')
    .delegate('.dd-remove', wbapp.evClick, function() {
        let id = $(this).parents('.dd-item').attr('data-id');
        if (id > '') {
            let that = this;
            $(that).prop('disabled',true);
            wbapp.confirm(null,'{{_lang.rmblk}}').on('confirm',function(){
                $modal.find('.modal-header .header').text('');
                $blockform.html('');
                wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id, null);
                setTimeout(() => {
                    $blocks.text(json_encode(wbapp.storage('yonger.{{_var.ypb}}.blocks')));
                }, timeout);
                $(that).prop('disabled',false);
            }).on('cancel',function(){
                $(that).prop('disabled',false)
            });
        }
    })
    .delegate('.dd-copy', wbapp.evClick, function() {
        let id = $(this).parents('.dd-item').attr('data-id');
        let block = wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id);
        id = wbapp.newId();
        block.header += ' (copy)';
        block.id = id;
        wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id, block);
    })
    .delegate('.dd-active', wbapp.evClick, function() {
        if (!$current) $('#{{_var.ypb}}').find('li.dd-item:first .dd-edit').trigger('click');
        let $line = $(this).parents('.dd-item');
        let id = $(this).parents('.dd-item').attr('data-id');
        if ($current.attr('data-id') == id) {
            $blockform.find('.yonger-block-common [name=active]').trigger('click');
        } else {
            let active = 'on';
            if ($(this).hasClass('on')) active = '';
            wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id + '.active', active);
            $blocks.text(json_encode(wbapp.storage('yonger.{{_var.ypb}}.blocks')));
        }
    })
    .delegate('.dd-edit', wbapp.evClick, function(ev) {
        ev.stopPropagation();
        let $line = $(this).parents('.dd-item');
        let id = $line.attr('data-id');
        let item = wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id);
        if ($current && $current.attr('data-id') == $line.attr('data-id')) return false;
        $(this).parents('.dd-list').find('.dd-item').removeClass('active');
        $current = $line;
        $current.addClass('active');
        blockEdit(id);
    })

    let blockSave = function() {
        if ($current !== undefined) {
            let data = $blockform.serializeJson();
            let id = $current.attr('data-id');
            data.id = id;
            data.name = $current.attr('data-name');
            data.form = $current.attr('data-form');
            wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id, data, false);
            $blocks.text(json_encode(wbapp.storage('yonger.{{_var.ypb}}.blocks')));
        }
    }

    let blockEdit = function(id) {
        $('#{{_var.ypb}}').data('current', undefined);
        let $modal = $blockform.parents('.modal');
        let item = wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id);
        if ($('#{{_var.ypb}} .dd-item[data-id="' + id + '"]').length == 0) {
            
        }
        wbapp.post('/module/yonger/blockform',{'item':item},function(editor){
            $modal.find('.modal-header .header').text($(editor).attr("header"));
            $blockform.html($(editor).html());
            $blockform.find('#yongerEditorBtnEdit').appendTo($modal.find('.modal-header .header'));
            wbapp.refresh();
            $blockform.undelegate('[name=header]:first', 'change');
            $blockform.delegate('[name=header]:first','change',function(){
                wbapp.storage('yonger.{{_var.ypb}}.blocks.' + id + '.header',$(this).val());
            })
        });

        $('#{{_var.ypb}}').data('current', id);
    }


    /*
    $(document).delegate('#{{_var.ypb}}Seo', wbapp.evClick, function() {
        if ($current) $current.removeClass('active');
        $current = null;
        $('#modalPagesEditBlocks').find('.list-group-item[data-name=seo]').trigger('click');
    })

    $(document).delegate('#{{_var.ypb}}Code', wbapp.evClick, function() {
        if ($current) $current.removeClass('active');
        $current = null;
        $('#modalPagesEditBlocks').find('.list-group-item[data-name=code]').trigger('click');
    })

    $(document).on('bind',function(ev,data) {
        if (strpos(' '+data.key, 'yonger.{{_var.ypb}}.blocks')) {
            $('#{{_var.ypb}} [name=blocks]').text(json_encode(wbapp.storage('yonger.{{_var.ypb}}.blocks')));
            console.log(json_encode(wbapp.storage('yonger.{{_var.ypb}}.blocks')));
        }
    });
    */
}

    wbapp.loadStyles(['/engine/lib/js/nestable/nestable.css']);
    wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], 'nestable-ready', function () {
        yonger.pageBlocks();
    });
</script>
<wb-lang>
    [ru]
    seo = SEO
    code = Вставки кода
    search = Поиск
    structure = Структура
    addblk = Добавить блок
    rmblk = "Внимание! Вместе с блоком будут удалены все данные, содержащиеся в нём. Подтерждаете удаление?"
    [en]
    seo = SEO
    code = Code includes
    search = Search
    structure = Structure
    addblk = Add block
    rmblk = Remove block?
</wb-lang>

</html>