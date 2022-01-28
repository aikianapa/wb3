<div class="input-group">
    <div class="input-group-prepend yonpageselect">
        <span class="input-group-text form-control p-1">
            <img data-src="/module/myicons/programing-data.2.svg?size=24&amp;stroke=323232" width="24" height="24">
        </span>
    </div>
    <input>
</div>
<script type="wbapp" remove>
    if (yonpageselect == undefined) {
        var yonpageselect;
        wbapp.get('/module/yonger/pageselect', function(data) {
            yonpageselect = data;
        })
    }

    $(document).undelegate('.yonpageselect','click');
    $(document).delegate('.yonpageselect','click',function() {
        let $modal = $(wbapp.tpl('wb.modal').html);
        let tpl = wbapp.tpl('#yonPageSelect').html;
        let that = this;
        $modal
            .attr('data-backdrop','true')
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
            let regex = new RegExp(url,"gi");
            $(yonpageselect).each(function(i,item){
                let str = item.url;
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
        
        $modal.delegate('.modal-header input','keyup',function(){
            let regex = $(this).val().replace("/", "\\/");
            regex = new RegExp(regex,"gi");
            list = [];
            $($modal.list).each(function(i,item){
                let str = item.url+' '+item.header;
                str.match(regex) ? list.push(item) : null;
            });
            $modal.ractive.reset({pages: list})
        })

        $modal.delegate('.list-group-item','click',function(){
            let path = $(this).data('path');
            if (path == "/") path = "";
            $(that).next('input').val(path).trigger('change');
            $modal.modal('hide');
        })
    });
</script>
<template id="yonPageSelect">
    <div class="list-group">
        {{#each pages}}
        <a href="javascript:void(0)" class="list-group-item text-dark" data-path='{{url}}'>
            <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">{{header}}</h6>
            <span class="d-block tx-11 text-muted">{{url}}</span>
        </a>
        {{/each}}
</div>
</template>