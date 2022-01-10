<div class="input-group">
    <div class="input-group-prepend yonpageselect">
        <span class="input-group-text form-control">
            <svg class="mi mi-programing-data.2 size-20" wb-stroke="#000000" wb-module="myicons"></svg>
        </span>
    </div>
    <input>
</div>
<script type="wbapp" remove>
    if (yonpageselect == undefined) {
        var yonpageselect;
        wbapp.get('/module/yonpageselect/list', function(data) {
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
            .modal('show');
        $modal.find('.modal-header').prepend('<input type="search" class="form-control">');
        $modal.find('.modal-body').addClass('p-0 pb-5 scroll-y').html(tpl);
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
            $(that).next('input').val($(this).data('path'));
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