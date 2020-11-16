$.fn.wbFilterChange = function (filter, target, flag = null) {
    if (flag == 'clear') {
        $(filter)[0].reset();
        $(filter).find('[onchange *= "wbAttrChange"]').trigger('change');
    }

    filter = wbapp.objByForm(filter);
    $.each(filter,function(key,val){
        if (val == '') delete filter[key];
    });
    
    wbapp.ajax({ 'target': target, '_tid': target, 'filter_add': filter});
}
