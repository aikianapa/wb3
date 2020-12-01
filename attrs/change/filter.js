$.fn.wbFilterChange = function (filter, target, flag = null) {
    var that = this;
    $(that).prop('disabled',true);
    if (flag == 'clear') {
        $(filter)[0].reset();
        $(filter).find('[onchange *= "wbAttrChange"]').trigger('change');
    }
    filter = wbapp.objByForm(filter);

    $.each(filter,function(key,val){
        if (val == '') delete filter[key];
    });

    wbapp.ajax({ 'target': target, '_tid': target, 'filter': filter},function(){
        $(that).prop('disabled', false);
    });
}
