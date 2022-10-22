<html>
<wb-var ypb="ypb_{{wbNewId()}}" />
<div class="divider-text">{{_lang.structure}}</div>

    <div class="row" id="{{_var.ypb}}">

        <div class="col mb-1" wb-if="'{{_sett.devmode}}'=='on'">
            <div class="input-group order-1">
                <div class="input-group-prepend">
                    <span class="input-group-text p-1" onclick="yonger.pagePresetSelect()">
                        <img data-src="/module/myicons/interface-essential-138.svg?size=24&stroke=323232" width="24"
                            height="24">
                    </span>
                </div>
                <input class="form-control" type="text" name="preset" placeholder="{{_lang.preset}}" autocomplete="off">
                <div class="input-group-append" onclick="yonger.pagePresetSave()">
                    <span class="input-group-text p-1">
                        <img data-src="/module/myicons/floppy-save.svg?size=24&stroke=323232" width="24" height="24">
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-auto" wb-if="'{{_sett.devmode}}'=='on'">
            <button type="button" on-click="blockadd" class="btn btn-block btn-outline-secondary nobr">
                <img src="/module/myicons/20/323232/text-item-list-add-plus.svg" />
                <span class="d-md-none d-lg-inline"> {{_lang.addblk}}</span>
            </button>
        </div>
        <div class="dd yonger-nested pl-3" wb-off>
            <ul class="dd-list" id="{{_var.ypb}}_yonblocks">
                {{#each blocks}}
                <li class="dd-item row" data-form="{{form}}" data-name="{{name}}" data-id="{{id}}">
                    <span class="dd-handle">&nbsp;</span>
                    <span class="dd-text lh-10 col ellipsis">
                        <div class="lh-5">{{header}}</div>
                        <div class="lh-5 tx-gray tx-normal tx-11">{{name}}</div>
                    </span>
                    <span class="dd-info col-auto">
                        <span class="row">
                            <div method="post" class="col-12 text-right m-0 nobr">
                                {{#if active=='on'}}
                                <img src="/module/myicons/24/82C43C/power-turn-on-square.1.svg"
                                    class="dd-active on cursor-pointer"> {{else}}
                                <img src="/module/myicons/24/FC5A5A/power-turn-on-square.1.svg"
                                    class="dd-active cursor-pointer" /> {{/if}}
                                <img src="/module/myicons/24/323232/copy-paste-select-add-plus.svg" on-click="copy"
                                    class="cursor-pointer">
                                <img src="/module/myicons/24/323232/content-edit-pen.svg" on-click="edit"
                                    class="cursor-pointer edit">
                                <img src="/module/myicons/24/323232/trash-delete-bin.2.svg" on-click="remove"
                                    class="cursor-pointer">
                            </div>
                        </span>
                    </span>
                </li>
                {{/each}}
            </ul>
            <textarea type="json" name="blocks" class="d-none"></textarea>
        </div>
    
</div>

<template id="yongerModalBlocksList">
    <div class="modal effect-slide-in-right left w-50" id="modalBlocksList" data-backdrop="true" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <input type="search" class="form-control mg-r-20" on-keyup="search" placeholder="{{_lang.search}}...">
                    <i class="fa fa-close cursor-pointer" data-dismiss="modal" aria-label="Close"></i>
                </div>
                <div class="modal-body p-0 pb-5 scroll-y">
                    <ul class="list-group" wb-off>
                        {{#each blocks}}
                        <li class="list-group-item d-flex align-items-center cursor-pointer" on-click="select"
                            data-name="{{name}}" data-id="{{id}}" data-header="{{header}}">
                            <img src="/module/myicons/search-checkmark-circle.svg?size=30&stroke=323232"
                                class="wd-30 wh-30 rounded-circle mg-r-15" alt="" data-name="{{name}}"
                                on-mouseenter="['viewPreview',true]" on-mouseleave="['viewPreview',false]">
                            <div>
                                <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">{{header}}</h6>
                                <span class="d-block tx-11 text-muted">{{name}}</span>
                            </div>
                        </li>
                        {{/each}}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

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

<script>
var target = '{{target}}';
var $blockform = $(target + ' > form');
var $blocks = $('#{{_var.ypb}} [name=blocks]');
var $modal = $blockform.parents('.modal');
var $current;
var ypbrBlocks = new Ractive({
    el: "#{{_var.ypb}}",
    template: $("#{{_var.ypb}}").html(),
    data: {
        blocks: []
    },
    on: {
        complete() {
            let blocks = []
            let that = this
            this.storage = $(ypbrBlocks.target).children('.yonger-nested').children('textarea[name=blocks]')
            this.current = null
            try {
                blocks = json_decode(this.storage.html(), true)
            } catch (error) {
                null
            }
            let data = []
            $.each(blocks, function(j, blk) {
                blk.id = wbapp.newId()
                data.push(blk)
            })
            this.set('blocks', data)
            $(ypbrBlocks.target).nestable({
                maxDepth: 0,
                beforeDragStop: function(l, e, p) {
                    setTimeout(function() {
                        ypbrBlocks.fire('sort')
                    }, 250)
                }
            })
            $('#{{_var.ypb}}').find('li.dd-item:first img.edit').trigger('click');
        },
        sort(ev, l) {
            let data = [];
            let blocks = ypbrBlocks.get('blocks')
            $('#{{_var.ypb}} .dd-list ').find('.dd-item').each(function(j) {
                let id = $(this).attr('data-id')
                let item = blocks[j]
                item.id = wbapp.newId()
                data.push(item);
            });
            ypbrBlocks.set('blocks', data)
            ypbrBlocks.fire('store')
        },
        store() {
            this.storage.html(json_encode(ypbrBlocks.get('blocks')))
            this.storage.trigger('change')
        },
        edit(ev) {
            let line = $(ev.node).parents('.dd-item');
            let id = line.index();
            let item = ypbrBlocks.get('blocks.' + id);
            line.parents('.dd-list').find('.dd-item').removeClass('active');
            ypbrBlocks.current = line;
            ypbrBlocks.current.addClass('active');
            let $modal = $blockform.parents('.modal[id^=modal][id*=Edit]');
            wbapp.post('/module/yonger/blockform', {
                'item': item
            }, function(editor) {
                $modal.find('.modal-header .header').text($(editor).attr("header"));
                $blockform.html($(editor).html());
                $blockform.find('#yongerEditorBtnEdit').appendTo($modal.find(
                    '.modal-header:first .header'));
                wbapp.refresh();
                $blockform.undelegate('[name=header]:first', 'change');
                $blockform.delegate('[name=header]:first', 'change', function() {
                    ypbrBlocks.get('blocks.' + id + '.header', $(this).val());
                })
            });
        },
        copy(ev) {
            let line = $(ev.node).parents('.dd-item');
            let id = line.index();
            let blocks = ypbrBlocks.get('blocks')
            let item = blocks[id];
            let newid = count(blocks);
            item.header += ' (копия)';
            blocks[newid] = item
            ypbrBlocks.set('blocks', blocks);
            ypbrBlocks.fire('store')
        },
        remove(ev) {
            let that = ev.node;
            let line = $(ev.node).parents('.dd-item');
            let id = line.index();
            if (id > '') {
                $(that).prop('disabled', true);
                wbapp.confirm(null, '{{_lang.rmblk}}').on('confirm', function() {
                    $modal.find('.modal-header .header').text('');
                    $blockform.html('');
                    let data = ypbrBlocks.get('blocks');
                    data.splice(id, 1)
                    ypbrBlocks.set('blocks', data)
                    ypbrBlocks.fire('store')
                    $(that).prop('disabled', false);
                }).on('cancel', function() {
                    $(that).prop('disabled', false)
                });
            }
        },
        blockadd() {
            let $blockslist = $(wbapp.tpl('#yongerModalBlocksList').html);
            let tpl = $($blockslist).html();
            wbapp.ajax({
                'url': '/module/yonger/blocklist'
            }, function(data) {
                let ractive = new Ractive({
                    el: $blockslist,
                    template: tpl,
                    data: {
                        blocks: data.data
                    },
                    on: {
                        complete() {
                            $blockslist.modal('show');
                            $blockslist.list = data.data;
                            $blockslist.find('.modal-header input').focus();
                        },
                        select(ev) {
                            let bid = $(ev.node).data('id');
                            let block = $blockslist.list[bid];
                            if (block.file == undefined) return;
                            let id = wbapp.furl(substr(block.file, 0, -4));
                            if (block.file == 'seo.php' && substr(block.path, 0, 10) ==
                                '/_yonger_/')
                                id = name = 'seo';
                            if (block.file == 'code.php' && substr(block.path, 0, 10) ==
                                '/_yonger_/')
                                id = name = 'code';
                            if (!in_array(id, ['seo', 'code']) && ypbrBlocks.get('blocks.' + id)) {
                                let i = 0;
                                let flag = false;
                                while (flag == false) {
                                    i++;
                                    let suf = id + '_' + i;
                                    if (!ypbrBlocks.get('blocks.' + suf)) {
                                        flag = true;
                                        id = suf;
                                    }
                                }
                            }

                            let data = {
                                'id': wbapp.newId(),
                                'header': block.header,
                                'name': block.name,
                                'form': block.path,
                                'active': 'on'
                            }
                            let blocks = ypbrBlocks.get('blocks')
                            blocks.push(data)
                            ypbrBlocks.set('blocks', blocks)
                            ypbrBlocks.fire('store')
                            setTimeout(() => {
                                $(ypbrBlocks.target).find('li.dd-item:last img.edit').trigger(
                                    'click');
                            }, 100);
                            $blockslist.modal('hide');
                        },
                        search(ev) {
                            let regex = $(ev.node).val().replace("/", "\\/");
                            regex = new RegExp(regex, "gi");
                            let list = {};
                            $.each($blockslist.list, function(i, item) {
                                let str = item.name + ' ' + item.header;
                                str.match(regex) ? list[item.id] = item : null;
                            });
                            ractive.set('blocks', list)
                        }
                    }
                })
            })
        
            }
    }
})

yonger.pagePresetSelect = function() {
    let yonpresetselect = wbapp.postSync('/module/yonger/presets/list/');
    let $modal = $(wbapp.tpl('wb.modal').html);
    let tpl = wbapp.tpl('#yonPresetSelect').html;
    $modal
        .attr('data-backdrop', 'true')
        .removeClass('fade')
        .addClass('effect-slide-in-right left w-50 removable')
        .modal('show');
    $modal.find('.modal-header').prepend('<input type="search" class="form-control">');
    $modal.find('.modal-body').addClass('p-0 pb-5 scroll-y').html(tpl);
    $modal.find('.modal-header input').focus();

    let list = $modal.list = yonpresetselect;

    $modal.ractive = Ractive({
        target: $modal.find('.modal-body'),
        data: {
            presets: list
        },
        template: tpl
    })

    $modal.delegate('.modal-header input', 'keyup', function() {
        let regex = $(this).val();
        let list = $modal.list;
        if (regex > ' ') {
            regex = new RegExp(regex, "gi");
            list = [];
            $.each($modal.list, function(i, item) {
                let str = item.name + ' ' + item.id;
                str.match(regex) ? list.push(item) : null;
            });
        }
        $modal.ractive.reset({
            presets: list
        })
    })

    $modal.delegate('.list-group-item', 'click', function() {
        let name = $(this).data('name');
        let prid = $(this).data('id');
        let ypb = $("#{{_form}}EditForm").find('[id^=ypb_].yonger-nested').attr('id');
        $("#{{_form}}EditForm [name=preset]").val(name).data('id', prid);
        ypbrBlocks.set('blocks', $modal.list[prid].blocks);
        $modal.modal('hide');
    })
}

yonger.pageBlocks = function() {
    let target = '{{target}}';
    let $blockform = $(target + ' > form');
    let $blocks = $('#{{_var.ypb}} [name=blocks]');
    let $modal = $blockform.parents('.modal');
    let $current;
    let timeout = 50;
    if ($blocks.val() == '') $blocks.val('null');

    $blockform.undelegate(':input[name]', 'change');
    $blockform.delegate(':input[name]', 'change', function() {
        if (ypbrBlocks.current !== undefined) {
            let data = $blockform.serializeJson();
            let id = ypbrBlocks.current.index();
            data.id = id;
            data.name = ypbrBlocks.current.attr('data-name');
            data.form = ypbrBlocks.current.attr('data-form');
            ypbrBlocks.set('blocks.' + id, data);
            ypbrBlocks.fire('store')
        }
    })


    $modal.delegate('#yongerEditorBtnEdit', wbapp.evClick, function() {
        if ($('#{{_var.ypb}}').data('current') !== undefined) {
            let form = $current.attr('data-form');
            wbapp.post('/module/yonger/editblock/', {
                'form': form
            }, function(data) {
                $(document).find('modals').append(data);
                $(document).find('#yongerBlockEditor').data('form', form);
                $(document).find('#yongerBlockEditor').modal('show');
            });
        }
    })

}
wbapp.loadStyles(['/engine/lib/js/nestable/nestable.css']);
wbapp.loadScripts(['/engine/lib/js/nestable/nestable.min.js'], 'nestable-ready', function() {
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
    preset = Имя шаблона
    preset_save = Сохранение шаблона
    preset_confirm = Перезаписать уже имеющийся шаблон
    [en]
    seo = SEO
    code = Code includes
    search = Search
    structure = Structure
    addblk = Add block
    rmblk = Remove block?
    preset = Preset name
    preset_save = Preset save
    preset_confirm = Preset is already exixst. Owerwrite?
</wb-lang>

</html>