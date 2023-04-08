<div class="input-group">
    <div class="input-group-prepend yonpageselect">
        <span class="p-1 input-group-text form-control">
            <svg class="d-inline mi mi-programing-data.2" size="24" stroke="323232" wb-on wb-module="myicons"></svg>
        </span>
    </div>
    <input>
</div>
<script remove>
    if (yonpageselect == undefined) {
        var yonpageselect;
        wbapp.get('/module/yonger/pageselect', function(data) {
            yonpageselect = data;
        })
    }

    $(document).undelegate('.yonpageselect', 'click');
    $(document).delegate('.yonpageselect', 'click', function() {
        let $modal = $(wbapp.tpl('wb.modal').html);
        let tpl = wbapp.tpl('#yonPageSelect').html;
        let that = this;
        $modal
            .attr('data-backdrop', 'true')
            .removeClass('fade')
            .addClass('effect-slide-in-right left w-50 removable')
            .modal('show')
            .find('.modal-header').prepend('<input type="search" class="form-control">')
            .find('.modal-body').addClass('p-0 pb-5 scroll-y').html(tpl);
        $modal.find('.modal-header input').focus();
        let list = [];
        let url = $(that).next('input').data('url');
        if (url > '') {
            url = "^" + url.replace("/", "\\/");
            let regex = new RegExp(url, "gi");
            $(yonpageselect).each(function(i, item) {
                let str = item.u;
                str.match(regex) ? list.push(item) : null;
            })
        } else {
            list = yonpageselect;
        }
        $modal.list = list;
        $modal.ractive = Ractive({
            target: $modal.find('.modal-body'),
            data: {
                pages: list
            },
            template: tpl
        })

        $modal.delegate('.modal-header input', 'keyup', function() {
            let regex = $(this).val().replace("/", "\\/");
            regex = new RegExp(regex, "gi");
            let list = Object.assign([], $modal.ractive.get('pages'));
            list.forEach((item, i) => {
                let str = item.u + ' ' + item.h;
                let invisible = str.match(regex) ? false : true;
                $modal.ractive.set('pages.'+i+'.invisible', invisible)
            });

        })

        $modal.delegate('.list-group-item', 'click', function() {
            let path = $(this).data('path');
            $(that).next('input').val(path).trigger('change');
            $modal.modal('hide');
        })
    });
</script>
<template id="yonPageSelect">
    <div class="list-group">
        {{#each pages}}
            {{#if invisible !== true}}
                <a href="javascript:void(0)" class="list-group-item text-dark" data-path='{{u}}'>
                    <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">{{h}}</h6>
                    <span class="d-block tx-11 text-muted">{{u}}</span>
                </a>
            {{/if}}
        {{/each}}
    </div>
</template>