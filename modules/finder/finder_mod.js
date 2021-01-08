$(document).undelegate('.mod-finder input', 'keyup');
$(document).delegate('.mod-finder input','keyup',function(){
    let self = this;
    let finder = $(self).parents('.mod-finder');
    let params = str_replace("'", '"', $(this).attr('data-params'));
    params = json_decode(params);
    params.finder.value = $(this).val();
    $.post('/module/finder/get/', params ,function(data){
        $(finder).find('.dropdown-menu').html(data.render);
        $(finder).find('.dropdown-menu').dropdown('show');
        console.log('Trigger: mod-finder-data');
        $(finder).trigger('mod-finder-data', self, data);
        $(finder).find('.dropdown-item').off('tap click');
        $(finder).find('.dropdown-item').on('tap click', function () {
            let finder = $(self).parents('.mod-finder');
            $(finder).find('input[type=search]').val($(this).text());
            setTimeout(function(){$(finder).find('.dropdown-menu').dropdown('hide');},10);
            console.log('Trigger: mod-finder-value');
            let values = array_values(data.result);
            $(finder).trigger('mod-finder-value', self, values[$(this).index()]);
        });
    });

    $(finder).undelegate('input', 'tap click');
    $(finder).delegate('input', 'tap click', function () {
        $(finder).find('.dropdown-menu').dropdown('hide');
    });
    $('.mod-finder > .dropdown-menu').width($('.mod-finder > input[type=search]').width());
});
