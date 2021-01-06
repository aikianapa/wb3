$(document).undelegate('.mod-finder input', 'keyup');
$(document).delegate('.mod-finder input','keyup',function(){
    let self = this;
    let finder = $(self).parents('.mod-finder');
    let params = str_replace("'", '"', $(this).attr('data-params'));
    params = json_decode(params);
    params.finder.value = $(this).val();
    $.post('/module/finder/get/', params ,function(data){
        $(finder).find('.dropdown-menu').html(data);
        $(finder).find('.dropdown-menu').dropdown('show');
        console.log('Trigger: mod-finder-data');
        $(finder).trigger('mod-finder-data',data);
        $(finder).find('[data-finder-id]').off('tap click');
        $(finder).find('[data-finder-id]').on('tap click', function () {
            let finder = $(self).parents('.mod-finder');
            $(finder).find('input[type=hidden]').val($(this).attr('data-finder-id'));
            $(finder).find('input[type=search]').val($(this).text());
            $(finder).find('input[type=hidden]').trigger('change');
            setTimeout(function(){$(finder).find('.dropdown-menu').dropdown('hide');},10);
        });
    });

    $(finder).undelegate('input', 'tap click');
    $(finder).delegate('input', 'tap click', function () {
        $(finder).find('.dropdown-menu').dropdown('hide');
    });
});

$(window).resize(function(){
    $('.mod-finder > .dropdown-menu').width($('.mod-finder > input[type=search]').width());
});

$(window).trigger('resize');
