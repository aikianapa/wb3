var mod_autoinc = function(params, data) {
    $(params._event.currentTarget).text(data.result);
    $(params._event.currentTarget)
        .removeAttr('data-ajax')
        .removeAttr('auto');
};
mod_autoinc(params, data);