"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var wbapp = new Object();
var _tmpphp = false;
var _tmpjq = false;
setTimeout(function () {
  wbapp.loader = true;
  var loader = document.getElementById("loader");
  typeof loader != 'undefined' && loader != null ? wbapp.delay = 2500 : wbapp.delay = 10;

  var get_cookie = function get_cookie(name) {
    var value = "; ".concat(document.cookie);
    var parts = value.split("; ".concat(name, "="));
    if (parts.length === 2) return parts.pop().split(';').shift();
  };

  wbapp.devmode = get_cookie('devmode');
  wbapp.evClick = 'tap click touchstart';
  wbapp.start();
}, 10);

wbapp.start = function () {
  if (typeof str_replace === 'undefined') {
    loadPhpjs();
    return;
  }

  if (typeof $ === 'undefined') {
    loadJquery();
    return;
  }

  var data = {};
  wbapp.bind = {};
  wbapp.ui = {
    spinner_sm: '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>',
    spinner_sm_grow: '<span class="spinner-grow spinner-grow-sm" role="status"></span>'
  };
  wbapp.session();
  wbapp.settings();
  setTimeout(function () {
    wbapp.loadScripts([//                , `/engine/js/jquery-migrate.min.js`
    "/engine/js/jquery-ui.min.js" // для modal draggable - нужно подумать куда перенести
    , "/engine/js/jquery.tap.js", "/engine/js/ractive.js", "/engine/js/topbar.min.js", "/engine/js/lazyload.js"], "wbapp-go", function () {
      Ractive.DEBUG = false;
      wbapp.eventsInit();
      wbapp.wbappScripts();
      wbapp.tplInit();
      wbapp.ajaxAuto();
      wbapp.lazyload();
      wbapp.modalsInit();
      $(document).scrollTop(0);
    });
  }, wbapp.delay);

  $.fn.disableSelection = function () {
    return this.attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);
  };

  $.fn.verify = function () {
    var form = this;
    var res = true;
    var idx = 0;
    $(form).find("[required],[minlength],[min],[max],[name=password],[type=email]").each(function () {
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
        if ($(this).val() > '' && !wbapp.check_email($(this).val()) || $(this).val() == '' && $(this).prop('required')) {
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
          $("[type=radio][name='" + fld + "']").each(function () {
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

        var check = $(form).find("input[name=" + pcheck + "]");

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
        var min = $(this).attr("min") * 1;
        var minstr = $(this).val() * 1;

        if (minstr < min) {
          res = false;
          $(this).data("error", ucfirst(label) + " " + wbapp._settings.sysmsg.min_val + ": " + min);
          wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
          $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
        }
      }

      if ($(this).is("[max]:not([readonly],[disabled])") && $(this).val() > "") {
        var max = $(this).attr("max") * 1;
        var maxstr = $(this).val() * 1;

        if (maxstr > max) {
          res = false;
          $(this).data("error", ucfirst(label) + " " + wbapp._settings.sysmsg.max_val + ": " + max);
          wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
          $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
        }
      }

      if ($(this).is("[minlength]:not([readonly],[disabled])") && $(this).val() > "") {
        var minlen = $(this).attr("minlength") * 1;
        var lenstr = strlen($(this).val());

        if (lenstr < minlen) {
          res = false;
          $(this).data("error", ucfirst(label) + " " + wbapp._settings.sysmsg.min_length + ": " + minlen);
          wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
          $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
        }
      }

      if ($(this).is('select[required]')) {
        var val = $(this).find('option:selected').attr('value');

        if (val == undefined || val == '') {
          res = false;
          $(this).data("error", wbapp._settings.sysmsg.required + ucfirst(label));
          wbapp.console("trigger: wb-verify-false [" + $(this).attr("name") + "]");
          $(form).trigger("wb-verify-false", [this, $(this).data("error")]);
        }
      }

      if ($(this).is("button")) {
        if ($(this).attr("value") !== undefined && $(this).val() == "" || $(this).attr("value") == undefined && $(this).html() == "") {
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
  };

  $.fn.outer = function (s) {
    return s ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
  };

  $.fn.runScripts = function () {
    $(this).find("script").each(function () {
      var type = $(this).attr("type");

      if (type !== "text/locale" && type !== "text/template") {
        eval($(this).text());
        if ($(this).attr("removable") !== undefined) $(this).remove();
      }
    });
  };

  $.fn.serializeJson = function () {
    var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var form = this;
    $(form).find("form [name], .wb-unsaved[name], .wb-tree-item [name]").each(function () {
      $(this).attr("wb-tmp-name", $(this).attr("name"));
      $(this).removeAttr("name");
    });
    var branch = $(form).serializeArray();
    $(branch).each(function (i, val) {
      data[val["name"]] = val["value"];

      if ($(form).find("textarea[type=json][name='" + val["name"] + "']").length) {
        var _val = $(form).find("textarea[type=json][name='" + val["name"] + "']").val();

        var _text = $(form).find("textarea[type=json][name='" + val["name"] + "']").text();

        _val == 'null' ? data[val["name"]] = _text : data[val["name"]] = _val;

        if (in_array(data[val["name"]], ['null', '', '{}', '[]'])) {
          data[val["name"]] = '';
        } else {
          try {
            data[val["name"]] = json_decode(data[val["name"]]);
          } catch (error) {
            wbapp.console('Unknown error!');
          }
        }
      } else if ($(form).find("textarea[name='" + val["name"] + "']").length) {
        if ($(form).parents(".treeData").length) {
          data[val["name"]] = htmlentities(data[val["name"]]);
          data[val["name"]] = str_replace('&quot;', '/"', data[val["name"]]);
          data[val["name"]] = str_replace('&amp;quot;', '"', data[val["name"]]);
        } else {
          var value = $(form).find("textarea[name='" + val["name"] + "']").val();
          var text = $(form).find("textarea[name='" + val["name"] + "']").text();

          if (value == 'null') {
            data[val["name"]] = text;
          } else {
            data[val["name"]] = value;
          }
        }
      }
    });
    var sel = $(form).find('select[name]:not([multiple])');
    $.each(sel, function () {
      data[this.name] = $(this).val();
    });
    var multi = $(form).find('select[name][multiple]');
    $.each(multi, function () {
      data[this.name] = $(this).val();
    });
    var check = $(form).find('input[name][type=checkbox]'); // fix unchecked values

    $.each(check, function () {
      data[this.name] = "";
      if (this.checked) data[this.name] = "on";
    });
    var check = $(form).find('input[name][type=radio]'); // fix unchecked values

    $.each(check, function () {
      if (this.checked) data[this.name] = $(this).attr('value');
    });
    $(form).find("form [wb-tmp-name], .wb-unsaved [wb-tmp-name], .wb-tree-item [wb-tmp-name]").each(function () {
      $(this).attr("name", $(this).attr("wb-tmp-name"));
      $(this).removeAttr("wb-tmp-name");
    });
    return data;
  };

  $.fn.jsonVal = function () {
    var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : undefined;

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
  };
};

wbapp.lazyload = function () {
  $("[data-src]:not([src])").each(function () {
    var link = document.createElement('link');
    link.rel = "preload";
    link.as = "image";
    link.href = $(this).attr('data-src');
    document.head.appendChild(link);
  });
  $("[data-src]:not([src])").lazyload();
};

wbapp.eventsInit = function () {
  $(document).delegate("[data-ajax]:not(input,select)", "click", function (e, tid) {
    if (!$(this).is("input,select")) {
      var params = wbapp.parseAttr($(this).attr("data-ajax"));

      if (Object.keys(params)[0] == $(this).attr("data-ajax")) {
        // ajax string only
        params.url = $(this).attr("data-ajax");

        if ($(this).parents('form').length) {
          var id = wbapp.newId('_', 'ax');

          if ($(this).parents('form').attr('id') == undefined) {
            $(this).parents('form').attr('id', id);
          }

          params.form = 'form#' + $(this).parents('form').attr('id');

          if (!$(params.form).verify()) {
            return false;
          }
        }
      }

      if ($(this).is('[data-toggle=tooltip]')) {
        $(this).tooltip('hide');
      } // fix tooltips


      params._event = e;
      if (tid !== undefined) params._tid = tid;
      wbapp.ajax(params);
      wbapp.console("Trigger: data-ajax");
      $(document).trigger("data-ajax", params);
      var href = $(this).attr('href');

      if (href !== undefined && href.substr(0, 1) == '#') {
        document.location.anchor = $(this).attr('href');
      }
    }
  });
  $(document).delegate("input[data-ajax],select[data-ajax]", "change", function (e, tid) {
    e.preventDefault();
    var search = $(this).attr("data-ajax");
    search = str_replace('$value', $(this).val(), search);
    var params = wbapp.parseAttr(search);
    params._event = e;
    if (tid !== undefined) params._tid = tid;
    wbapp.ajax(params);
    return false;
  });
  $(document).delegate("input[type=search][data-ajax]", "keyup", function () {
    var minlen = 0;
    var that = this;
    var val = $(this).val();
    that.waitajax = false;
    if ($(this).attr("minlength")) minlen = $(this).attr("minlength") * 1;

    if (that.waitajax == false && val.length >= minlen) {
      that.waitajax = true;
      $(this).trigger("change");
      setTimeout(function () {
        that.waitajax = false;
      }, 500);
    }
  });
  $(document).delegate("input[type=checkbox]", "click", function () {
    if ($(this).prop("checked") == false) {
      $(this).removeAttr("checked");
    } else {
      $(this).prop("checked", true);
      $(this).attr("checked", true);
    }
  });
  $(document).delegate("[rows=auto]", "keydown keyup focus", function () {
    this.style.overflow = "hidden";
    this.style.height = "1px";
    this.style.height = this.scrollHeight + "px";
  });
  $(document).delegate("[rows=auto]", "focusout", function () {
    this.style.overflow = "hidden";
    this.style.height = "auto";
  });
};

wbapp.ajaxAuto = function () {
  var func = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
  $(document).find("[data-ajax][auto]").each(function () {
    $(this).trigger("click");
    if ($(this).attr('once') !== undefined) $(this).removeAttr('data-ajax').removeAttr('once');
    $(this).removeAttr("auto");
  });
};

wbapp.auth = function (form) {
  var mode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'signin';
  if (!$(form).verify()) return;
  var data = $(form).serializeJson();

  var dislink = function dislink() {
    $(form).find('a:not([disabled]),button:not([disabled]),[data-ajax]:not([disabled])').addClass('wb-auth-disabled').attr('disabled', true);
    $(form).addClass('cursor-wait');
  };

  var enlink = function enlink() {
    $(form).find('.wb-auth-disabled').removeClass('wb-auth-disabled').removeAttr('disabled');
    $(form).removeClass('cursor-wait');
  };

  var signin = function signin() {
    if ($(form).attr("action") !== undefined) var url = $(form).attr("action");else var url = "/api/auth/email";
    wbapp.post(url, data, function (res) {
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
  };

  var signup = function signup() {
    if ($(form).attr("action") !== undefined) var url = $(form).attr("action");else var url = "/api/auth/signup";
    wbapp.post(url, data, function (res) {
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
  };

  var recover = function recover() {
    if ($(form).attr("action") !== undefined) var url = $(form).attr("action");else var url = "/api/auth/recover";
    data.text = json_encode($(form).find('.recover-text').html());
    wbapp.post(url, data, function (res) {
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
  };

  dislink();
  eval(mode)();
};

wbapp.alive = function () {
  $.post("/ajax/alive", {}, function (data) {
    if (data.result == false || data.result == undefined) {
      wbapp.console("Trigger: session_close");
      $(document).trigger("session_close");
      clearInterval(alive);
    }
  });
};

wbapp.storage = function (key) {
  var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;
  var binds = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

  function getKey(list) {
    key = "";
    $(list).each(function (i, k) {
      if (k.substr(0, 1) * 1 > -1) {
        key += "['".concat(k, "']");
      } else {
        if (i > 0) key += '.';
        key += k;
      }
    });
    return key;
  }

  if (value === undefined) {
    // get data
    var _list = key.split(".");

    var res;
    var data = localStorage.getItem(_list[0]);
    if (data !== null) data = JSON.parse(data);

    if (_list.length) {
      key = getKey(_list);

      try {
        eval("res = data.".concat(key));
        if (_typeof(res) == 'object') res = Object.assign({}, res);
        return res;
      } catch (err) {
        return undefined;
      }
    }

    return data;
  } else {
    // set data
    var path, branch, type;
    var list = key.split(".");
    var last = list.length;
    var lastkey = list[last - 1];
    $(list).each(function (i, k) {
      if (i == 0) {
        key = k;
        data = localStorage.getItem(key);

        if (i + 1 !== last && data == null) {
          data = {};
        } else {
          try {
            data = JSON.parse(data);
          } catch (err) {
            data = {};
          }
        }
      } else {
        if (k.substr(0, 1) * 1 > -1) {
          key += "['".concat(k, "']");
        } else {
          if (i > 0) key += '.';
          key += k;
        }
      }

      try {
        eval("branch = data.".concat(key));
        if (_typeof(branch) == 'object') branch = Object.assign({}, branch);
        if (i + 1 < last && _typeof(branch) !== "object") eval("data.".concat(key, " = {}"));
      } catch (err) {
        eval("data.".concat(key, " = {}"));
      }
    });
    var tmpValue = value;

    if (value === null) {
      eval("delete data.".concat(key));
    } else if (value !== {}) {
      if (typeof value == 'string') {
        eval("data.".concat(key, " = value"));
      } else {
        eval("tmpValue = Object.assign({}, value)");
        Object.entries(tmpValue).length == 0 ? null : value = tmpValue;
        if (_typeof(value) == 'object') value = Object.assign({}, value);
        eval("data.".concat(key, " = value"));
      }
    }

    localStorage.setItem(list[0], json_encode(data));

    var checkBind = function checkBind(bind, key) {
      if (bind == key) return true;
      if (key.substr(0, bind.length) == bind) return true;
      return false;
    };

    if (binds == true) {
      $.each(wbapp.template, function (i, tpl) {
        if (tpl.params.bind !== undefined && checkBind(tpl.params.bind, key) && tpl.params.render !== undefined && tpl.params.render == 'client') {
          wbapp.render(tpl.params.target);
        } else if (tpl.params._params !== undefined && tpl.params._params.bind !== undefined && checkBind(tpl.params._params.bind, key) && tpl.params.render == 'server') {
          wbapp.render(tpl.params.target);
        }
      });
      $(document).trigger("bind", {
        key: key,
        data: value
      });
      $(document).trigger("bind-" + key, value);
      wbapp.console("Trigger: bind [" + key + "]");
    }

    return data;
  }
};

wbapp.save = function (obj, params, event) {
  wbapp.console("Trigger: wb-save-start");
  $(obj).trigger("wb-save-start", params);
  var that = this;
  var data, form, result;
  var method = "POST";

  if (params.form !== undefined) {
    form = $(params.form);
  } else {
    form = $(obj).parents("form");
  }

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

  setTimeout(function () {
    // Задержка для ожидания обработки возможных плагинов
    data = wbapp.objByForm(form);
    if (data._idx) delete data._idx; //if ($(obj).is("input,select,textarea,button,img,a") && params.table && (params.id || params.item)) {

    if (params.table && (params.id || params.item)) {
      var fld = $(obj).attr("name");
      var value = $(obj).val();
      var id;
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
        eval("data.".concat(fld, " = value;"));
      } else {
        var tmpId = 'tmp.' + wbapp.newId();
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
      if (params.url == undefined) params.url = "/api/save/".concat(params.table);
    } else if (params.field !== undefined) {
      wbapp.storage('tmp' + '.' + params.table + '.' + params.field, data, false);
      data = wbapp.storage('tmp' + '.' + params.table);
    }

    if ($(obj).data('saved-id') !== undefined) {
      data['id'] = $(obj).data('saved-id');
    }

    wbapp.post(params.url, data, function (data) {
      if (data.error == true) {
        $(obj).trigger("wb-save-error", {
          params: params,
          data: data
        });
        return null;
      }

      if (params.callback) eval('params = ' + params.callback + '(params,data)');

      if (params.data && params.error !== true) {
        var update = [];
        var dataname;
        $.each(data, function (key, value) {
          update[key] = value;
        });
        eval('var checknew = (typeof ' + params.data + ');');

        if (checknew == "undefined") {
          eval("dataname = str_replace(\"['" + data._id + "']\",\"\",\"" + params.data + "\");");
          eval(dataname + '.push(update)');
        } else {
          eval(params.data + ' = update;');
        }
      }

      if (params.dismiss && params.error !== true) $("#" + params.dismiss).modal("hide");
      wbapp.console('Update by tpl');
      $.each(wbapp.template, function (i, tpl) {
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
          if (tpl.params.bind !== undefined) tpl.params.update = tpl.params.bind;
          wbapp.renderServer(tpl.params, data);
        }
      });
      if (data._id !== undefined) $(obj).data('saved-id', data._id);
      wbapp.console("Trigger: wb-save-done");
      $(obj).trigger("wb-save-done", {
        params: params,
        data: data
      });
    });
  }, 50);
};

wbapp.updateInputs = function () {
  $(document).find(":checkbox").each(function () {
    if ($(this).attr("value") == "on") {
      $(this).attr("checked", true).prop("checked", true);
    } else {
      $(this).attr("checked", false).prop("checked", false);
    }
  });
};

wbapp.wbappScripts = function () {
  var done = [];
  $(document).find("script[type=wbapp],script[wbapp],script[wb-app]").each(function () {
    if (this.done !== undefined) return;
    this.done = true;
    var src = null;

    if ($(this).is('[src]')) {
      src = $(this).attr('src');
    } else if ($(this).is('[wbapp]')) {
      src = $(this).attr('wbapp');
    } else if ($(this).is('[wb-app]')) {
      src = $(this).attr('wb-app');
    }

    if (src !== null && src > '') {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', src, true);

      xhr.onload = function () {
        eval(xhr.responseText);
      };

      xhr.send();
    } else {
      var script = $(this).text();
      var hash = md5(script);

      if (!in_array(hash, done)) {
        eval(script);
        done.push(hash);
      }
    }

    if ($(this).attr("remove") !== undefined) $(this).remove();
  });
};

wbapp.checkJson = function (queryString) {
  queryString = str_replace(" ", "", queryString.trim());
  if (queryString == "{}") return true;
  if (queryString.substr(0, 1) == "{" && queryString.substr(-1) == "}" && strpos(queryString, ":")) return true;
  return false;
};

wbapp.parseAttr = function () {
  var queryString = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
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
};

wbapp.post = function _callee(url) {
  var data,
      func,
      _args = arguments;
  return regeneratorRuntime.async(function _callee$(_context) {
    while (1) {
      switch (_context.prev = _context.next) {
        case 0:
          data = _args.length > 1 && _args[1] !== undefined ? _args[1] : {};
          func = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;

          if (is_string(data)) {
            data += '&__token=' + wbapp._session.token;
          } else {
            try {
              data.__token = wbapp._session.token;
            } catch (error) {
              null;
            }
          }

          wbapp.loading();
          $.post(url, data).then(function (data) {
            wbapp.unloading();
            if (func !== null) return func(data);
          });

        case 5:
        case "end":
          return _context.stop();
      }
    }
  });
};

wbapp.get = function _callee2(url) {
  var data,
      func,
      _args2 = arguments;
  return regeneratorRuntime.async(function _callee2$(_context2) {
    while (1) {
      switch (_context2.prev = _context2.next) {
        case 0:
          data = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : {};
          func = _args2.length > 2 && _args2[2] !== undefined ? _args2[2] : null;

          if (is_string(data)) {
            data += '&__token=' + wbapp._session.token;
          } else {
            try {
              data.__token = wbapp._session.token;
            } catch (error) {
              null;
            }
          }

          wbapp.loading();
          $.get(url, data).then(function (data) {
            wbapp.unloading();
            if (func !== null) return func(data);
          }).fail(function (data) {
            wbapp.unloading();
            if (func !== null) return func(false);
          });

        case 5:
        case "end":
          return _context2.stop();
      }
    }
  });
};

wbapp.ajax = function _callee3(params) {
  var func,
      opts,
      token,
      _opts,
      target,
      clearval,
      _args3 = arguments;

  return regeneratorRuntime.async(function _callee3$(_context3) {
    while (1) {
      switch (_context3.prev = _context3.next) {
        case 0:
          func = _args3.length > 1 && _args3[1] !== undefined ? _args3[1] : null;

          if (!(!params.url && !params.tpl && !params.target)) {
            _context3.next = 3;
            break;
          }

          return _context3.abrupt("return");

        case 3:
          opts = Object.assign({}, params);
          delete opts._event;

          if (!(params.request_type == 'remove_item')) {
            _context3.next = 9;
            break;
          }

          wbapp.post(params.url, opts, function (data) {
            if (data._removed !== undefined && data._removed == true && params.update !== undefined) {
              $.each(wbapp.template, function (i, tpl) {
                var rend = false;
                if (tpl.params !== undefined && tpl.params.bind !== undefined && tpl.params.bind == params.update) rend = true;
                if (tpl.params !== undefined && tpl.params._params !== undefined && tpl.params._params.bind !== undefined && tpl.params._params.bind == params.update) rend = true;
                if (rend) wbapp.renderServer(tpl.params);
              });
            }
          });
          _context3.next = 39;
          break;

        case 9:
          if (!(params.url !== undefined)) {
            _context3.next = 19;
            break;
          }

          if (params.form !== undefined) {
            params.formdata = wbapp.objByForm(params.form);
            params.formflds = {};
            $(params.form).find('[name]').each(function () {
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
            });
          }

          _opts = Object.assign({}, params);
          delete _opts._event;

          if (_opts._tid !== undefined && _opts._tid > '') {
            delete _opts.data; // добавил для очистки фильтра

            params = wbapp.tpl(_opts._tid).params;
          }

          wbapp.loading();
          if (params.filter !== undefined) _opts.filter = params.filter;
          wbapp.post(params.url, _opts, function (data) {
            wbapp.unloading();

            if (count(data) == 2 && data.error !== undefined && data.callback !== undefined) {
              eval(data.callback + '(params,data)');
              if (func !== null) return func(params, data);
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
            if (params.replace) $(document).find(params.replace).replaceWith(data); //if (params.form) wbapp.formByObj(params.form,data);

            if (params.render == 'client') {
              if (params.bind && _typeof(data) == "object") wbapp.storage(params.bind, data);
              if (params.update && _typeof(data) == "object") wbapp.storageUpdate(params.update, data);
            }

            if (params._trigger !== undefined && params._trigger == "remove") eval('delete ' + params.data); // ???

            if (params.dismiss && params.error !== true) $("#" + params.dismiss).modal("hide");
            if (params.render !== undefined && params.render == 'client') wbapp.renderClient(params, data);

            if (params._event !== undefined && $(params._event.target).parent().is(":input")) {// $inp = $(params._event.target).parent();
              // тут нужна обработка значений на клиенте
            }

            if (params.render == 'client') {
              var res = $(data).find(params.target).html();
              $(document).find(params.target).html(res);
            } else if (params.render == undefined || params.render == 'server') {
              if (data.html !== undefined && params.target !== undefined) {
                $(document).find(params.target).html(data.html);
              } else if (params.update !== undefined || params.update !== undefined) {
                $.each(wbapp.template, function (i, tpl) {
                  if (tpl.params && tpl.params.bind && tpl.params.bind == params.update) {
                    if (_typeof(tpl.params) == 'object' && _typeof(data) == 'object') wbapp.renderServer(tpl.params, data);
                  } else if (tpl.params && tpl.params._params && tpl.params._params.bind && tpl.params._params.bind == params.update) {
                    if (_typeof(tpl.params._params) == 'object' && _typeof(data) == 'object') wbapp.renderServer(tpl.params._params, data);
                  }
                });
              }

              $(document).find(params.target).children('template').remove();
              $(document).find('.pagination[data-tpl="' + params.target + '"]').parents('nav').remove();
              var pagert = $(document).find(params.target).parent();
              if ($(pagert).is('li')) pagert = $(pagert).parent();
              if ($(pagert).is('tbody')) pagert = $(pagert).parent();
              if (data.pos == 'both' || data.pos == 'top') $(pagert).before(data.pag);
              if (data.pos == 'both' || data.pos == 'bottom') $(pagert).after(data.pag);
            }

            if (params.callback !== undefined) eval(params.callback + '(params,data)');
            wbapp.tplInit();
            wbapp.lazyload();
            wbapp.ajaxAuto(); //wbapp.console("Trigger: wb-ajax-done");

            if (data.result == undefined) params['data'] = data;

            if (params.form !== undefined) {
              $(params.form).trigger("wb-ajax-done", params);
            } else if (params.target !== undefined) {
              $(params.target).trigger("wb-ajax-done", params);
            } else {
              $(document).trigger("wb-ajax-done", params);
            }

            var showmod = $(document).find(".modal.show:not(:visible)");
            if (showmod.length) showmod.removeClass("show").modal('show');
            if (func !== null) return func(params, data);
          });
          _context3.next = 39;
          break;

        case 19:
          if (!(params.target !== undefined)) {
            _context3.next = 39;
            break;
          }

          if (wbapp.template[params.target] !== undefined) {
            target = wbapp.template[params.target].params;
          }

          if (target) {
            _context3.next = 26;
            break;
          }

          wbapp.console("Template not found: " + params.target);
          return _context3.abrupt("return");

        case 26:
          target.target = params.target;
          if (target.filter == undefined) target.filter = {};
          if (target._params == undefined) target._params = {};
          clearval = null;
          $.each(params, function (key, val) {
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

            if (key == 'filter_remove' && target.filter[val] !== undefined) delete target.filter[val];

            if (key == 'filter_add') {
              $.each(val, function (k, v) {
                target.filter[k] = v;
              });
            }
          });

          if (clearval !== null) {
            $(clearval).each(function (k, v) {
              $(target.filter).each(function (tk, tv) {
                json_encode(tv) == json_encode(v) ? target.filter = {} : null;
              });
            });
          }

          if (target._params && target._params.page !== undefined) target._params.page = 1;
          if (target._params && target._params.pages !== undefined) delete target._params.pages;
          if (target._params && target._params.count !== undefined) delete target._params.count;
          if (target.tpl !== undefined) target._params.tpl = target.tpl;
          if (target._tid == undefined) target._tid = params.target; // чтобы срабатывал вариант ответа с json

          if (target.url == undefined && target.route !== undefined && target.route.uri !== undefined) target.url = target.route.uri;
          wbapp.ajax(target, func);

        case 39:
        case "end":
          return _context3.stop();
      }
    }
  });
};

wbapp.storageUpdate = function (key, data) {
  var store = wbapp.storage(key);
  if (!store) wbapp.storage(key, {});

  if (store._id == undefined && (store.result !== undefined || store.params !== undefined) && data !== null && data._id !== undefined) {
    if (data._removed !== undefined && data._removed == true) {
      if (store.params !== undefined && store.params.render == 'server') {
        wbapp.renderServer(store.params);
      } else {
        try {
          delete store.result[data._id];
        } catch (err) {
          wbapp.console('Not removed');
        }
      }
    } else if (data._renamed !== undefined && data._renamed == true) {/// rename
    } else {
      try {
        store.result[data._id] = data;
      } catch (err) {}
    }

    wbapp.storage(key, store);
  } else {
    wbapp.storage(key, data);
  }
};

wbapp.loading = function () {
  if (wbapp.loader !== true) return;
  $(document).find('body').addClass('loading');

  if (typeof topbar !== 'undefined') {
    topbar.hide();
    topbar.show();
  }
};

wbapp.unloading = function () {
  if (wbapp.loader !== true) return;
  $(document).find('body').removeClass('loading');

  if (typeof topbar !== 'undefined') {
    topbar.hide();
  }

  wbapp.fetch = function (selector, data, ret) {
    if (selector == undefined) {
      var selector = "body";
    }

    if (data == undefined) {
      var data = {};
    }

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

    if (is_object(html)) {
      var tpl = $(html).outer();
    } else {
      var tpl = html;
    } // контроллер не обслуживает данный запрос - устарело


    var url = "/ajax/setdata/" + form + "/" + item;
    var res = null;
    var param = {
      tpl: tpl,
      data: data
    };
    param = base64_encode(JSON.stringify(param));
    wbapp.postSync(url, {
      data: param
    }, function (data) {
      if (ret == undefined || ret == false) {
        $(selector).after(data).remove();
        res = true;
      } else {
        res = data;
      }
    });
    return res;
  };
};

wbapp.toast = function (title, text) {
  var params = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var target = '.content-toasts';

  if (!$(document).find(target).length) {
    $('body').prepend('<div class="content-toasts position-fixed t-0" style="z-index:999999;right:0;"></div>');
  }

  var options = {
    'target': target,
    'delay': 3000
  };

  if (wbapp.template['wb.toast'] == undefined) {
    var res = wbapp.getTpl("snippets", "toast");
    wbapp.tpl('wb.toast', {
      html: res.result,
      params: {}
    });
  }

  var $tpl = $(wbapp.tpl('wb.toast').html);
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
  var toast = Ractive({
    el: options.target,
    append: true,
    template: $tpl.outer(),
    data: {
      title: title,
      text: text,
      age: ''
    }
  });
  $(document).find(options.target).find(".toast:last-child").toast('show').off('hidden.bs.toast').on('hidden.bs.toast', function (e) {
    $(e.currentTarget).remove();
  });
};

wbapp.formByObj = function (selector, data) {
  var form = $(document).find(selector, 0);
  $(form)[0].clear;
  $.each(data, function (key, value) {
    $(form).find("[name='" + key + "']").val(value);
  });
};

wbapp.objByForm = function (form) {
  form = $(form);
  var data = $(form).serializeJson();
  return data;
};

wbapp.tplInit = function () {
  if (!wbapp.template) wbapp.template = {};

  if (wbapp.template['wb.modal'] == undefined) {
    var res = wbapp.getTpl("snippets", "modal");
    wbapp.tpl('wb.modal', {
      html: res.result,
      params: {}
    });
  }

  setTimeout(function () {
    $(document).find("template").each(function () {
      var tid;
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
        } catch (error) {
          null;
        }
      }

      var html = $(this).html();
      html = html.replace(/<template\b[^<]*(?:(?!<\/template>)<[^<]*)*<\/template>/gi, "");
      html = str_replace('%7B%7B', '{{', html);
      html = str_replace('%7D%7D', '}}', html);
      $(this).removeAttr("data-params");

      if ($(this).attr("data-ajax") !== undefined) {
        var prms = wbapp.parseAttr($(this).attr("data-ajax"));
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
        var profileMenu = Ractive({
          target: tid,
          template: wbapp.template[tid].html,
          data: function data() {
            return wbapp.storage(params.bind);
          }
        });
        wbapp.render(tid, wbapp.storage(params.bind));
      } else if (params.bind && params.render == 'server') {
        wbapp.storage(params.bind, params);
      } else if (params._params && params._params.bind && params._params.render == 'server') {
        wbapp.storage(params._params.bind, params);
      }

      if ($(this).prop("visible") == undefined) $(this).remove();
    });
    wbapp.wbappScripts();
  }, 10);
};

wbapp.getForm = function (form, mode) {
  var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var res = wbapp.postSync("/ajax/getform/".concat(form, "/").concat(mode), data);
  return res;
};

wbapp.getTpl = function (form, mode) {
  var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var res = wbapp.postSync("/ajax/gettpl/".concat(form, "/").concat(mode), data);
  return res;
};

wbapp.tpl = function (tid) {
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

  if (data == null && wbapp.template[tid] !== undefined) {
    if (wbapp.template[tid].params !== undefined && wbapp.template[tid].params.locale !== undefined) {
      var tpl = wbapp.template[tid].html;
      var loc = wbapp.template[tid].params.locale;
      $.each(loc, function (key, val) {
        tpl = str_replace('{{_lang.' + key + '}}', val, tpl);
      });
      wbapp.template[tid].html = tpl;
    }

    return wbapp.template[tid];
  } else {
    wbapp.template[tid] = data;
  }
};

wbapp.render = function (tid, _data) {
  if (tid == undefined) return;
  var params = wbapp.template[tid].params;
  if (_data == undefined && params.bind == undefined) _data = {};
  if (_data == undefined) _data = wbapp.storage(params.bind);
  if (params.render == undefined) params.render = null; // для рендера не списковых данных

  switch (params.render) {
    case 'client':
      wbapp.renderClient(params, _data);
      break;

    case 'server':
      wbapp.renderServer(params, _data);
      break;

    case null:
      new Ractive({
        'target': tid,
        'template': wbapp.template[tid].html,
        'data': function data() {
          return _data;
        }
      });
      break;
  }

  wbapp.lazyload();
  wbapp.trigger('wb-render-done', tid, _data);
};

wbapp.renderServer = function (params) {
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

  if (params.target !== undefined && params.target > '#' && $(document).find(params.target).length) {
    //delete params.data;
    delete params.bind;
    params._tid = params.target;
    wbapp.ajax(params, function (data) {
      var inner = '<wb>' + data.data + '</wb>';
      inner = $(inner).find(params.target).html();
      $(params.target).html(inner);
    });
  }
};

wbapp.renderClient = function (params) {
  var _data2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

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

  if (params.from !== undefined && _data2[params.from] == undefined) {
    var from = {};
    from[params.from] = _data2;
    _data2 = from;
  }

  if (wbapp.bind[params.bind] == undefined) {
    wbapp.bind[params.bind] = {};
    newbind = tid;
  }

  var html = wbapp.template[tid].html;
  wbapp.bind[params.bind][tid] = new Ractive({
    target: params.target,
    template: html,
    data: function data() {
      return _data2;
    }
  }); ///wbapp.storage(params.bind, data);

  wbapp.template[tid].params = params;
  var pagination = $(tid).find(".pagination");

  if (pagination) {
    var page = 1;
    $(pagination).data("tpl", tid);
    if (params.page) page = params.page;
    $(pagination).find(".page-item").removeClass("active");
    $(pagination).find("[data-page=\"".concat(page, "\"]")).parent(".page-item").addClass("active");
  }

  if (newbind) {
    wbapp.bind[params.bind][tid].set(_data2);
    $(document).on("bind-" + params.bind, function (e, data) {
      try {
        wbapp.bind[params.bind][tid].set(data);
      } catch (error) {
        wbapp.bind[params.bind][tid].update(data);
      }
    });
  }
};

wbapp.newId = function (separator, prefix) {
  if (separator == undefined) {
    separator = "";
  }

  var mt = explode(" ", microtime());
  var md = substr(str_repeat("0", 2) + dechex(ceil(mt[0] * 10000)), -4);
  var id = dechex(time() + rand(100, 999));

  if (prefix !== undefined && prefix > "") {
    id = prefix + separator + id + md;
  } else {
    id = "id" + id + separator + md;
  }

  return id;
};

wbapp.modalsInit = function () {
  wbapp.modalZndx == undefined ? wbapp.modalZndx = 2000 : null;
  $(document).undelegate(".modal-header", "dblclick");
  $(document).delegate(".modal-header", "dblclick", function (event) {
    var that = $(event.target);
    $(that).closest(".modal").toggleClass("modal-fullscreen");
  });

  if (document.modalDelegates == undefined) {
    document.modalDelegates = true;
    $(document).delegate(".modal", "shown.bs.modal", function () {
      $('.modal[data-zidx]').each(function () {
        var max = $(this).attr('data-zidx') * 1;
        if (max > wbapp.modalZndx) wbapp.modalZndx = max;
      });

      if ($(this).parents('.modal')) {
        $(this).appendTo($(this).parents('.modal').parent());
      }

      var that = this;
      if ($(that).find('.modal-content').css('position') == 'fixed') return;
      $(that).find('.modal-content') //      .resizable({
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
        if ($(this).data("parent") == undefined) $(this).data("parent", $(this).closest()); // нельзя переносить модальное окно, так как могут возникнуть проблемы с селектором!
        //$(this).appendTo("body");
      }

      if ($(that).attr('data-zidx') == undefined) {
        $(that).css("z-index", wbapp.modalZndx).attr('data-zidx', wbapp.modalZndx);

        if ($(that).attr("data-backdrop") !== "false") {
          $(".modal-backdrop:not([data-zidx])").css("z-index", wbapp.modalZndx - 5).attr('data-zidx', wbapp.modalZndx - 5);
        }
      }

      window.dispatchEvent(new Event('resize'));
    });
    $(document).delegate(".modal", "DOMSubtreeModified", function () {
      if ($(this).find('.modal-content').height() > $(window).height() - 80) {
        $(this).addClass('h-100');
      } else {
        $(this).removeClass('h-100');
      }
    });
    $(document).delegate(".modal", 'hidden.bs.modal', function () {
      var zndx = $(this).css("z-index") * 1;
      $(".modal-backdrop[style*='z-index: " + (zndx - 5) + "']").remove();
    });
    $(document).delegate(".modal [data-dismiss]", "click", function (event) {
      event.preventDefault();
      var zndx = $(this).attr("data-dismiss") * 1;
      var modal = $(document).find(".modal[data-zidx='" + $(this).attr("data-dismiss") + "']");
      modal.modal("hide"); // $(document).find(".modal-backdrop[data-dismiss='" + (zndx - 5) + "']").remove();
    });
    $(document).delegate(".modal", "hidden.bs.modal", function (event) {
      var that = this;

      if ($(this).hasClass("removable")) {
        $(that).modal("dispose").remove();
      } else {
        $(this).appendTo($(this).data("parent"));
      }
    });
  }

  $(document).off("wb-ajax-done");
  $(document).on("wb-ajax-done", function () {
    wbapp.console("Trigger: wb-ajax-done");

    if (wbapp !== undefined) {
      wbapp.tplInit();
      wbapp.wbappScripts(); //wbapp.pluginsInit();

      wbapp.lazyload();
    }

    if ($(".modal.show:not(:visible),.modal[data-show=true]:not(:visible)").length) $(".modal.show:not(:visible),.modal[data-show=true]:not(:visible)").modal("show");
    if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
  });
};

wbapp.getModal = function () {
  var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
  var modal = $(document).data("wbapp-modal");

  if (modal == undefined) {
    var modal = wbapp.postSync("/ajax/gettpl/snippets/modal/");
    modal = $("<div>" + modal.content + "</div>").find(".modal").clone();
    $(document).data("wbapp-modal", modal);
  }

  var zndx = wbapp.modalZndx * 1 + 10;
  wbapp.modalZndx = zndx;
  if (id !== null) $(modal).attr("id", id);
  if (zndx !== undefined) $(modal).data("zndx", zndx).attr("style", "z-index:" + zndx);
  return $(modal).clone();
};

wbapp.ajaxSync = function (ajaxObjs, fn) {
  if (!ajaxObjs) return;
  wbapp.loading();
  var data = [];
  var ajaxCount = ajaxObjs.length;

  if (fn == undefined) {
    var fn = function fn(data) {
      return data;
    };
  }

  for (var i = 0; i < ajaxCount; i++) {
    //append logic to invoke callback function once all the ajax calls are completed, in success handler.
    try {
      ajaxObjs[i].data.__token = wbapp._session.token;
    } catch (error) {
      null;
    }

    $.ajax(ajaxObjs[i]).done(function (res) {
      ajaxCount--;

      if (ajaxObjs.length > 0) {
        data.push(res);
      } else {
        data = res;
      }
    }).fail(function () {
      wbapp.unloading();
      ajaxCount--;

      if (ajaxObjs.length > 0) {
        data.push(false);
      } else {
        data = false;
      }
    }); //make ajax call
  }

  ;

  while (ajaxCount > 0) {// wait all done
  }

  wbapp.unloading();
  return fn(data);
};

wbapp.getSync = function (url) {
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  return wbapp.ajaxSync([{
    url: url,
    type: 'GET',
    async: true,
    data: data
  }])[0];
};

wbapp.postSync = function (url) {
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  return wbapp.ajaxSync([{
    url: url,
    type: 'POST',
    async: false,
    data: data
  }])[0];
};

wbapp.session = function (e) {
  if (wbapp._session == undefined) wbapp._session = wbapp.postSync("/ajax/getsess/");
  wbapp.trigger('wb-getsess', e, wbapp._session);
  return wbapp._session;
};

wbapp.settings = function (e) {
  if (wbapp._settings == undefined) wbapp._settings = wbapp.postSync("/ajax/getsett/");
  wbapp.trigger('wb-getsett', e, wbapp._settings);
  return wbapp._settings;
};

wbapp.console = function (text) {
  if (wbapp._settings == undefined || wbapp._settings.devmode == 'on') {
    console.log(text);
  }
};

wbapp.loadScripts = function () {
  var scripts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
  var trigger = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var func = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  if (wbapp.loadedScripts == undefined) wbapp.loadedScripts = [];
  var ready = [];
  var stop = 0;
  var count = scripts.length;
  scripts.forEach(function (src, i) {
    //    let name = src.split("/");
    //    name = name[name.length-1];
    if (wbapp.devmode > 0 && src.indexOf('?') == -1) src += '?' + wbapp.devmode;
    var name = src;

    if (wbapp.loadedScripts.indexOf(src) !== -1) {
      wbapp.console("Script loaded: " + name);
      stop += 1;

      if (stop >= count) {
        if (trigger > '') {
          $(document).find("script#" + trigger + "-remove").remove();
          $(document).trigger(trigger);
        }

        if (func !== null) return func(scripts);
      }
    } else {
      var script = document.createElement('script');
      script.src = src;
      script.async = false;

      script.onload = function () {
        wbapp.loadedScripts.push(name);
        wbapp.console("Script loaded: " + name);
        stop += 1;

        if (stop >= count) {
          if (trigger > '') {
            $(document).find("script#" + trigger + "-remove").remove();
            $(document).trigger(trigger);
          }

          if (func !== null) return func(scripts);
        }
      };

      document.head.appendChild(script);
    }
  });
};

wbapp.loadStyles = function _callee4() {
  var styles,
      trigger,
      func,
      i,
      _args4 = arguments;
  return regeneratorRuntime.async(function _callee4$(_context4) {
    while (1) {
      switch (_context4.prev = _context4.next) {
        case 0:
          styles = _args4.length > 0 && _args4[0] !== undefined ? _args4[0] : [];
          trigger = _args4.length > 1 && _args4[1] !== undefined ? _args4[1] : null;
          func = _args4.length > 2 && _args4[2] !== undefined ? _args4[2] : null;
          if (wbapp.loadedStyles == undefined) wbapp.loadedStyles = [];
          i = 0;
          styles.forEach(function (src) {
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
              var style = document.createElement('link');
              wbapp.loadedStyles.push(src);
              style.href = src;
              style.rel = "stylesheet";
              style.type = "text/css";
              style.async = true;

              style.onload = function () {
                i++;

                if (i >= styles.length) {
                  if (func !== null) return func(styles);

                  if (trigger !== null) {
                    $(document).find("script#" + trigger + "-remove").remove();
                    $(document).trigger(trigger);
                    wbapp.console("Trigger: " + trigger);
                  }
                }
              };

              document.head.appendChild(style);
            }
          });

        case 6:
        case "end":
          return _context4.stop();
      }
    }
  });
};

wbapp.loadPreload = function _callee5() {
  var trigger,
      func,
      preloads,
      preload_max,
      preload_count,
      preload_check,
      _args5 = arguments;
  return regeneratorRuntime.async(function _callee5$(_context5) {
    while (1) {
      switch (_context5.prev = _context5.next) {
        case 0:
          trigger = _args5.length > 0 && _args5[0] !== undefined ? _args5[0] : null;
          func = _args5.length > 1 && _args5[1] !== undefined ? _args5[1] : null;
          preloads = {};
          $('link[rel=preload][as][href]').each(function () {
            if (preloads[$(this).attr('as')] == undefined) {
              preloads[$(this).attr('as')] = [];
            }

            preloads[$(this).attr('as')].push($(this).attr('href'));
          });
          preload_max = 0;
          preload_count = 0;
          if (preloads.script.length > 0) preload_max++;
          if (preloads.style.length > 0) preload_max++;

          preload_check = function preload_check() {
            if (preload_count == preload_max) {
              if (func !== null) return func(styles);

              if (trigger !== null) {
                $(document).trigger(trigger);
                wbapp.console("Trigger: " + trigger);
              }

              wbapp.trigger('ready-all');
            }
          };

          wbapp.loadScripts(preloads.script, 'preloaded-js', function () {
            preload_count++;
            preload_check();
          });
          wbapp.loadStyles(preloads.style, 'preloaded-css', function () {
            preload_count++;
            preload_check();
          });

        case 11:
        case "end":
          return _context5.stop();
      }
    }
  });
};

wbapp.on = function _callee6(trigger) {
  var func,
      _args6 = arguments;
  return regeneratorRuntime.async(function _callee6$(_context6) {
    while (1) {
      switch (_context6.prev = _context6.next) {
        case 0:
          func = _args6.length > 1 && _args6[1] !== undefined ? _args6[1] : null;
          if (func == null) func = function func() {
            return true;
          };
          $(document).on(trigger, func);

        case 3:
        case "end":
          return _context6.stop();
      }
    }
  });
};

wbapp.trigger = function (trigger) {
  var event = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  wbapp.console('Trigger: ' + trigger);

  if (event == null) {
    $(document).trigger(trigger, data);
  } else {
    $(document).trigger(trigger, event, data); //$(event).trigger(trigger, data);
  }
};

wbapp.furl = function (str) {
  str = str.replace(/[^\/а-яА-Яa-zA-Z0-9_-]{1,}/gm, "_");
  str = str.replace(/[__]{1,}/gm, "_");
  str = wbapp.transilt(str);
  return str;
};

wbapp.transilt = function (word) {
  var answer = "";
  var a = {};
  var i;
  a["Ё"] = "YO";
  a["Й"] = "I";
  a["Ц"] = "TS";
  a["У"] = "U";
  a["К"] = "K";
  a["Е"] = "E";
  a["Н"] = "N";
  a["Г"] = "G";
  a["Ш"] = "SH";
  a["Щ"] = "SCH";
  a["З"] = "Z";
  a["Х"] = "H";
  a["Ъ"] = "'";
  a["ё"] = "yo";
  a["й"] = "i";
  a["ц"] = "ts";
  a["у"] = "u";
  a["к"] = "k";
  a["е"] = "e";
  a["н"] = "n";
  a["г"] = "g";
  a["ш"] = "sh";
  a["щ"] = "sch";
  a["з"] = "z";
  a["х"] = "h";
  a["ъ"] = "'";
  a["Ф"] = "F";
  a["Ы"] = "I";
  a["В"] = "V";
  a["А"] = "a";
  a["П"] = "P";
  a["Р"] = "R";
  a["О"] = "O";
  a["Л"] = "L";
  a["Д"] = "D";
  a["Ж"] = "ZH";
  a["Э"] = "E";
  a["ф"] = "f";
  a["ы"] = "i";
  a["в"] = "v";
  a["а"] = "a";
  a["п"] = "p";
  a["р"] = "r";
  a["о"] = "o";
  a["л"] = "l";
  a["д"] = "d";
  a["ж"] = "zh";
  a["э"] = "e";
  a["Я"] = "Ya";
  a["Ч"] = "CH";
  a["С"] = "S";
  a["М"] = "M";
  a["И"] = "I";
  a["Т"] = "T";
  a["Ь"] = "'";
  a["Б"] = "B";
  a["Ю"] = "YU";
  a["я"] = "ya";
  a["ч"] = "ch";
  a["с"] = "s";
  a["м"] = "m";
  a["и"] = "i";
  a["т"] = "t";
  a["ь"] = "'";
  a["б"] = "b";
  a["ю"] = "yu";

  for (i = 0; i < word.length; ++i) {
    answer += a[word[i]] === undefined ? word[i] : a[word[i]];
  }

  return answer;
};

wbapp.check_email = function (email) {
  if (email.match(/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/i)) {
    return true;
  } else {
    return false;
  }
};

var array_column = function array_column(list, column, indice) {
  var result, key;

  if (list.length) {
    if (typeof indice !== "undefined") {
      result = {};

      for (key in list) {
        if (typeof list[key][column] !== 'undefined' && typeof list[key][indice] !== 'undefined') result[list[key][indice]] = list[key][column];
      }
    } else {
      result = [];

      for (key in list) {
        if (typeof list[key][column] !== 'undefined') result.push(list[key][column]);
      }
    }
  }

  return result;
};

var alive = setInterval(function () {
  wbapp.alive();
}, 84600);

wbapp.init = function () {
  wbapp.wbappScripts();
  wbapp.tplInit();
  wbapp.modalsInit();
};

wbapp.print = function (pid) {
  var divToPrint = document.getElementById(pid);
  var newWin = window.open('', 'Print-Window');
  newWin.document.open();
  newWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="/engine/lib/bootstrap/css/bootstrap.min.css"></head><body onload="window.print()">' + divToPrint.innerHTML + '</body></html>');
  newWin.document.close();
  setTimeout(function () {
    newWin.close();
  }, 10);
};

function is_object(val) {
  return val instanceof Object;
}

function is_callable(t, n, o) {
  var e = "",
      r = {},
      i = "";
  if ("string" == typeof t) r = window, e = i = t;else {
    if (!(t instanceof Array && 2 === t.length && "object" == _typeof(t[0]) && "string" == typeof t[1])) return !1;
    r = t[0], i = t[1], e = (r.constructor && r.constructor.name) + "::" + i;
  }
  return !(!n && "function" != typeof r[i]) && (o && (window[o] = e), !0);
}

var loadPhpjs = function loadPhpjs() {
  if (_tmpphp == false) {
    _tmpphp = true;
    var phpjs = document.createElement('script');
    phpjs.src = "/engine/js/php.js";
    phpjs.async = false;
    phpjs.defer = true;

    phpjs.onload = function () {
      wbapp.start();
    };

    document.head.appendChild(phpjs);
  }
};

var loadJquery = function loadJquery() {
  if (_tmpjq == false) {
    _tmpjq = true;
    var jquery = document.createElement('script');
    jquery.src = '/engine/js/jquery.min.js';
    jquery.async = false;
    jquery.defer = true;

    jquery.onload = function () {
      wbapp.start();
    };

    document.head.appendChild(jquery);
  }
};
//# sourceMappingURL=wbapp.dev.js.map