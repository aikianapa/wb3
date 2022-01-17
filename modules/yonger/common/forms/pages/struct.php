<html>

<div class="form-group row">

    <nav class="nav navbar col">
        <h5 class="order-1">{{_lang.structure}}</h5>

        <div class="dropdown dropright order-2 d-block">
            <div class="btn-group" role="group">
                <a href="#" id="yongerPageBlockSeo" class="btn btn-sm btn-outline-secondary nobr"
                wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">{{_lang.seo}}</a>
                <a href="#" id="yongerPageBlockCode" class="btn btn-sm btn-outline-secondary nobr"
                wb-if="'{{_route.id}}' !== '_header' && '{{_route.id}}' !== '_footer'">{{_lang.code}}</a>
                <a href="#" id="yongerPageBlockAdd" class="btn btn-sm btn-outline-secondary nobr">
                    {{_lang.addblk}}
                </a>
            </div>
        </div>


    </nav>
    <div class="col-12">
        <div id="yongerPageBlocks" class="dd yonger-nested pl-3">
            <ul class="dd-list" id="yonblocks">
                <wb-foreach wb="bind=yonger.page.blocks&render=client">
                    <li class="dd-item row" data-id="{{id}}" data-form="{{form}}" data-name="{{name}}">
                        <span class="dd-handle"><img src="/module/myicons/20/000000/dots-2.svg" /></span>
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



<script wb-app remove>
yonger.pageBlocks = function() {
    let $blockform = $('#yongerBlocksForm > form');
    let $blocks = $('#yongerPageBlocks [name=blocks]');
    let $modal = $blockform.parents('.modal');
    let $current;
    let timeout = 50;
    if ($blocks.val() == '') $blocks.val('null');

    let data = json_decode($blocks.val(), true);
    $.each(data, function(i, item) {
        if (item.id == undefined) delete data[i];
    });
    wbapp.storage('yonger.page.blocks', data);

    $(document).delegate('#yonblocks', 'wb-render-done', function(ev, data) {
        ev.stopPropagation();
        if (!$current) $('#yongerPageBlocks').find('li.dd-item:first .dd-edit').trigger('click');
        let id = $('#yongerPageBlocks').data('current');
        $('#yongerPageBlocks').find('li.dd-item[data-id="' + id + '"]').addClass('active');
    })

    $('#yongerPageBlocks').nestable({
        maxDepth: 0,
        beforeDragStop: function(l, e, p) {
            let data = {};
            setTimeout(() => {
                $('#yongerPageBlocks .dd-item').each(function() {
                    let id = $(this).attr('data-id');
                    data[id] = wbapp.storage('yonger.page.blocks.' + id);
                });
                wbapp.storage('yonger.page.blocks', data);
                $blocks.text(json_encode(wbapp.storage('yonger.page.blocks')));
            }, timeout);

        }
    });

    $('#modalPagesEdit').delegate('#yongerPageBlockAdd',wbapp.evClick,function(){
        let $modal = $('#modalPagesEditBlocks');
        $modal.modal('show');
        wbapp.ajax({'url':'/module/yonger/blocklist'},function(data){
            $modal.list = data.data;
            wbapp.storage('yonger.blocks', $modal.list);
            $modal.undelegate('.modal-header input', 'keyup');
            $modal.delegate('.modal-header input','keyup',function(){
                let regex = $(this).val().replace("/", "\\/");
                regex = new RegExp(regex,"gi");
                let list = {};
                $.each($modal.list,function(i,item){
                    let str = item.name+' '+item.header;
                    str.match(regex) ? list[item.id] = item : null;
                });
                wbapp.storage('yonger.blocks', list);
            })
        })
    })


    wbapp.on('yongerBlockEditorSave', function(e,d){
        let form = $current.attr('data-form');
        let id = $current.attr('data-id')
        blockEdit(id);
    });


    $blockform.undelegate(':input[name]:not(.wb-unsaved)', 'change');
    $blockform.delegate(':input[name]:not(.wb-unsaved)', 'change', function() {
        if ($('#yongerPageBlocks').data('current') !== undefined) blockSave();
    })


    $modal.delegate('#yongerEditorBtnEdit',wbapp.evClick,function(){
        if ($('#yongerPageBlocks').data('current') !== undefined) {
            let form = $current.attr('data-form');
            wbapp.post('/module/yonger/editblock/',{'form':form},function(data){
                $(document).find('modals').append(data);
                $(document).find('#yongerBlockEditor').data('form',form);
                $(document).find('#yongerBlockEditor').modal('show');
            });
        }
    })


    $('#yongerPageBlocks').delegate('.dd-remove', wbapp.evClick, function() {
        let id = $(this).parents('.dd-item').attr('data-id');
        if (id > '') {
            let that = this;
            $(that).prop('disabled',true);
            wbapp.confirm(null,'{{_lang.rmblk}}').on('confirm',function(){
                $modal.find('.modal-header .header').text('');
                $blockform.html('');
                wbapp.storage('yonger.page.blocks.' + id, null);
                setTimeout(() => {
                    $blocks.text(json_encode(wbapp.storage('yonger.page.blocks')));
                }, timeout);
                $(that).prop('disabled',false);
            }).on('cancel',function(){
                $(that).prop('disabled',false)
            });
        }
    });

    $('#yongerPageBlocks').delegate('.dd-copy', wbapp.evClick, function() {
        let id = $(this).parents('.dd-item').attr('data-id');
        let block = wbapp.storage('yonger.page.blocks.' + id);
        id = wbapp.newId();
        block.header += ' (copy)';
        block.id = id;
        wbapp.storage('yonger.page.blocks.' + id, block);
    });

    var blockSave = function() {
        if ($current !== undefined) {
            let data = $blockform.serializeJson();
            let id = $current.attr('data-id');
            data.id = id;
            data.name = $current.attr('data-name');
            data.form = $current.attr('data-form');
            wbapp.storage('yonger.page.blocks.' + id, data, false);
            $blocks.text(json_encode(wbapp.storage('yonger.page.blocks')));
        }
    }

    var blockEdit = function(id) {
        $('#yongerPageBlocks').data('current', undefined);
        let $blockform = $('#yongerBlocksForm > form');
        let $modal = $blockform.parents('.modal');
        let item = wbapp.storage('yonger.page.blocks.' + id);
        if ($('#yongerPageBlocks .dd-item[data-id="' + id + '"]').length == 0) {
            
        }
        wbapp.post('/module/yonger/blockform',{'item':item},function(editor){
            $modal.find('.modal-header .header').text($(editor).attr("header"));
            $blockform.html($(editor).html());
            $blockform.find('#yongerEditorBtnEdit').appendTo($modal.find('.modal-header .header'));
            wbapp.wbappScripts();
            wbapp.tplInit();
            $blockform.undelegate('[name=header]:first', 'change');
            $blockform.delegate('[name=header]:first','change',function(){
                wbapp.storage('yonger.page.blocks.' + id + '.header',$(this).val());
            })
        });

        $('#yongerPageBlocks').data('current', id);
    }

    $('#yongerPageBlocks').delegate('.dd-active', wbapp.evClick, function() {
        if (!$current) $('#yongerPageBlocks').find('li.dd-item:first .dd-edit').trigger('click');
        let $line = $(this).parents('.dd-item');
        let id = $(this).parents('.dd-item').attr('data-id');
        if ($current.attr('data-id') == id) {
            $blockform.find('.yonger-block-common [name=active]').trigger('click');
        } else {
            let active = 'on';
            if ($(this).hasClass('on')) active = '';
            wbapp.storage('yonger.page.blocks.' + id + '.active', active);
            $blocks.text(json_encode(wbapp.storage('yonger.page.blocks')));
        }
    });


    $('#yongerPageBlocks').delegate('.dd-edit', wbapp.evClick, function(ev) {
        ev.stopPropagation();
        let $line = $(this).parents('.dd-item');
        let id = $line.attr('data-id');
        let item = wbapp.storage('yonger.page.blocks.' + id);
        if ($current && $current.attr('data-id') == $line.attr('data-id')) return false;
        $(this).parents('.dd-list').find('.dd-item').removeClass('active');
        $current = $line;
        $current.addClass('active');
        blockEdit(id);
    })

    $(document).delegate('#yongerPageBlockSeo', wbapp.evClick, function() {
        if ($current) $current.removeClass('active');
        $current = null;
        $('#modalPagesEditBlocks').find('.list-group-item[data-name=seo]').trigger('click');
    })

    $(document).delegate('#yongerPageBlockCode', wbapp.evClick, function() {
        if ($current) $current.removeClass('active');
        $current = null;
        $('#modalPagesEditBlocks').find('.list-group-item[data-name=code]').trigger('click');
    })
/*
    $(document).on('bind',function(ev,data) {
        if (strpos(' '+data.key, 'yonger.page.blocks')) {
            $('#yongerPageBlocks [name=blocks]').text(json_encode(wbapp.storage('yonger.page.blocks')));
            console.log(json_encode(wbapp.storage('yonger.page.blocks')));
        }
    });
    */
}

yonger.yongerPageBlockAdd = function(bid) {
    let id = wbapp.newId();
    let block = wbapp.storage('yonger.blocks.' + bid);
    $('#modalPagesEditBlocks').modal('hide');
    if (block.file !== undefined && block.file == 'seo.php' && substr(block.path,0,10) == '/_yonger_/') id = name = 'seo';
    if (block.file !== undefined && block.file == 'code.php' && substr(block.path,0,10) == '/_yonger_/') id = name = 'code';

    if ($('#yongerPageBlocks').find('li.dd-item[data-id="'+id+'"]').length) {
        $('#yongerPageBlocks').find('li.dd-item[data-id="'+id+'"] .dd-edit').trigger('click');
        return;
    }

    let data = {
        'id': id,
        'header': block.name,
        'name': block.name,
        'form': block.path,
        'active': 'on'
    }
    wbapp.storage('yonger.page.blocks.' + id, data);
    setTimeout(() => {
        $(document).find('#yongerPageBlocks [name=blocks]').text(json_encode(wbapp.storage('yonger.page.blocks')));
        $(document).find('#yongerPageBlocks [name=blocks]').trigger('change');
        $('#yongerPageBlocks').find('li.dd-item:last .dd-edit').trigger('click');
    }, 100);
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
    structure = Структура
    addblk = Добавить блок
    rmblk = "Внимание! Вместе с блоком будут удалены все данные, содержащиеся в нём. Подтерждаете удаление?"
    [en]
    seo = SEO
    code = Code includes
    structure = Structure
    addblk = Add block
    rmblk = Remove block?
</wb-lang>

</html>