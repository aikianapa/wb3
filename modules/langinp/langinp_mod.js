$(document).off('langinp-mod-js');
$(document).on('langinp-mod-js', async function() {

    $(document).undelegate('.dropdown.mod-langinp > .input-group-prepend', 'tap click');
    $(document).delegate('.dropdown.mod-langinp > .input-group-prepend', 'tap click', function() {
        $(this).parents('.input-group').children('.mod-langinp:input').trigger('change');
    });

    $(document).undelegate('.dropdown.mod-langinp', 'hide.bs.dropdown');
    $(document).delegate('.dropdown.mod-langinp', 'hide.bs.dropdown', function() {
        let form = $(this).find('.dropdown-menu');
        let json = {};
        $(form).find(':input').each(function() {
            json[$(this).attr('data-name')] = $(this).val();
        })
        $(this).closest('.input-group').find('textarea.mod-langinp-data').html(json_encode(json));
        $(this).closest('.input-group').find('.mod-langinp:input').val(json[wbapp._session.lang]);
        $(this).closest('.input-group').find('.mod-langinp:input').trigger('change');
    });

    $(document).undelegate('.dropdown.mod-langinp', 'show.bs.dropdown');
    $(document).delegate('.dropdown.mod-langinp', 'show.bs.dropdown', function() {
        let form = $(this).find('.dropdown-menu');
        let json = {};
        let width = $(this).closest('.input-group').width();
        $(form).width(width);
        try {
            json = json_decode($(this).closest('.input-group').find('textarea.mod-langinp-data').html());
        } catch (error) {
            null;
        }
        $(form).find(':input[data-name]').each(function() {
            if (json[$(this).attr('data-name')] !== undefined) {
                $(this).val(json[$(this).attr('data-name')]);
            }
        })
    });

    $(document).undelegate('.mod-langinp:input', 'change keyup');
    $(document).delegate('.mod-langinp:input', 'change keyup', function(e) {
        let form = $(this).parents('.input-group').find('.dropdown-menu');
        $(form).find('[data-name="' + wbapp._session.lang + '"]').val($(this).val());
        let json = {};
        $(form).find(':input').each(function() {
            json[$(this).attr('data-name')] = $(this).val();
        })
        $(this).closest('.input-group').find('textarea.mod-langinp-data').html(json_encode(json));
        $(this).closest('.input-group').find('textarea.mod-langinp-data').trigger('change');
    });
});