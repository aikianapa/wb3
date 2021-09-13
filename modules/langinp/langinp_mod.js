$(document).off('langinp-mod-js');
$(document).on('langinp-mod-js', function () {

    $(document).undelegate('.dropdown.mod-langinp > .input-group-prepend', 'tap click');
    $(document).delegate('.dropdown.mod-langinp > .input-group-prepend', 'tap click', function () {
        $(this).parents('.input-group').children('input.mod-langinp').trigger('change');
    });

    $(document).undelegate('.dropdown.mod-langinp', 'hide.bs.dropdown');
    $(document).delegate('.dropdown.mod-langinp', 'hide.bs.dropdown', function () {
        let form = $(this).find('.dropdown-menu');
        let json = {};
        $(form).find('input').each(function () {
            json[$(this).attr('data-name')] = $(this).val();
        })
        $(this).closest('.input-group').find('textarea.mod-langinp').html(json_encode(json));
        $(this).closest('.input-group').find('input.mod-langinp').val(json[wbapp._session.lang]);
        $(this).closest('.input-group').find('input.mod-langinp').trigger('change');
    });

    $(document).undelegate('.dropdown.mod-langinp', 'show.bs.dropdown');
    $(document).delegate('.dropdown.mod-langinp', 'show.bs.dropdown', function () {
        let form = $(this).find('.dropdown-menu');
        let json = {};
        let width = $(this).closest('.input-group').width();
        $(form).width(width);
        try {
            json = json_decode($(this).closest('.input-group').find('textarea.mod-langinp').html());
        } catch (error) {
            null;
        }
        $(form).find('input[data-name]').each(function () {
            if (json[$(this).attr('data-name')] !== undefined) {
                $(this).val(json[$(this).attr('data-name')]);
            }
        })
    });

    $(document).undelegate('input.mod-langinp', 'change keyup');
    $(document).delegate('input.mod-langinp', 'change keyup',function(e){
        let form = $(this).parents('.input-group').find('.dropdown-menu');
        $(form).find('[data-name="' + wbapp._session.lang + '"]').val($(this).val());
        let json = {};
        $(form).find('input').each(function () {
            json[$(this).attr('data-name')] = $(this).val();
        })
        $(this).closest('.input-group').find('textarea.mod-langinp').html(json_encode(json));
    });
});