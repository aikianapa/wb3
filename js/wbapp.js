"use strict"
var wbapp = new Object();
wbapp.tmp = {};
var _tmpphp = false;
var _tmpjq = false;

setTimeout(async function() {
    wbapp.loader = true;
    let loader = document.getElementById("loader");
    typeof(loader) !== 'undefined' && loader !== null ? wbapp.delay = 20 : wbapp.delay = 10;

    var get_cookie = function(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
    let devmode = get_cookie('devmode')
    devmode ? devmode = '' : null;
    wbapp.devmode = devmode
    wbapp.evClick = 'tap click';
    wbapp.start();
}, 5);

wbapp.metadata = function () {
    let meta = $(`meta[name="wbmeta"]`)
    let data = meta.attr('content')
    meta.remove()
    data = data > '' ? JSON.parse(atob(data)) : {}
    wbapp._session = data.sess
    wbapp._settings = data.sett
    wbapp._route = data.rout
    return data;
}

wbapp.css = function (url) {
    return new Promise((resolve, reject) => {
        let link = document.createElement('link');
        link.type = 'text/css';
        link.rel = 'stylesheet';
        link.onload = () => resolve();
        link.onerror = () => reject();
        link.href = url;
        let res = false;
        if (!res && document.querySelector('head') !== undefined) { document.querySelector('head').append(link); res = true; }
        if (!res && document.querySelector('body') !== undefined) { document.querySelector('body').append(link); res = true; }
        if (!res && document.querySelector('html') !== undefined) { document.querySelector('html').append(link); res = true; }
    });
};

wbapp.start = async function() {
    if (typeof str_replace === 'undefined') {
        loadPhpjs();
        return;
    }
    if (typeof $ === 'undefined') {
        loadJquery();
        return;
    }
    wbapp.metadata()
    var data = {};
    wbapp.bind = {};
    wbapp.ui = {
        spinner_sm: '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>',
        spinner_sm_grow: '<span class="spinner-grow spinner-grow-sm" role="status"></span>'
    };
    wbapp.session();
    wbapp.settings();

    $.fn.submitForm = function() {
        return this.submit(function(ev) {
            ev.stopPropagation();
            ev.preventDefault();
            let form = this
            let error = false;
            let action = $(form).attr('action')
            let method = $(form).attr('method').toUpperCase()

            let data = new FormData(form);
            data.pathname = document.location.pathname;


            try {
                fetch(action, { method: method, body: data })
                    .then(function(response) {
                        if (response.ok) {
                            return response = response.json()
                        } else {
                            return { error: true }
                        }
                    })
                    .then(function(data) {
                        if (data.error) {
                            $(form).trigger('wb-submit-fail', data);
                        } else {
                            $(form).trigger('wb-submit-success', data);
                        }
                    })
            } catch (error) {
                $(form).trigger('wb-submit-fail', [error]);
            }
        })
    }


    $.fn.disableSelection = function() {
        return this
            .attr('unselectable', 'on')
            .css('user-select', 'none')
            .on('selectstart', false);
    };

    $.fn.verify = function() {
        var form = this;
        var res = true;
        var idx = 0;
        $(form).find("[required],[minlength],[min],[max],[name=password],[type=email]").each(function() {
            if ($(this).is('[name=password_check],[name=password-confirm]')) return;
            idx++;
            var label = $(this).attr("data-label");

            if (label == undefined || label == "") label = $(this).prev("label").text();
            if (label == undefined || label == "") label = $(this).next("label").text();
            if ((label == undefined || label == "") && $(this).attr("id") !== undefined) label = $(this).parents("form").find("label[for=" + $(this).attr("id") + "]").text();
            if (label == undefined || label == "") label = $(this).parents(".form-group").find("label").text();
            if (label == undefined || label == "") label = $(this).attr("placeholder");
            if (label == undefined || label == "") label = $(this).attr("name");

            $(this).data("idx", idx);

            if ($(this).is('[type=email]')) {
                if (($(this).val() > '' && !wbapp.check_email($(this).val())) || ($(this).val() == '' && $(this).prop('required'))) {
                    res = false;
                    $(this).data("error", wbapp._settings.sysmsg.email_correct);
                    wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
                } else {
                    wbapp.console("trigger: wb-verify-true [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-true", [this]);
                }
            } else if ($(this).is("[required]:not(select)") && $(this).val() == "") {
                res = false;
                $(this).data("error", wbapp._settings.sysmsg.required + ucfirst(label));
                wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
            } else if ($(this).is(":not([disabled],[readonly],[min],[max],[maxlength],[type=checkbox])")) {
                if ($(this).val() == "") {
                    res = false;
                    wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-false", [this]);
                } else {

                    wbapp.console("trigger: wb-verify-true [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-true", [this]);
                }
            }
            if ($(this).is("[required][type=checkbox]:not(:checked)")) {
                res = false;
                $(this).data("error", wbapp._settings.sysmsg.required + ucfirst(label));
                wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
            }
            if ($(this).is("[type=radio]") && $(this).is(":not(:checked)")) {
                res = false;
                var fld = $(this).attr("name");
                if (fld > "") {
                    $("[type=radio][name='" + fld + "']").each(function() {
                        if ($(this).is(":checked")) {
                            res = true;
                        }
                    });
                }
                if (!res) {
                    wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-false", [this]);
                }
            }
            if ($(this).is("[name=password]")) {
                if ($(form).find($(this).attr("name") + "_check").length) {
                    var pcheck = $(this).attr("name") + "_check";
                } else {
                    var pcheck = $(this).attr("name") + "-confirm";
                }
                let check = $(form).find("input[name=" + pcheck + "]");

                if (check.length) {
                    if ($(this).val() !== $(check).val()) {
                        res = false;

                        $(this).data("error", wbapp._settings.sysmsg.pass_match);
                        wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                        $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
                        $(form).trigger("wb-verify-false", [check, $(check).data("error")]);
                    } else {
                        $(form).trigger("wb-verify-true", [check]);
                    }
                }
            }
            if ($(this).is("[min]:not([readonly],[disabled])") && $(this).val() > "") {
                let min = $(this).attr("min") * 1;
                let minstr = $(this).val() * 1;
                if (minstr < min) {
                    res = false;
                    $(this).data("error", ucfirst(label) + " " + wbapp._settings.sysmsg.min_val + ": " + min);
                    wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
                }
            }

            if ($(this).is("[max]:not([readonly],[disabled])") && $(this).val() > "") {
                let max = $(this).attr("max") * 1;
                let maxstr = $(this).val() * 1;
                if (maxstr > max) {
                    res = false;
                    $(this).data("error", ucfirst(label) + " " + wbapp._settings.sysmsg.max_val + ": " + max);
                    wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
                }
            }

            if ($(this).is("[minlength]:not([readonly],[disabled])") && $(this).val() > "") {
                let minlen = $(this).attr("minlength") * 1;
                let lenstr = strlen($(this).val());
                if (lenstr < minlen) {
                    res = false;
                    $(this).data("error", ucfirst(label) + " " + wbapp._settings.sysmsg.min_length + ": " + minlen);
                    wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
                }
            }

            if ($(this).is('select[required]')) {
                let val = $(this).find('option:selected').attr('value');
                if (val == undefined || val == '') {
                    res = false;
                    $(this).data("error", wbapp._settings.sysmsg.required + ucfirst(label));
                    wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
                    $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
                }
            }


            if ($(this).is("button")) {
                if (
                    ($(this).attr("value") !== undefined && $(this).val() == "") ||
                    ($(this).attr("value") == undefined && $(this).html() == "")
                ) {
                    res = false;
                }
            }


        });
        if (res == true) {
            wbapp.trigger('wb-verify-success', form, [form]);
        }
        if (res == false) {
            wbapp.trigger('wb-verify-fail', form, [form]);
        }
        return res;
    }

    $.fn.outer = function(s) {
        return s ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
    };

    $.fn.runScripts = async function() {
        $(this).find("script").each(function() {
            var type = $(this).attr("type");
            if (type !== "text/locale" && type !== "text/template") {
                eval($(this).text());
                if ($(this).attr("removable") !== undefined) $(this).remove();
            }
        });
    }

    $.fn.serializeJson = function(data = {}) {
        var form = this;
        $(form).find("form [name], .wb-unsaved[name], .wb-tree-item [name]").each(function() {
            $(this).attr("wb-tmp-name", $(this).attr("name"));
            $(this).removeAttr("name");
        });
        var branch = $(form).serializeArray();

        $(branch).each(function(i, val) {
            let value = val["value"];
            let name = val["name"];
            data[name] = value;
            let $textarea = $(form).find("textarea[name='" + name + "']");
            if ($textarea.length && $textarea.is("[type=json]")) {
                let _val = $textarea.val();
                let _text = $textarea.text();
                _val == 'null' ? data[name] = _text : data[name] = _val;
                if (in_array(data[name], ['null', '', '{}', '[]'])) {
                    data[name] = '';
                } else {
                    try {
                        data[name] = json_decode(data[name], true);
                    } catch (error) {
                        wbapp.console('Unknown error!');
                    }

                }
            } else if ($textarea.length) {
                if ($(form).parents(".treeData").length) {
                    data[name] = htmlentities(data[name]);
                    data[name] = str_replace('&quot;', '/"', data[name]);
                    data[name] = str_replace('&amp;quot;', '"', data[name]);
                } else if ($textarea.data('iconv')) {
                    value = data[name];
                    eval(`data[name] = ${$textarea.data('iconv')}(value)`);
                } else {
                    let value = $textarea.val();
                    let text = $textarea.text();
                    if (value == 'null') {
                        data[name] = text;
                    } else { data[name] = value; }
                }
            }
        });

        let sel = $(form).find('select[name]:not([multiple])');
        $.each(sel, function() {
            data[this.name] = $(this).val();
        });

        let multi = $(form).find('select[name][multiple]');
        $.each(multi, function() {
            data[this.name] = $(this).val();
        });

        let attaches = $(form).find('input[name][type=file]');
        let reader = new FileReader();
        $.each(attaches, function() {
            let file = $(this)[0].files[0];
            if (file) {
                let that = this;
                reader.readAsDataURL(file);
                reader.onload = function() {
                    data[that.name] = reader.result.toString(); //base64encoded string
                };
            }
        });

        var check = $(form).find('input[name][type=checkbox]');
        // fix unchecked values
        $.each(check, function() {
            data[this.name] = "";
            if (this.checked) data[this.name] = "on";
        });

        var check = $(form).find('input[name][type=radio]');
        // fix unchecked values
        $.each(check, function() {
            if (this.checked) data[this.name] = $(this).attr('value');
        });

        $(form).find("form [wb-tmp-name], .wb-unsaved [wb-tmp-name], .wb-tree-item [wb-tmp-name]").each(function() {
            $(this).attr("name", $(this).attr("wb-tmp-name"));
            $(this).removeAttr("wb-tmp-name");
        });
        // fix dot notation
        let obj = {}
        $.each(data, function(name, value) {
            if (strpos(name, ".")) {
                let chunks = explode(".", name);
                let idx = ""
                $.each(chunks, function(i, key) {
                    if (i < chunks.length) {
                        idx == "" ? idx = key : idx += "." + key
                        eval(`if (obj.${idx} == undefined) obj.${idx} = {}`);
                    }
                })
                eval(`obj['${name}'] = value`);
            } else {
                eval(`obj['${name}'] = value`);
            }
        })
        return obj;
    }

    $.fn.jsonVal = function(data = undefined) {
        if (strtolower($(this).attr("type")) !== "json") {
            return $(this).val();
        }
        if (data == undefined) {
            data = $(this).val();
            try {
                data = json_decode(data);
            } catch (error) {
                data = {};
            }
            return data;
        } else {
            if (data == "") {
                data = {};
            } else {
                data = json_encode(data);
            }
            if ($(this).is("textarea")) $(this).html(data);
            $(this).val(data).trigger("change");
        }
    }

    setTimeout(function() {
        let load = [];
        if (typeof Vue == 'undefined') load.push("/engine/js/vue.min.js");
        if (typeof topbar == 'undefined') load.push("/engine/js/topbar.min.js");
        if (typeof jQuery.ui == 'undefined') load.push("/engine/js/jquery-ui.min.js");
        if (typeof Ractive == 'undefined') load.push(`/engine/js/ractive.js`);
        load.push(`/engine/js/jquery.tap.js`);
        if (typeof lazyload == 'undefined') load.push(`/engine/js/lazyload.js`);


        wbapp.loadScripts(load, "wbapp-go", async function() {
            Ractive.DEBUG = false;
            wbapp.eventsInit();
            wbapp.wbappScripts();
            wbapp.tplInit();
            wbapp.ajaxAuto();
            wbapp.lazyload();
            wbapp.modalsInit();
            wbapp.fileinpInit();
            wbapp.wbappScripts();
            //$(document).scrollTop(0);
            $(document).on("wb-ajax-done", async function() {
                wbapp.console("Trigger: wb-ajax-done");
                if (wbapp !== undefined) {
                    wbapp.tplInit();
                    wbapp.wbappScripts();
                    //wbapp.pluginsInit();
                    wbapp.lazyload();
                }
                if ($(".modal.show:not(:visible),.modal[data-show=true]:not(:visible)").length) $(".modal.show:not(:visible),.modal[data-show=true]:not(:visible)").modal("show");
                if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
            });
            wbapp.trigger("wb-ready");
        });
    }, wbapp.delay);

}

wbapp.ractive = function(target = 'body', tpl = 'empty', data = []) {
    return new Ractive({
        'target': target,
        'template': tpl,
        'data': data
    });
}

wbapp.fileinpInit = function() {
    var getBase64 = function(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => {
                //let encoded = reader.result.toString().replace(/^data:(.*,)?/, '');
                let encoded = reader.result.toString();
                if ((encoded.length % 4) > 0) {
                    encoded += '='.repeat(4 - (encoded.length % 4));
                }
                resolve(encoded);
            };
            reader.onerror = error => reject(error);
        });
    }
    $(document).delegate('input[type=file][data-base64]', 'change', async function() {
        if ($(this).val() == '') {
            $(this).data('base64', '');
        } else {
            $(this).data('base64', await getBase64($(this).prop('files')[0]));
        }
    })
}

wbapp.confirm = function(title = null, text = null, options = null) {
    /*
    wbapp.confirm()
    .on('confirm', function () {
        alert(1);
    })
    .on('cancel', function () {
        alert(0)
    });
    */
    let modal
    if (wbapp.confirmModal == undefined) {
        modal = wbapp.getForm('common', 'confirm', { data: { 'confirm': true } });
        wbapp.confirmModal = modal.result
    } else {
        modal = wbapp.confirmModal
    }
    let $modal = $(wbapp.confirmModal);
    let confirm = false;
    title !== null ? title = $modal.find('.modal-title').text(title) : null;
    text !== null ? text = $modal.find('.modal-body').text(text) : null;
    $modal.modal();
    $modal.undelegate('.btn.confirm', wbapp.evClick);
    $modal.delegate('.btn.confirm', wbapp.evClick, function() {
        confirm = true;
        $modal.trigger('confirm').modal('hide');
    });
    $modal.on('hide.bs.modal', function() {
        confirm == false ? $modal.trigger('cancel') : null;
    })
    return $modal;
}

wbapp.lazyload = async function() {
    /*
    $("[data-src]:not([src])").each(function() {
        let link = document.createElement('link');
        link.rel = "preload";
        link.as = "image";
        link.href = $(this).attr('data-src');
        document.head.appendChild(link);
    });
    */
    $("[data-src]:not([src])").attr('loading', 'lazy')
    $("[data-src]:not([src])").lazyload();
}

wbapp.eventsInit = async function() {
    $(document).undelegate("[data-ajax]:not(input,select)", wbapp.evClick);
    $(document).delegate("[data-ajax]:not(input,select)", wbapp.evClick, async function(e, tid) {
        if (!$(this).is("input,select")) {
            let params = wbapp.parseAttr($(this).attr("data-ajax"));
            if (params.url == undefined && typeof params == 'string') {
                // ajax string only
                params.url = $(this).attr("data-ajax");
                if ($(this).parents('form').length) {
                    let id = wbapp.newId('_', 'ax');
                    if ($(this).parents('form').attr('id') == undefined) {
                        $(this).parents('form').attr('id', id);
                    }
                    params.form = 'form#' + $(this).parents('form').attr('id');
                    if (!$(params.form).verify()) {
                        return false;
                    }
                }
            }
            if ($(this).is('[data-toggle=tooltip]')) { $(this).tooltip('hide'); } // fix tooltips
            params._event = e;
            if (tid !== undefined) params._tid = tid;
            wbapp.ajax(params);
            wbapp.trigger("data-ajax", params);
            let href = $(this).attr('href');
            if (href !== undefined && href.substr(0, 1) == '#') {
                document.location.anchor = $(this).attr('href');
            }
        }
    })

    $(document).undelegate("input[data-ajax],select[data-ajax]", "change")
    $(document).delegate("input[data-ajax],select[data-ajax]", "change", async function(e, tid) {
        e.preventDefault();
        let search = $(this).attr("data-ajax");
        search = str_replace('$value', $(this).val(), search);
        let params = wbapp.parseAttr(search);
        params._event = e;
        if (tid !== undefined) params._tid = tid;
        wbapp.ajax(params);
        return false;
    })

    $(document).undelegate("input[type=search][data-ajax]", "keyup");
    $(document).delegate("input[type=search][data-ajax]", "keyup", function() {
        var minlen = 0;
        var that = this;
        var val = $(this).val();
        that.waitajax = false;
        if ($(this).attr("minlength")) minlen = $(this).attr("minlength") * 1;
        if (that.waitajax == false && val.length >= minlen) {
            that.waitajax = true;
            $(this).trigger("change");
            setTimeout(function() {
                that.waitajax = false;
            }, 500);
        }
    })

    $(document).undelegate("input[type=checkbox]", "click");
    $(document).delegate("input[type=checkbox]", "click", async function() {
        if ($(this).prop("checked") == false) {
            $(this).removeAttr("checked");
        } else {
            $(this).prop("checked", true);
            $(this).attr("checked", true);
        }
    });

    $(document).undelegate("[rows=auto]", "keydown keyup focus");
    $(document).delegate("[rows=auto]", "keydown keyup focus", function() {
        this.style.overflow = "hidden";
        this.style.height = "1px";
        this.style.height = (this.scrollHeight) + "px";
    });

    $(document).undelegate("[rows=auto]", "focusout")
    $(document).delegate("[rows=auto]", "focusout", async function() {
        this.style.overflow = "hidden";
        this.style.height = "auto";
    });


}

wbapp.ajaxAuto = async function(func = null) {
    $(document).find("[data-ajax][auto]").each(async function() {
        $(this).trigger("click");
        if ($(this).attr('once') !== undefined) $(this).removeAttr('data-ajax').removeAttr('once');
        $(this).removeAttr("auto");
    });
}

wbapp.auth = function(form, mode = 'signin') {
    if (!$(form).verify()) return;
    let data = $(form).serializeJson();

    let dislink = function() {
        $(form).find('a:not([disabled]),button:not([disabled]),[data-ajax]:not([disabled])').addClass('wb-auth-disabled').attr('disabled', true);
        $(form).addClass('cursor-wait');
    }

    let enlink = function() {
        $(form).find('.wb-auth-disabled').removeClass('wb-auth-disabled').removeAttr('disabled');
        $(form).removeClass('cursor-wait');
    }

    let signin = function() {
        if ($(form).attr("action") !== undefined) var url = $(form).attr("action");
        else var url = "/api/auth/email";
        wbapp.post(url, data, function(res) {
            if (res.login) {
                wbapp.console("Trigger: wb-signin-success");
                $(document).trigger('wb-signin-success');
                if (res.redirect) document.location.href = res.redirect;
            } else {
                wbapp.console("Trigger: wb-signin-error");
                $(document).trigger('wb-signin-error');
                $(form).find('.is-valid').removeClass('is-valid');
                $(form)[0].reset();
                $(form).find('.signin-error').removeClass('d-none');
            }
            enlink();
        });
    }

    let signup = function() {
        if ($(form).attr("action") !== undefined) var url = $(form).attr("action");
        else var url = "/api/auth/signup";
        wbapp.post(url, data, function(res) {
            if (res.signup) {
                wbapp.console("Trigger: wb-signup-success");
                $(document).trigger('wb-signup-success');
                $(form).find('.signup-success').removeClass('d-none');
                $(form).find(".signup-error, .signup-form").remove();
                if (res.redirect) document.location.href = res.redirect;
            } else {
                $(form).find('.is-valid').removeClass('is-valid');
                wbapp.console("Trigger: wb-signup-error");
                $(document).trigger('wb-signup-error');
                $(form).find('.signup-error').removeClass('d-none');
            }
            enlink();
        });
    }

    let recover = function() {
        if ($(form).attr("action") !== undefined) var url = $(form).attr("action");
        else var url = "/api/auth/recover";
        data.text = json_encode($(form).find('.recover-text').html());

        wbapp.post(url, data, function(res) {
            if (res.recover) {
                wbapp.console("Trigger: wb-signrc-success");
                $(document).trigger('wb-signrc-success');
                $(form).find(".recover-success").removeClass('d-none');
                $(form).find(".recover-error, .recover-form").remove();
            } else {
                $(form).find('.is-valid').removeClass('is-valid');
                wbapp.console("Trigger: wb-signrc-error");
                $(document).trigger('wb-signrc-error');
                $(form).find(".recover-error").removeClass('d-none');
            }
            enlink();
        });
    }

    dislink();

    eval(mode)();
}

wbapp.alive = async function() {
    $.post("/ajax/alive", {}, async function(data) {
        if (data.result == false || data.result == undefined) {
            wbapp.console("Trigger: session_close");
            $(document).trigger("session_close");
            clearInterval(alive);
        }
    });
}

wbapp.store = function(storage = null, key, value = undefined, binds = true) {
    if (storage == null) storage = localStorage
    key = str_replace('-', '__', key)

    function getKey(list) {
        key = "";
        $(list).each(function(i, k) {
            if (k.substr(0, 1) * 1 > -1) {
                key += `['${k}']`
            } else {
                if (i > 0) key += '.'
                key += k
            }
        })
        return key
    }

    if (value === undefined) {
        // get data
        let list = key.split(".");
        var res;
        var data = storage.getItem(list[0]);
        if (data !== null) {
            data = JSON.parse(data);
        } else {
            data = {}
        }

        if (list.length) {
            key = getKey(list);
            try {
                eval(`res = data.${key}`);
                if (typeof res == 'object') res = Object.assign({}, res);
                return res;
            } catch (err) {
                return undefined
            }
        }
        return data;
    } else {
        // set data
        var path, branch, type;
        var list = key.split(".");
        var last = list.length;
        var lastkey = list[last - 1];
        $(list).each(function(i, k) {
            if (i == 0) {
                key = k;
                data = storage.getItem(key);
                if (i + 1 !== last && data == null) {
                    data = {}
                } else {
                    try {
                        data = JSON.parse(data);
                    } catch (err) {
                        data = {}
                    }
                }
            } else {
                if (k.substr(0, 1) * 1 > -1) {
                    key += `['${k}']`
                } else {
                    if (i > 0) key += '.'
                    key += k
                }
            }
            typeof data == 'object' ? null : data = {}
            try {
                eval(`branch = data.${key}`);
                if (typeof branch == 'object') branch = Object.assign({}, branch);
                if (i + 1 < last && typeof branch !== "object") eval(`data.${key} = {}`);
            } catch (err) {
                data ? null : data = {};
                eval(`data.${key} = {}`);
            }
        })
        var tmpValue = value;
        if (value === null) {
            eval(`delete data.${key}`);
        } else if (value !== {}) {
            if (typeof value == 'string') {
                eval(`data.${key} = value`);
            } else {
                eval(`tmpValue = Object.assign({}, value)`);
                Object.entries(tmpValue).length == 0 ? null : value = tmpValue;
                if (typeof value == 'object') value = Object.assign({}, value);
                eval(`data.${key} = value`);
            }
        }
        storage.setItem(list[0], json_encode(data));

        let checkBind = function(bind, key) {
            if (bind == key) return true;
            if (key.substr(0, bind.length) == bind) return true;
            return false;
        }

        if (binds == true) {
            $.each(wbapp.template, function(i, tpl) {
                if (tpl.params.bind !== undefined && tpl.params.bind !== null && checkBind(tpl.params.bind, key) &&
                    tpl.params.render !== undefined && tpl.params.render == 'client') {
                    wbapp.render(tpl.params.target);
                } else if (tpl.params._params !== undefined && tpl.params._params.bind !== undefined &&
                    checkBind(tpl.params._params.bind, key) && tpl.params.render == 'server') {
                    wbapp.render(tpl.params.target);
                }
            });
            $(document).trigger("bind", { key: key, data: value });
            $(document).trigger("bind-" + key, value);
            wbapp.console("Trigger: bind [" + key + "]");
        }
        return data;
    }
}

wbapp.storage = function(key, value = undefined, binds = true) {
    return wbapp.store(localStorage, key, value, binds)
}

wbapp.data = function(key, value = undefined, binds = true) {
    return wbapp.store(sessionStorage, key, value, binds)
}

wbapp.save = async function(obj, params, func = null) {
    wbapp.console("Trigger: wb-save-start");
    $(obj).trigger("wb-save-start", params);
    let that = this;
    let data, form, result;
    let method = "POST";
    params.form !== undefined ? form = $(params.form) : form = $(obj).parents("form");

    if ($(form).length && !$(form).verify()) {
        $(obj).trigger("wb-save-error", {
            params: params
        });
        return false;
    }

    if ($(form).attr("method") !== undefined) method = $(form).attr("method");
    if ($(form).parents('.modal.saveclose').length) {
        params.dismiss = $(form).parents('.modal.saveclose').attr('id');
    }

    setTimeout(function() {
        // Задержка для ожидания обработки возможных плагинов
        data = wbapp.objByForm(form);
        if (data._idx) delete data._idx;

        //if ($(obj).is("input,select,textarea,button,img,a") && params.table && (params.id || params.item)) {
        if (params.table && (params.id || params.item)) {
            let fld = $(obj).attr("name");
            let value = $(obj).val();
            let id;
            if (params.field == undefined && fld > '') params.field = fld;
            if ($(obj).is(":checkbox") && $(obj).prop("checked")) value = "on";
            if ($(obj).is(":checkbox") && !$(obj).prop("checked")) value = "";

            if (params.id) {
                id = params.id;
            } else if (params.item) id = params.item;

            if (data.id !== undefined && data.id > '') id = data.id;

            if (fld !== undefined) {
                data = {};
                data['id'] = id;
                eval(`data.${fld} = value;`);
            } else {
                let tmpId = 'tmp.' + wbapp.newId();
                wbapp.storage(tmpId, {
                    'id': id
                }, false);
                if (params.field !== undefined) {
                    wbapp.storage(tmpId + '.' + params.field, data, false);
                } else {
                    data['id'] = id;
                    wbapp.storage(tmpId, data, false);
                }
                data = wbapp.storage(tmpId);
                wbapp.storage(tmpId, null);
            }
            data['id'] = id;
            if (params.url == undefined) params.url = `/api/save/${params.table}`;
        } else if (params.field !== undefined) {
            wbapp.storage('tmp' + '.' + params.table + '.' + params.field, data, false);
            data = wbapp.storage('tmp' + '.' + params.table);
        }
        if ($(obj).data('saved-id') !== undefined) {
            data['id'] = $(obj).data('saved-id');
        }
        wbapp.post(params.url, data, function(data) {
            if (data.error == true) {
                $(obj).trigger("wb-save-error", {
                    params: params,
                    data: data,
                });
                return null;
            }

            if (params.callback) eval('params = ' + params.callback + '(params,data)');

            if (params.data && params.error !== true) {
                var update = [];
                var dataname;
                $.each(data, function(key, value) {
                    update[key] = value
                });
                eval('var checknew = (typeof ' + params.data + ');');
                if (checknew == "undefined") {
                    eval(`dataname = str_replace("['` + data._id + `']","","` + params.data + `");`);
                    eval(dataname + '.push(update)');
                } else {
                    eval(params.data + ' = update;');
                }
            }

            if (params.dismiss && params.error !== true) $("#" + params.dismiss).modal("hide");
            wbapp.console('Update by tpl');
            wbapp.updateView(params, data);
            if (data._id !== undefined) $(obj).data('saved-id', data._id);

            wbapp.console("Trigger: wb-save-done");
            $(obj).trigger("wb-save-done", {
                params: params,
                data: data,
            });
            wbapp.console("Trigger: wb-form-save " + params.form);
            $(params.form).trigger("wb-form-save", {
                params: params,
                data: data,
            });

            if (func !== null) return func(data);

        });
    }, 50);
}

wbapp.updateView = function(params = {}, data = {}) {
    console.log('Update view');
    $.each(wbapp.template, function(i, tpl) {
        if (tpl.params.render == undefined || tpl.params.render !== 'client') tpl.params.render = 'server';
        if (tpl.params.render == 'client') {
            // client-side update
            if (tpl.params._params && tpl.params._params.bind) tpl.params = tpl.params._params;
            if (tpl.params.bind && (tpl.params.bind == params.bind || strpos(' ' + tpl.params.bind, params.update)) == 1) {
                if (params.bind) wbapp.storage(params.bind, data);
                if (params.update) wbapp.storageUpdate(params.update, data);
            }
        } else {
            // server-side update
            let prms = Object.assign({}, tpl.params);
            let post = null;
            prms.route == undefined ? prms.route = [] : null;
            if (prms.bind !== undefined && prms.update == undefined) prms.update = prms.bind;
            if (prms._params !== undefined && prms._params.bind !== undefined) prms.update = prms._params.bind;
            if (prms._params !== undefined && prms._params.update !== undefined) prms.update = prms._params.update;
            if (prms.route !== undefined && prms.route._post !== undefined) post = prms.route._post;
            if (prms.url == undefined && prms.route.url !== undefined) prms.url = prms.route.url;
            if (params.update == prms.update) {
                let target = prms.target;
                if (post && prms.url !== undefined) {
                    fetch(prms.url, {
                            method: 'POST',
                            body: post
                        })
                        .then((resp) => resp.text())
                        .then(function(res) {
                            let html = $(res).find(target).html();
                            $(document).find(target).html(html);
                            wbapp.refresh();
                        })
                } else {
                    wbapp.renderServer(prms, data);
                }
            }

        }
    })
}

wbapp.updateInputs = function() {
    $(document).find(":checkbox").each(async function() {
        if ($(this).attr("value") == "on") {
            $(this).attr("checked", true).prop("checked", true);
        } else {
            $(this).attr("checked", false).prop("checked", false);
        }
    })
}

wbapp.wbappScripts = async function() {
    var done = [];
    $(document).find("script[type=wbapp],script[wbapp],script[wb-app]").each(function() {
        if (this.done !== undefined) return;
        this.done = true;
        let src = null;
        if ($(this).is('[src]')) {
            src = $(this).attr('src');
        } else if ($(this).is('[wbapp]')) {
            src = $(this).attr('wbapp');
        } else if ($(this).is('[wb-app]')) {
            src = $(this).attr('wb-app');
        }
        var that = this;
        if (src !== null && src > '') {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', src, true);
            xhr.onload = function() {
                //eval(xhr.responseText);
                $(that).after('<script type="text/javascript">' + xhr.responseText + '</script>').remove();
            };
            xhr.send();
        } else {
            var script = $(this).text();
            var hash = md5(script);
            if (!in_array(hash, done)) {
                $(that).after('<script type="text/javascript">' + script + '</script>').remove();
                //eval(script);
                done.push(hash);
            }
        }
        if ($(this).attr("remove") !== undefined) $(this).remove();
        if ($(this).attr("removable") !== undefined) $(this).remove();
    });
}

wbapp.checkJson = function(queryString) {
    queryString = str_replace(" ", "", queryString.trim());
    if (queryString == "{}") return true;
    if (queryString.substr(0, 1) == "{" && queryString.substr(-1) == "}" && strpos(queryString, ":")) return true;
    return false;
}

wbapp.parseAttr = function(queryString = null) {
    if (queryString == null) queryString = $(this).attr("wb-data");
    queryString = str_replace("'", '"', queryString);
    queryString = str_replace("&amp;", '&', queryString);
    var params = {};
    if (wbapp.checkJson(queryString)) {
        params = JSON.parse(queryString);
    } else {
        parse_str(queryString, params);
    }
    $(this).data("wb-data", params);
    return params;
}

wbapp.post = async function(url, data = {}, func = null) {
    if (is_string(data)) {
        data += '&__token=' + wbapp._session.token;
    } else {
        try { data.__token = wbapp._session.token; } catch (error) { null }
    }
    wbapp.loading();
    $.post(url, data).then(function(res) {
        wbapp.unloading();
        if (func !== null) return func(res);
    })
}

wbapp.get = async function(url, data = {}, func = null) {
    if (is_string(data)) {
        data += '&__token=' + wbapp._session.token;
    } else {
        try { data.__token = wbapp._session.token; } catch (error) { null }
    }
    wbapp.loading();
    $.get(url, data)
        .then(function(data) {
            wbapp.unloading();
            if (func !== null) return func(data);
        })
        .fail(function(data) {
            wbapp.unloading();
            if (func !== null) return func(false);
        })
}

wbapp.ajax = async function(params, func = null) {
    if (!params.url && !params.tpl && !params.target) return;
    let opts = Object.assign({}, params);
    let token;
    delete opts._event;

    if (params.form !== undefined) {
        $(params.form).trigger("wb-ajax-start", params);
    } else if (params.target !== undefined) {
        $(params.target).trigger("wb-ajax-start", params);
    }
    wbapp.trigger("wb-ajax-start", params);

    if (params.request_type == 'remove_item') {
        wbapp.post(params.url, opts, async function(data) {
            if (data._removed !== undefined && data._removed == true && params.update !== undefined) {
                $.each(wbapp.template, function(i, tpl) {
                    let rend = false;
                    if (tpl.params !== undefined && tpl.params.bind !== undefined && tpl.params.bind == params.update) rend = true;
                    if (tpl.params !== undefined && tpl.params._params !== undefined && tpl.params._params.bind !== undefined && tpl.params._params.bind == params.update) rend = true;
                    if (rend) wbapp.renderServer(tpl.params);
                });
            }
        });
    } else if (params.url !== undefined) {
        if (params.form !== undefined) {
            params.formdata = wbapp.objByForm(params.form);
            params.formflds = {};
            $(params.form).find('[name]').each(function() {
                params.formflds[$(this).attr('name')] = $(this).attr('name');
                if ($(this).attr('name')) {
                    if ($(this).attr('data-label') > '') {
                        params.formflds[$(this).attr('name')] = $(this).attr('data-label');
                    } else if ($(this).closest('.form-group').find('label').length) {
                        params.formflds[$(this).attr('name')] = $(this).closest('.form-group').find('label').text();
                    } else if ($(this).prev("label").length) {
                        params.formflds[$(this).attr('name')] = $(this).prev("label").text();
                    } else if ($(this).attr('placeholder') > '') {
                        params.formflds[$(this).attr('name')] = $(this).attr('placeholder');
                    }
                }
            })
        }
        let opts = Object.assign({}, params);
        delete opts._event;

        if (opts._tid !== undefined && opts._tid > '') {
            delete opts.data; // добавил для очистки фильтра
            params = wbapp.tpl(opts._tid).params;
        }

        wbapp.loading();
        if (params.filter !== undefined) opts.filter = params.filter;
        wbapp.post(params.url, opts, async function(data) {
            wbapp.unloading();
            if (count(data) == 2 && data.error !== undefined && data.callback !== undefined) {
                eval(data.callback + '(params,data)');
                if (func !== null) return func(params, data);
            } else if (data.callback !== undefined) {
                eval(data.callback);
            }

            if (params.target && params.target > '#') {
                if ($(data).is(params.target)) {
                    $(document).find(params.target).html($(data).html());
                } else {
                    $(document).find(params.target).html($(data).find(params.target + ':first').html());
                }
            }

            if (params.source && params.source > '') data = $(data).find(params.source).html();
            if (params.html) $(document).find(params.html).html(data);
            if (params.append) $(document).find(params.append).append(data);
            if (params.prepend) $(document).find(params.prepend).prepend(data);
            if (params.replace) $(document).find(params.replace).replaceWith(data);
            //if (params.form) wbapp.formByObj(params.form,data);
            if (params.render == 'client') {
                if (params.bind && typeof data == "object") wbapp.storage(params.bind, data);
                if (params.update && typeof data == "object") wbapp.storageUpdate(params.update, data);
            }
            if (params._trigger !== undefined && params._trigger == "remove") eval('delete ' + params.data); // ???
            if (params.dismiss && params.error !== true) $("#" + params.dismiss).modal("hide");
            if (params.render !== undefined && params.render == 'client') wbapp.renderClient(params, data);
            if (params._event !== undefined && $(params._event.target).parent().is(":input")) {
                // $inp = $(params._event.target).parent();
                // тут нужна обработка значений на клиенте
            }
            if (params.update !== undefined) wbapp.updateView(params, data);
            wbapp.refresh(data);
            if (data.html !== undefined && params.target !== undefined) {
                if (params._params !== undefined && params._params.more !== undefined) {
                    $(document).find(params.target).append(data.html);
                } else {
                    $(document).find(params.target).html(data.html);
                }
            }
            /*
            if (params.render == 'client') {
                let res = $(data).find(params.target).html();
                $(document).find(params.target).html(res);
            } else if (params.render == undefined || params.render == 'server') {
                if (data.html !== undefined && params.target !== undefined) {
                    if (params._params !== undefined && params._params.more !== undefined) {
                        $(document).find(params.target).append(data.html);
                    } else {
                        $(document).find(params.target).html(data.html);
                    }

                } else if (params.update !== undefined || params.update !== undefined) {
                    $.each(wbapp.template, function(i, tpl) {
                        if (tpl.params && tpl.params.bind && tpl.params.bind == params.update) {
                            if (typeof tpl.params == 'object' && typeof data == 'object') wbapp.renderServer(tpl.params, data);
                        } else if (tpl.params && tpl.params._params && tpl.params._params.bind && tpl.params._params.bind == params.update) {
                            if (typeof tpl.params._params == 'object' && typeof data == 'object') wbapp.renderServer(tpl.params._params, data);
                        }
                    })
                }

                $(document).find(params.target).children('template').remove();
            }
            */
            if (params.callback !== undefined) eval(params.callback + '(params,data)');
            wbapp.setPag(params.target, data);
            //wbapp.console("Trigger: wb-ajax-done");
            if (data.result == undefined) params['data'] = data;
            if (params.form !== undefined) {
                $(params.form).trigger("wb-ajax-done", params);
            } else if (params.target !== undefined) {
                $(params.target).trigger("wb-ajax-done", params);
            } else {
                $(document).trigger("wb-ajax-done", params);
            }
            setTimeout(async function() {
                let showmod = $(document).find(".modal.show:not(:visible)");
                if (showmod.length) showmod.removeClass("show").modal('show');
            }, 50);
            if (func !== null) return func(params, data);
        });
    } else if (params.target !== undefined) {
        if (wbapp.template[params.target] !== undefined) {
            var target = wbapp.template[params.target].params;
        }
        if (!target) {
            wbapp.console("Template not found: " + params.target);
            return;
        } else {
            target.target = params.target;
            if ($(target.target)[0].filter == undefined) {
                try {
                    $(target.target)[0].filter = wbapp.template[target.target].params._params.filter;
                } catch (error) {
                    $(target.target)[0].filter = {}
                }
            }

            target.filter = $(target.target)[0].filter;
            if (target._params == undefined) target._params = {};
            let clearval = null;
            $.each(params, function(key, val) {
                if (key == 'filter') {
                    if (val == 'clear') {
                        target.filter = {};
                    } else {
                        target.filter = val;
                    }
                }
                if (key == 'filter_clear') {
                    clearval = val;
                }
                if (key == 'filter_remove') {
                    if (typeof val == "string") {
                        val = val.trim().split(' ');
                        delete target.filter[val];
                    }
                    if (typeof val == "object") {
                        $.each(val, function(i, v) {
                            delete target.filter[v];
                        })
                    }

                }
                if (key == 'filter_add') {
                    $.each(val, function(k, v) {
                        target.filter[k] = v;
                    })
                }
            });
            if (clearval !== null) {
                $(clearval).each(function(k, v) {
                    $(target.filter).each(function(tk, tv) {
                        json_encode(tv) == json_encode(v) ? target.filter = {} : null;
                    })
                })
            }
            $(target.target)[0].filter = target.filter;
            if (target._params && target._params.page !== undefined) target._params.page = 1
            if (target._params && target._params.pages !== undefined) delete target._params.pages
            if (target._params && target._params.count !== undefined) delete target._params.count
            if (target.tpl !== undefined) target._params.tpl = target.tpl;
            if (target._tid == undefined) target._tid = params.target; // чтобы срабатывал вариант ответа с json
            if (target.url == undefined && target.route !== undefined && target.route.uri !== undefined) target.url = target.route.uri;
            params.clear !== undefined && params.clear == "true" ? $(document).find(target._tid).html('') : null;

            wbapp.template[params.target].params = target;
            if (target._params == undefined || target._params.length == 0) { void(0); } else {

                if (target.filter) {
                    wbapp.template[params.target].params._params.filter = target.filter;
                }


                if (wbapp.tmp.ajax_params == undefined || wbapp.tmp.ajax_params !== target) {
                    wbapp.tmp.ajax_params = target;
                    wbapp.ajax(target, function() {
                        delete wbapp.tmp.ajax_params;
                        func
                    }); // только если переданы предыдущие параметры
                }
            }
        }
    }
}

wbapp.refresh = function(data = null) {
    setTimeout(async function() {
        wbapp.wbappScripts();
        wbapp.tplInit();
        wbapp.ajaxAuto();
        wbapp.lazyload();
        wbapp.modalsInit();
        //        wbapp.fileinpInit();
        wbapp.wbappScripts();
        if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
        if (data !== null) paginationfix(data);
    }, 1)
    let paginationfix = async function(data) {
        if (data.pag == undefined || data.params == undefined) return;
        if (data.params.more == undefined || data.params.more < 'more') return;
        if (data.params.target == undefined || data.params.target < '#') return;
        let $pag = $(document).find(`.pagination[data-tpl="${data.params.target}"]`)
        if (!$pag) return;
        if (data.params.page >= data.params.pages) data.pag = '';
        if (data.pag > '') {
            data.pag = $(data.pag).find(`.pagination[data-tpl="${data.params.target}"]`).html();
        }
        $pag.html(data.pag);
    }
}

wbapp.renderFilter = function(tid, filter) {
    let tpl = wbapp.tpl(tid);
    tpl.params.filter = filter;
    if (tpl.params._params !== undefined) tpl.params._params.filter = filter;
    wbapp.tpl(tid, tpl);
    wbapp.data('wbapp.filter.' + tid.substr(1), filter);
    wbapp.render(tid);
}

wbapp.storageUpdate = async function(key, data) {
    var store = wbapp.storage(key);
    if (!store) wbapp.storage(key, {});
    if (store._id == undefined && (store.result !== undefined || store.params !== undefined) && data !== null && data._id !== undefined) {
        if (data._removed !== undefined && data._removed == true) {
            if (store.params !== undefined && store.params.render == 'server') {
                wbapp.renderServer(store.params);
            } else {
                try {
                    delete store.result[data._id]
                } catch (err) {
                    wbapp.console('Not removed');
                }
            }
        } else if (data._renamed !== undefined && data._renamed == true) {
            /// rename
        } else {
            try {
                store.result[data._id] = data
            } catch (err) {
                wbapp.console('Error: wbapp.storageUpdate()');
            }
        }
        wbapp.storage(key, store);
    } else {
        wbapp.storage(key, data);
    }
}


wbapp.loading = async function() {
    if (wbapp.loader !== true) return;
    $(document).find('body').addClass('loading');
    if (typeof topbar !== 'undefined') {
        topbar.hide();
        topbar.show();
    }
}

wbapp.unloading = async function() {
    if (wbapp.loader !== true) return;
    $(document).find('body').removeClass('loading');
    if (typeof topbar !== 'undefined') {
        topbar.hide();
    }
}

wbapp.fetch = function(selector = 'body', data = {}, ret) {
    if ($(selector).length) {
        var tpl_id = $(selector).attr("data-wb-tpl");
        if (tpl_id !== undefined) {
            var html = urldecode($("#" + tpl_id).html());
        } else {
            if ($(selector).is("script")) {
                var html = $(selector).html();
            } else {
                if ($(selector).length == 1) {
                    var html = $(selector).outer();
                } else {
                    var html = selector;
                }
            }
        }
    } else {
        var html = selector;
    }
    var form = "undefined";
    var item = "undefined";
    if (data.form !== undefined) {
        form = data.form;
    }
    if (data.id !== undefined) {
        item = data.id;
    }
    if (data._form !== undefined) {
        form = data._form;
    }
    if (data._id !== undefined) {
        item = data._id;
    }
    if (data._item !== undefined) {
        item = data._item;
    }

    if (is_object(html)) { var tpl = $(html).outer(); } else { var tpl = html; }
    // контроллер не обслуживает данный запрос - устарело
    var url = "/ajax/setdata/" + form + "/" + item;
    var res = null;
    var param = { tpl: tpl, data: data };
    param = base64_encode(JSON.stringify(param));
    wbapp.postSync(url, {
        data: param
    }, function(data) {
        if (ret == undefined || ret == false) {
            $(selector).after(data).remove();
            res = true;
        } else {
            res = data;
        }
    });
    return res;
}


wbapp.toast = async function(title, text, params = {}) {
    var target = '.content-toasts';
    if (!$(document).find(target).length) {
        $('body').prepend('<div class="content-toasts position-fixed t-0" style="z-index:999999;right:0;"></div>');
    }

    let options = {
        'target': target,
        'delay': 3000,
    }

    if (wbapp.template['wb.toast'] == undefined) {
        var res = wbapp.getForm("snippets", "toast");
        wbapp.tpl('wb.toast', {
            html: res.result,
            params: {}
        });
    }

    let $tpl = $(wbapp.tpl('wb.toast').html);

    if (params.target) options.target = params.target;
    if (params.delay) options.delay = params.delay;
    if (params.bgcolor) {
        $tpl.addClass('bd-' + params.bgcolor);
        $tpl.find('.toast-header').addClass('bg-' + params.bgcolor);
    }
    if (params.txcolor) {
        $tpl.find('.toast-header h6').addClass('tx-' + params.txcolor).removeClass('tx-inverse');
    }
    $tpl.attr('data-delay', options.delay);

    let toast = Ractive({
        el: options.target,
        append: true,
        template: $tpl.outer(),
        data: {
            title: title,
            text: text,
            age: ''
        },
        on: {
            complete() {
                if (params.audio) {
                    let audio = new Audio(params.audio)
                    audio.autoplay = true
                }
            }
        }
    });

    let last = $(document).find(options.target).find(".toast:last-child")
    if (last.length && last.toast !== undefined) {
        last.toast('show')
            .off('hidden.bs.toast')
            .on('hidden.bs.toast', function(e) {
                $(e.currentTarget).remove();
            });
    }
}


wbapp.formByObj = function(selector, data) {
    var form = $(document).find(selector, 0);
    $(form)[0].clear;
    $.each(data, async function(key, value) {
        $(form).find("[name='" + key + "']").val(value);
    });
}

wbapp.objByForm = function(form) {
    form = $(form);
    let data = $(form).serializeJson();
    return data;
}

wbapp.tplInit = async function() {
    if (!wbapp.template) wbapp.template = {};
    if (wbapp.template['wb.modal'] == undefined) {
        fetch('/ajax/getform/snippets/modal')
            .then((response) => response.json())
            .then(function (res) { 
                wbapp.tpl('wb.modal', {
                    html: res.result,
                    params: {}
                });

             })
    }

    $(document).find("template:not([nowb])").each(async function() {
        if (this.done !== undefined) return
        else this.done = true;
        var tid
        if (tid == undefined && $(this).is("template[id]")) tid = $(this).attr("id");
        if (tid == undefined) tid = $(this).parent().attr("id");
        if (tid == undefined && $(this).is("[data-target]")) tid = $(this).attr("data-target");
        if (tid == undefined) {
            $(this).attr("id", "fe_" + wbapp.newId());
            var tid = $(this).attr("id");
        }
        tid = "#" + tid;

        var params = [];
        if ($(this).attr("data-params") !== undefined) {
            try {
                params = wbapp.parseAttr($(this).attr("data-params"));
                params['target'] = tid;
            } catch (error) { null }
        }

        if (params.filter !== undefined) {
            wbapp.data('wbapp.filter.' + tid.substr(1), params.filter);
        }

        let html = $(this).html();

        html = html.replace(/<template\b[^<]*(?:(?!<\/template>)<[^<]*)*<\/template>/gi, "");
        html = str_replace('%7B%7B', '{{', html);
        html = str_replace('%7D%7D', '}}', html);

        $(this).removeAttr("data-params");
        if ($(this).attr("data-ajax") !== undefined) {
            let prms = wbapp.parseAttr($(this).attr("data-ajax"));
            params = array_merge(prms, params);
            wbapp.tpl(tid, {
                'html': html,
                'params': params
            });
            $(this).trigger("click", tid);
        } else {
            wbapp.tpl(tid, {
                "html": html,
                "params": params
            });
        }

        if (params.bind && params.render == 'client') {
            wbapp.render(tid, wbapp.storage(params.bind));
        } else if (params.bind && params.render == 'server') {
            wbapp.storage(params.bind, params);
        } else if (params._params && params._params.bind && params._params.render == 'server') {
            wbapp.storage(params._params.bind, params);
        }
        if ($(this).prop("visible") == undefined) $(this).remove();
        wbapp.wbappScripts();
    });
}

wbapp.getForm = function(form, mode, data = {}, func = null) {
    if (!func) {
        var res = wbapp.postSync("/ajax/getform/" + form + "/" + mode, data);
    } else {
        var res = wbapp.post("/ajax/getform/" + form + "/" + mode, data, func);
    }
    return res;
}

wbapp.getTpl = function(tpl, data = {}, func = null) {
    if (!func) {
        var res = wbapp.postSync(`/ajax/gettpl/${tpl}`, data);
    } else {
        var res = wbapp.post(`/ajax/gettpl/${tpl}`, data, func);
    }
    return res;
}


wbapp.tpl = function(tid, data = null) {
    if (data == null && wbapp.template[tid] !== undefined) {
        if (wbapp.template[tid].params !== undefined && wbapp.template[tid].params.locale !== undefined) {
            let tpl = wbapp.template[tid].html;
            let loc = wbapp.template[tid].params.locale;
            $.each(loc, function(key, val) {
                tpl = str_replace('{{_lang.' + key + '}}', val, tpl);
            });
            wbapp.template[tid].html = tpl;
        }
        return wbapp.template[tid];
    } else {
        wbapp.template[tid] = data;
    }
}

wbapp.render = async function(tid, data) {
    if (tid == undefined) return;
    let params = wbapp.template[tid].params;
    if (data == undefined) {
        data = {};
        params.bind !== undefined ? data = wbapp.storage(params.bind) : null;
        params.update !== undefined ? data = wbapp.storage(params.update) : null;
    }
    if (params.render == undefined) params.render = null; // для рендера не списковых данных

    switch (params.render) {
        case 'client':
            wbapp.renderClient(params, data);
            break;
        case 'server':
            wbapp.renderServer(params, data);
            break;
        case null:
            wbapp.renderServer(params, data);
            break;
            /*
            new Ractive({
                'target': tid,
                'template': wbapp.template[tid].html,
                'data': () => { return data; }
            });
            break;
            */
    }
    wbapp.lazyload();
    wbapp.trigger('wb-render-done', tid, data);
}

wbapp.setPag = async function(target, data) {
    $(document).find('.pagination[data-tpl="' + target + '"]').parents('nav').remove();
    var pagert = $(document).find(target);
    if ($(pagert).is('li')) pagert = $(pagert).parent();
    if ($(pagert).is('tbody')) pagert = $(pagert).parents('table');
    if (data.pos == 'both' || data.pos == 'top') $(pagert).before(data.pag);
    if (data.pos == 'both' || data.pos == 'bottom') $(pagert).after(data.pag);
}

wbapp.renderServer = async function(params, data = {}) {
    if (params.target !== undefined && params.target > '#' && $(document).find(params.target).length) {
        //delete params.data;
        params.bind ? params.update = params.bind : null;
        delete params.bind;
        params._tid = params.target;
        let post;
        params.url == undefined && params.ajax !== undefined ? params.url = params.ajax : null;
        try {
            post = params._route._post;
        } catch (error) {
            post = null;
        }

        if (post) {
            wbapp.post(params.url, post, function(res) {
                let html = $(res).find(params.target).html();
                $(document).find(params.target).html(html);
                wbapp.refresh();
            })
        } else {
            wbapp.ajax(params, function(data) {
                wbapp.setPag(params.target, data.data)
            });
        }
    }
}

wbapp.renderClient = async function(params, data = {}) {
    var tid;
    var newbind = false;
    if (tid == undefined && params._tid !== undefined) tid = params._tid;
    if (tid == undefined && params.target !== undefined) tid = params.target;
    if (params.target == undefined && tid !== undefined) params.target = tid;

    /*
    var that = params._event.target;
    var tpl = $(that).parent();
    if ($(that).is("template")) tpl = $(that);
    var tid = "#"+$(tpl).parent().attr("id");
    if (params.target !== undefined) tid = params.target;
    */

    if (wbapp.template[tid] == undefined) return;

    if (params.from !== undefined && data[params.from] == undefined) {
        let from = {};
        from[params.from] = data;
        data = from;
    }


    if (wbapp.bind[params.bind] == undefined) {
        wbapp.bind[params.bind] = {};
        newbind = tid;
    }
    var html = wbapp.template[tid].html;
    wbapp.bind[params.bind][tid] = new Ractive({
            target: params.target,
            template: html,
            data: () => {
                return data
            }
        })
        ///wbapp.storage(params.bind, data);
    wbapp.template[tid].params = params;
    var pagination = $(tid).find(".pagination");
    if (pagination) {
        let page = 1;
        $(pagination).data("tpl", tid);
        params.page ? page = params.page : page = 1;
        $(pagination).find(".page-item").removeClass("active");
        $(pagination).find(`[data-page="${page}"]`).parent(".page-item").addClass("active");
    }

    if (newbind) {
        wbapp.bind[params.bind][tid].set(data);
        $(document).off("bind-" + params.bind);
        $(document).on("bind-" + params.bind, function(e, data) {
            try {
                wbapp.bind[params.bind][tid].set(data);
            } catch (error) {
                wbapp.bind[params.bind][tid].update(data);
            }
        })
    }
}

wbapp.newId = function(separator, prefix) {
    (separator == undefined) ? separator = "" : null;
    var mt = explode(" ", microtime());
    var md = substr(str_repeat("0", 2) + dechex(ceil(mt[0] * 10000)), -4);
    var id = dechex(time() + rand(100, 999));
    id = (prefix !== undefined && prefix > "") ? prefix + separator + id + md : "id" + id + separator + md;
    return id;
}

wbapp.modalsInit = async function() {
    wbapp.modalZndx == undefined ? wbapp.modalZndx = 2000 : null;

    $(document).undelegate(".modal-header", "dblclick");
    $(document).delegate(".modal-header", "dblclick", function(event) {
        var that = $(event.target);
        $(that).closest(".modal").toggleClass("modal-fullscreen");
    });

    if (document.modalDelegates == undefined) {
        document.modalDelegates = true;
        $(document).delegate(".modal", "shown.bs.modal", function() {
            $('.modal[data-zidx]').each(function() {
                let max = $(this).attr('data-zidx') * 1;
                if (max > wbapp.modalZndx) wbapp.modalZndx = max;
            })

            if ($(this).parents('.modal')) {
                $(this).appendTo($(this).parents('.modal').parent());
            }

            var that = this;
            if ($(that).find('.modal-content').css('position') == 'fixed') return;
            $(that).find('.modal-content')
                //      .resizable({
                //        minWidth: 300,
                //        minHeight: 175,
                //        handles: 'n, e, s, w, ne, sw, se, nw',
                //      })
                .draggable({
                    handle: '.modal-header',
                    containment: "body"
                });

            wbapp.modalZndx += 10;
            if (!$(this).closest().is("body")) {
                if ($(this).data("parent") == undefined) $(this).data("parent", $(this).closest());
                // нельзя переносить модальное окно, так как могут возникнуть проблемы с селектором!
                //$(this).appendTo("body");
            }
            if ($(that).attr('data-zidx') == undefined) {
                $(that).css("z-index", wbapp.modalZndx).attr('data-zidx', wbapp.modalZndx);
                if ($(that).attr("data-backdrop") !== "false") {
                    $(".modal-backdrop:not([data-zidx])").css("z-index", (wbapp.modalZndx - 5)).attr('data-zidx', wbapp.modalZndx - 5);
                }
            }

            let bh = $(this).find('.modal-body').height()
            if (bh > 0) {
                $(this).find('.modal-body .modal-h').height(bh)
            }

            window.dispatchEvent(new Event('resize'));
        });

        $(document).delegate(".modal", "DOMSubtreeModified", async function() {
            if ($(this).find('.modal-content').height() > $(window).height() - 80) {
                $(this).addClass('h-100');
            } else {
                $(this).removeClass('h-100');
            }
        })

        $(document).delegate(".modal", 'hidden.bs.modal', async function() {
            let zndx = $(this).css("z-index") * 1;
            $(".modal-backdrop[style*='z-index: " + (zndx - 5) + "']").remove();
            $(this).removeAttr('data-zidx');
        })

        $(document).delegate(".modal [data-dismiss]", "click", function(event) {
            event.preventDefault();
            let zndx = $(this).attr("data-dismiss") * 1;
            let modal = $(document).find(".modal[data-zidx='" + $(this).attr("data-dismiss") + "']");
            modal.modal("hide");
            $(this).removeAttr('data-zidx');
        });


        $(document).delegate(".modal", "hidden.bs.modal", function(event) {
            var that = this;
            if ($(this).hasClass("removable") || $(this).hasClass("remove")) {
                $(that).modal("dispose").remove();
            } else {
                $(this).appendTo($(this).data("parent"));
            }
        });
    }
}

wbapp.getModal = function(id = null) {
    var modal = $(document).data("wbapp-modal");
    if (modal == undefined) {
        var modal = wbapp.getForm("snippets", "modal");
        modal = $("<div>" + modal.content + "</div>").find(".modal").clone();
        $(document).data("wbapp-modal", modal);
    }
    var zndx = wbapp.modalZndx * 1 + 10;
    wbapp.modalZndx = zndx;
    if (id !== null) $(modal).attr("id", id);
    if (zndx !== undefined) $(modal).data("zndx", zndx).attr("style", "z-index:" + zndx);
    return $(modal).clone();
}

wbapp.ajaxSync = function(ajaxObjs, fn) {
    if (!ajaxObjs) return;
    wbapp.loading();
    var data = [];
    var ajaxCount = ajaxObjs.length;
    if (fn == undefined) {
        var fn = function(data) {
            return data;
        }
    }
    for (var i = 0; i < ajaxCount; i++) { //append logic to invoke callback function once all the ajax calls are completed, in success handler.
        try { ajaxObjs[i].data.__token = wbapp._session.token; } catch (error) { null }
        $.ajax(ajaxObjs[i]).done(function(res) {
            ajaxCount--;
            if (ajaxObjs.length > 0) {
                data.push(res);
            } else {
                data = res;
            }
        }).fail(function() {
            wbapp.unloading();
            ajaxCount--;
            if (ajaxObjs.length > 0) {
                data.push(false);
            } else {
                data = false;
            }
        }); //make ajax call
    };
    while (ajaxCount > 0) {
        // wait all done
    }
    wbapp.unloading();
    return fn(data);
}


wbapp.getSync = function(url, data = {}) {
    return wbapp.ajaxSync([{
        url: url,
        type: 'GET',
        async: false,
        data: data
    }])[0];
}

wbapp.postSync = function(url, data = {}) {
    return wbapp.ajaxSync([{
        url: url,
        type: 'POST',
        async: false,
        data: data
    }])[0];
}

wbapp.session = function(e) {
    if (wbapp._session == undefined) wbapp._session = wbapp.postSync("/ajax/getsess/");
    wbapp.trigger('wb-getsess', e, wbapp._session);
    return wbapp._session;
}

wbapp.settings = function(e) {
    if (wbapp._settings == undefined) wbapp._settings = wbapp.postSync("/ajax/getsett/");
    wbapp.trigger('wb-getsett', e, wbapp._settings);
    return wbapp._settings;
}

wbapp.console = async function(text) {
    if (wbapp._settings == undefined || wbapp._settings.devmode == 'on') {
        console.log(text);
    }
}

wbapp.require = function(src) {
    let script = document.createElement('script');
    let head = document.getElementsByTagName('head')[0];
    script.type = 'text/javascript';
    script.src = src;
    head.appendChild(script);
}


wbapp.loadScripts = async function(scripts = [], trigger = null, func = null) {
    if (document.loadedScripts == undefined) document.loadedScripts = [];
    if (document.loadingScripts == undefined) document.loadingScripts = [];
    let ready = [];
    var stop = 0;
    var count = scripts.length;
    var loadedArr = JSON.parse(JSON.stringify(document.loadedScripts));
    var loadingArr = JSON.parse(JSON.stringify(document.loadingScripts));
    scripts.forEach(function(src, i) {
        //    let name = src.split("/");
        //    name = name[name.length-1];
        let name = src + '';
        let loaded = in_array(name, loadedArr);
        let loading = in_array(name, loadingArr);;
        if (wbapp.devmode > 0 && src.indexOf('?') == -1) src += '?' + wbapp.devmode;
        if (loading) {
            wbapp.console("Script is loading: " + name);
        } else if (loaded) {
            wbapp.console("Script already loaded: " + name);
            stop += 1;
            if (stop >= count) {
                if (trigger > '') {
                    $(document).find("script#" + trigger + "-remove").remove();
                    $(document).trigger(trigger);
                }
                if (func !== null) return func(scripts);
            }
        } else if (!loading && !loaded) {
            let script = document.createElement('script');
            document.loadingScripts.push(name);
            wbapp._settings.devmode == 'on' && src.indexOf('?') == -1 ? script.src = name + "?" + wbapp.newId() : script.src = name;
            script.async = false;
            script.onload = async function() {
                document.loadedScripts.push(name);
                let pos = document.loadingScripts.indexOf(name);
                delete document.loadingScripts[pos];
                wbapp.console("Script loaded: " + name);
                stop += 1;
                if (stop >= count) {
                    if (trigger > '') {
                        $(document).find("script#" + trigger + "-remove").remove();
                        $(document).trigger(trigger);
                    }
                    if (func !== null) return func(scripts);
                }
            }
            document.head.appendChild(script);
        }

    });
}

wbapp.loadStyles = async function(styles = [], trigger = null, func = null) {
    if (wbapp.loadedStyles == undefined) wbapp.loadedStyles = [];
    var i = 0;
    styles.forEach(function(src) {
        setTimeout(function() {
            if (wbapp.loadedStyles.indexOf(src) !== -1) {
                i++;
                if (i >= styles.length) {
                    if (func !== null) return func(styles);
                    if (trigger !== null) {
                        wbapp.console("Trigger: " + trigger);
                        $(document).find("script#" + trigger + "-remove").remove();
                        $(document).trigger(trigger);
                    }
                }
            } else {
                if (wbapp.devmode && src.indexOf('?') == -1) src += '?' + wbapp.devmode;
                wbapp.css(src)
            }
        }, 1)
    });
}

wbapp.loadPreload = function(trigger = null, func = null) {
    let preloads = {};
    $('link[rel=preload][as][href]').each(function() {
        if (preloads[$(this).attr('as')] == undefined) { preloads[$(this).attr('as')] = []; }
        preloads[$(this).attr('as')].push($(this).attr('href'))
    });
    let preload_max = 0;
    let preload_count = 0;
    if (preloads.script !== undefined && preloads.script.length > 0) preload_max++;
    if (preloads.style !== undefined && preloads.style.length > 0) preload_max++;
    let preload_check = () => {
        if (preload_count == preload_max) {
            if (func !== null) return func(styles);
            if (trigger !== null) {
                $(document).trigger(trigger);
                wbapp.console("Trigger: " + trigger);
            }
            wbapp.trigger('ready-all');
        }
    }
    wbapp.loadScripts(preloads.script, 'preloaded-js', () => {
        preload_count++;
        preload_check()
    });
    wbapp.loadStyles(preloads.style, 'preloaded-css', () => {
        preload_count++;
        preload_check()
    });
}

wbapp.on = async function(trigger, func = null) {
    if (func == null) func = () => { return true; }
    $(document).on(trigger, func);
}

wbapp.trigger = async function(trigger, event = null, data = null) {
    wbapp.console('Trigger: ' + trigger);
    if (event == null) {
        $(document).trigger(trigger, data);
    } else {
        $(document).trigger(trigger, event, data);
        //$(event).trigger(trigger, data);
    }
}

String.prototype.replaceArray = function(find, replace) {
    var replaceString = this;
    var regex;
    for (var i = 0; i < find.length; i++) {
        regex = new RegExp(find[i], "g");
        replaceString = replaceString.replace(regex, replace[i]);
    }
    return replaceString;
};

wbapp.furl = function(str) {
    str = wbapp.transilt(str);
    str = str.replace("'", '');
    str = str.replace(/[^\/а-яА-Яa-zA-Z0-9_-]{1,}/gm, "-");
    str = str.replace('/', "-");
    str = str.replace(/[__]{1,}/gm, "_");
    str = str.replace(/[--]{1,}/gm, "-");
    str = str.replace(/[--]{1,}/gm, "-");
    if (str.substr(-1) == '-' || str.substr(-1) == '_') {
        str = str.substr(0, str.length - 1)
    }
    //str = str.replace('-', '_');
    return str.toLowerCase();
}



wbapp.transilt = function(word) {
    let cyr = [
        'ё', 'ж', 'ч', 'щ', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ы', 'ь', 'э', 'я',
        'Ё', 'Ж', 'Ч', 'Щ', 'Ш', 'Ю', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ы', 'Ь', 'Э', 'Я'
    ]
    let lat = [
        'yo', 'j', 'ch', 'sch', 'sh', 'u', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', '`', 'y', '', 'e', 'ya',
        'yo', 'J', 'Ch', 'Sch', 'Sh', 'U', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', '`', 'Y', '', 'E', 'ya'
    ]
    word = word + ""
    return word.replaceArray(cyr, lat)
}

wbapp.check_email = function(email) {
    if (email.match(/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/i)) {
        return true;
    } else {
        return false;
    }
}

var array_column = function(list, column, indice) {
    var result, key;
    if (list.length) {
        if (typeof indice !== "undefined") {
            result = {};

            for (key in list)
                if (typeof list[key][column] !== 'undefined' && typeof list[key][indice] !== 'undefined')
                    result[list[key][indice]] = list[key][column];
        } else {
            result = [];

            for (key in list)
                if (typeof list[key][column] !== 'undefined')
                    result.push(list[key][column]);
        }
    }
    return result;
}

var alive = setInterval(async function() {
    wbapp.alive();
}, 84600);

wbapp.init = async function() {
    wbapp.wbappScripts();
    wbapp.tplInit();
    wbapp.lazyload()
    wbapp.modalsInit();
}

wbapp.print = async function(pid) {
    var divToPrint = document.getElementById(pid);
    var newWin = window.open('', 'Print-Window');
    newWin.document.open();
    newWin.document.write('<!DOCTYPE html><html><head><link rel="stylesheet" type="text/css" href="/engine/lib/bootstrap/css/bootstrap.min.css"></head><body onload="window.print()">' + divToPrint.innerHTML + '</body></html>');
    newWin.document.close();
    setTimeout(() => { newWin.close(); }, 1000);
}

wbapp.redirectPost = async function(location, args) {
    var form = '';
    $.each(args, function(key, value) {
        value == undefined ? value = "" : null;
        typeof value == 'object' ? value = JSON.stringify(value) : null;
        value = value.split('"').join('\"');
        form += '<textarea style="display:none;" name="' + key + '">' + value + '</textarea>';
    });
    $('<form action="' + location + '" method="POST">' + form + '</form>').appendTo($(document.body)).submit();
}

function is_object(val) { return val instanceof Object; }

function is_callable(t, n, o) {
    var e = "",
        r = {},
        i = "";
    if ("string" == typeof t) r = window, e = i = t;
    else {
        if (!(t instanceof Array && 2 === t.length && "object" == typeof t[0] && "string" == typeof t[1])) return !1;
        r = t[0], i = t[1], e = (r.constructor && r.constructor.name) + "::" + i
    }
    return !(!n && "function" != typeof r[i]) && (o && (window[o] = e), !0)
}

function is_visible(elem) {
    if (!(elem instanceof Element)) throw Error('DomUtil: elem is not an element.');
    const style = getComputedStyle(elem);
    if (style.display === 'none') return false;
    if (style.visibility !== 'visible') return false;
    if (style.opacity < 0.1) return false;
    if (elem.offsetWidth + elem.offsetHeight + elem.getBoundingClientRect().height +
        elem.getBoundingClientRect().width === 0) {
        return false;
    }
    const elemCenter = {
        x: elem.getBoundingClientRect().left + elem.offsetWidth / 2,
        y: elem.getBoundingClientRect().top + elem.offsetHeight / 2
    };
    if (elemCenter.x < 0) return false;
    if (elemCenter.x > (document.documentElement.clientWidth || window.innerWidth)) return false;
    if (elemCenter.y < 0) return false;
    if (elemCenter.y > (document.documentElement.clientHeight || window.innerHeight)) return false;
    let pointContainer = document.elementFromPoint(elemCenter.x, elemCenter.y);
    do {
        if (pointContainer === elem) return true;
    } while (pointContainer = pointContainer.parentNode);
    return false;
}

var loadPhpjs = async function() {
    if (_tmpphp == false) {
        _tmpphp = true;
        let phpjs = document.createElement('script');
        phpjs.src = `/engine/js/php.js`;
        phpjs.async = false;
        phpjs.onload = async function() {
            setTimeout(() => { wbapp.start() }, 10);
        }
        document.head.appendChild(phpjs);
    }
}

var loadJquery = function() {
    if (_tmpjq == false) {
        _tmpjq = true;
        let jquery = document.createElement('script');
        jquery.src = '/engine/js/jquery.min.js';
        jquery.async = false;
        jquery.onload = async function() {
            setTimeout(() => { wbapp.start() }, 10);
        }
        document.head.appendChild(jquery);
    }

}
