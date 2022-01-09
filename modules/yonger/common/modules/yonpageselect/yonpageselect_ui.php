<script type="wbapp" remove>
    if (yonpageselect == undefined) {
        var yonpageselect;
        wbapp.get('/module/yonpageselect/list', function(data) {
            yonpageselect = data;
        })
    }

    $('.yonpageselect').click(function() {
        let $modal = $(wbapp.tpl('wb.modal').html);
        let tpl = wbapp.tpl('#yonPageSelect').html;
        let that = this;
        $modal.find('.modal-body').html(tpl);
        $modal.removeClass('fade').addClass('removable').modal('show');
        Ractive({
            target: $modal.find('.modal-body'),
            data: {
                pages: yonpageselect
            },
            template: tpl
        })
        $modal.delegate('.list-group-item','click',function(){
            $(that).val($(this).data('path'));
            $modal.modal('hide');
        })
    });
</script>
<template id="yonPageSelect">
    <ul class="list-group">
        {{#each pages}}
        <li class="list-group-item text-dark" data-path='{{url}}'>
            <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">{{header}}</h6>
            <span class="d-block tx-11 text-muted">{{url}}</span>
        </li>
        {{/each}}
    </ul>
</template>