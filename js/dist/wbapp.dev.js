"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var wbapp = new Object();
wbapp.tmp = {};
var _tmpphp = false;
var _tmpjq = false;
setTimeout(function _callee() {
  var loader, get_cookie, devmode;
  return regeneratorRuntime.async(function _callee$(_context) {
    while (1) {
      switch (_context.prev = _context.next) {
        case 0:
          wbapp.loader = true;
          loader = document.getElementById("loader");
          typeof loader !== 'undefined' && loader !== null ? wbapp.delay = 20 : wbapp.delay = 10;

          get_cookie = function get_cookie(name) {
            var value = "; ".concat(document.cookie);
            var parts = value.split("; ".concat(name, "="));
            if (parts.length === 2) return parts.pop().split(';').shift();
          };

          devmode = get_cookie('devmode');
          devmode ? devmode = '' : null;
          wbapp.devmode = devmode;
          wbapp.evClick = 'tap click';
          wbapp.start();

        case 9:
        case "end":
          return _context.stop();
      }
    }
  });
}, 5);

wbapp.start = function _callee5() {
  var data;
  return regeneratorRuntime.async(function _callee5$(_context5) {
    while (1) {
      switch (_context5.prev = _context5.next) {
        case 0:
          if (!(typeof str_replace === 'undefined')) {
            _context5.next = 3;
            break;
          }

          loadPhpjs();
          return _context5.abrupt("return");

        case 3:
          if (!(typeof $ === 'undefined')) {
            _context5.next = 6;
            break;
          }

          loadJquery();
          return _context5.abrupt("return");

        case 6:
          data = {};
          wbapp.bind = {};
          wbapp.ui = {
            spinner_sm: '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>',
            spinner_sm_grow: '<span class="spinner-grow spinner-grow-sm" role="status"></span>'
          };
          wbapp.session();
          wbapp.settings();

          $.fn.submitForm = function () {
            return this.submit(function (ev) {
              ev.stopPropagation();
              ev.preventDefault();
              var form = this;
              var error = false;
              var action = $(form).attr('action');
              var method = $(form).attr('method').toUpperCase();
              var data = new FormData(form);
              data.pathname = document.location.pathname;

              try {
                fetch(action, {
                  method: method,
                  body: data
                }).then(function (response) {
                  if (response.ok) {
                    return response = response.json();
                  } else {
                    return {
                      error: true
                    };
                  }
                }).then(function (data) {
                  if (data.error) {
                    $(form).trigger('wb-submit-fail', data);
                  } else {
                    $(form).trigger('wb-submit-success', data);
                  }
                });
              } catch (error) {
                $(form).trigger('wb-submit-fail', [error]);
              }
            });
          };

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

          $.fn.runScripts = function _callee2() {
            return regeneratorRuntime.async(function _callee2$(_context2) {
              while (1) {
                switch (_context2.prev = _context2.next) {
                  case 0:
                    $(this).find("script").each(function () {
                      var type = $(this).attr("type");

                      if (type !== "text/locale" && type !== "text/template") {
                        eval($(this).text());
                        if ($(this).attr("removable") !== undefined) $(this).remove();
                      }
                    });

                  case 1:
                  case "end":
                    return _context2.stop();
                }
              }
            }, null, this);
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
              var value = val["value"];
              var name = val["name"];
              data[name] = value;
              var $textarea = $(form).find("textarea[name='" + name + "']");

              if ($textarea.length && $textarea.is("[type=json]")) {
                var _val = $textarea.val();

                var _text = $textarea.text();

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
                  eval("data[name] = ".concat($textarea.data('iconv'), "(value)"));
                } else {
                  var _value = $textarea.val();

                  var text = $textarea.text();

                  if (_value == 'null') {
                    data[name] = text;
                  } else {
                    data[name] = _value;
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
            var attaches = $(form).find('input[name][type=file]');
            var reader = new FileReader();
            $.each(attaches, function () {
              var file = $(this)[0].files[0];

              if (file) {
                var that = this;
                reader.readAsDataURL(file);

                reader.onload = function () {
                  data[that.name] = reader.result.toString(); //base64encoded string
                };
              }
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
            }); // fix dot notation

            var obj = {};
            $.each(data, function (name, value) {
              if (strpos(name, ".")) {
                var chunks = explode(".", name);
                var idx = "";
                $.each(chunks, function (i, key) {
                  if (i < chunks.length) {
                    idx == "" ? idx = key : idx += "." + key;
                    eval("if (obj.".concat(idx, " == undefined) obj.").concat(idx, " = {}"));
                  }
                });
                eval("obj.".concat(name, " = value"));
              } else {
                eval("obj.".concat(name, " = value"));
              }
            });
            return obj;
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

          setTimeout(function () {
            var load = [];
            if (typeof Vue == 'undefined') load.push("/engine/js/vue.min.js");
            if (typeof topbar == 'undefined') load.push("/engine/js/topbar.min.js");
            if (typeof jQuery.ui == 'undefined') load.push("/engine/js/jquery-ui.min.js");
            if (typeof Ractive == 'undefined') load.push("/engine/js/ractive.js");
            load.push("/engine/js/jquery.tap.js");
            if (typeof lazyload == 'undefined') load.push("/engine/js/lazyload.js");
            wbapp.loadScripts(load, "wbapp-go", function _callee4() {
              return regeneratorRuntime.async(function _callee4$(_context4) {
                while (1) {
                  switch (_context4.prev = _context4.next) {
                    case 0:
                      Ractive.DEBUG = false;
                      wbapp.eventsInit();
                      wbapp.wbappScripts();
                      wbapp.tplInit();
                      wbapp.ajaxAuto();
                      wbapp.lazyload();
                      wbapp.modalsInit();
                      wbapp.fileinpInit();
                      wbapp.wbappScripts(); //$(document).scrollTop(0);

                      $(document).on("wb-ajax-done", function _callee3() {
                        return regeneratorRuntime.async(function _callee3$(_context3) {
                          while (1) {
                            switch (_context3.prev = _context3.next) {
                              case 0:
                                wbapp.console("Trigger: wb-ajax-done");

                                if (wbapp !== undefined) {
                                  wbapp.tplInit();
                                  wbapp.wbappScripts(); //wbapp.pluginsInit();

                                  wbapp.lazyload();
                                }

                                if ($(".modal.show:not(:visible),.modal[data-show=true]:not(:visible)").length) $(".modal.show:not(:visible),.modal[data-show=true]:not(:visible)").modal("show");
                                if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();

                              case 4:
                              case "end":
                                return _context3.stop();
                            }
                          }
                        });
                      });
                      wbapp.trigger("wb-ready");

                    case 11:
                    case "end":
                      return _context4.stop();
                  }
                }
              });
            });
          }, wbapp.delay);

        case 19:
        case "end":
          return _context5.stop();
      }
    }
  });
};

wbapp.ractive = function () {
  var target = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'body';
  var tpl = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'empty';
  var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
  return new Ractive({
    'target': target,
    'template': tpl,
    'data': data
  });
};

wbapp.fileinpInit = function () {
  var getBase64 = function getBase64(file) {
    return new Promise(function (resolve, reject) {
      var reader = new FileReader();
      reader.readAsDataURL(file);

      reader.onload = function () {
        //let encoded = reader.result.toString().replace(/^data:(.*,)?/, '');
        var encoded = reader.result.toString();

        if (encoded.length % 4 > 0) {
          encoded += '='.repeat(4 - encoded.length % 4);
        }

        resolve(encoded);
      };

      reader.onerror = function (error) {
        return reject(error);
      };
    });
  };

  $(document).delegate('input[type=file][data-base64]', 'change', function _callee6() {
    return regeneratorRuntime.async(function _callee6$(_context6) {
      while (1) {
        switch (_context6.prev = _context6.next) {
          case 0:
            if (!($(this).val() == '')) {
              _context6.next = 4;
              break;
            }

            $(this).data('base64', '');
            _context6.next = 9;
            break;

          case 4:
            _context6.t0 = $(this);
            _context6.next = 7;
            return regeneratorRuntime.awrap(getBase64($(this).prop('files')[0]));

          case 7:
            _context6.t1 = _context6.sent;

            _context6.t0.data.call(_context6.t0, 'base64', _context6.t1);

          case 9:
          case "end":
            return _context6.stop();
        }
      }
    }, null, this);
  });
};

wbapp.confirm = function () {
  var title = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
  var text = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

  /*
  wbapp.confirm()
  .on('confirm', function () {
      alert(1);
  })
  .on('cancel', function () {
      alert(0)
  });
  */
  var modal;

  if (wbapp.confirmModal == undefined) {
    modal = wbapp.getForm('common', 'confirm', {
      data: {
        'confirm': true
      }
    });
    wbapp.confirmModal = modal.result;
  } else {
    modal = wbapp.confirmModal;
  }

  var $modal = $(wbapp.confirmModal);
  var confirm = false;
  title !== null ? title = $modal.find('.modal-title').text(title) : null;
  text !== null ? text = $modal.find('.modal-body').text(text) : null;
  $modal.modal();
  $modal.undelegate('.btn.confirm', wbapp.evClick);
  $modal.delegate('.btn.confirm', wbapp.evClick, function () {
    confirm = true;
    $modal.trigger('confirm').modal('hide');
  });
  $modal.on('hide.bs.modal', function () {
    confirm == false ? $modal.trigger('cancel') : null;
  });
  return $modal;
};

wbapp.lazyload = function _callee7() {
  return regeneratorRuntime.async(function _callee7$(_context7) {
    while (1) {
      switch (_context7.prev = _context7.next) {
        case 0:
          /*
          $("[data-src]:not([src])").each(function() {
              let link = document.createElement('link');
              link.rel = "preload";
              link.as = "image";
              link.href = $(this).attr('data-src');
              document.head.appendChild(link);
          });
          */
          $("[data-src]:not([src])").attr('loading', 'lazy');
          $("[data-src]:not([src])").lazyload();

        case 2:
        case "end":
          return _context7.stop();
      }
    }
  });
};

wbapp.eventsInit = function _callee12() {
  return regeneratorRuntime.async(function _callee12$(_context12) {
    while (1) {
      switch (_context12.prev = _context12.next) {
        case 0:
          $(document).undelegate("[data-ajax]:not(input,select)", wbapp.evClick);
          $(document).delegate("[data-ajax]:not(input,select)", wbapp.evClick, function _callee8(e, tid) {
            var params, id, href;
            return regeneratorRuntime.async(function _callee8$(_context8) {
              while (1) {
                switch (_context8.prev = _context8.next) {
                  case 0:
                    if ($(this).is("input,select")) {
                      _context8.next = 17;
                      break;
                    }

                    params = wbapp.parseAttr($(this).attr("data-ajax"));

                    if (!(params.url == undefined && typeof params == 'string')) {
                      _context8.next = 10;
                      break;
                    }

                    // ajax string only
                    params.url = $(this).attr("data-ajax");

                    if (!$(this).parents('form').length) {
                      _context8.next = 10;
                      break;
                    }

                    id = wbapp.newId('_', 'ax');

                    if ($(this).parents('form').attr('id') == undefined) {
                      $(this).parents('form').attr('id', id);
                    }

                    params.form = 'form#' + $(this).parents('form').attr('id');

                    if ($(params.form).verify()) {
                      _context8.next = 10;
                      break;
                    }

                    return _context8.abrupt("return", false);

                  case 10:
                    if ($(this).is('[data-toggle=tooltip]')) {
                      $(this).tooltip('hide');
                    } // fix tooltips


                    params._event = e;
                    if (tid !== undefined) params._tid = tid;
                    wbapp.ajax(params);
                    wbapp.trigger("data-ajax", params);
                    href = $(this).attr('href');

                    if (href !== undefined && href.substr(0, 1) == '#') {
                      document.location.anchor = $(this).attr('href');
                    }

                  case 17:
                  case "end":
                    return _context8.stop();
                }
              }
            }, null, this);
          });
          $(document).undelegate("input[data-ajax],select[data-ajax]", "change");
          $(document).delegate("input[data-ajax],select[data-ajax]", "change", function _callee9(e, tid) {
            var search, params;
            return regeneratorRuntime.async(function _callee9$(_context9) {
              while (1) {
                switch (_context9.prev = _context9.next) {
                  case 0:
                    e.preventDefault();
                    search = $(this).attr("data-ajax");
                    search = str_replace('$value', $(this).val(), search);
                    params = wbapp.parseAttr(search);
                    params._event = e;
                    if (tid !== undefined) params._tid = tid;
                    wbapp.ajax(params);
                    return _context9.abrupt("return", false);

                  case 8:
                  case "end":
                    return _context9.stop();
                }
              }
            }, null, this);
          });
          $(document).undelegate("input[type=search][data-ajax]", "keyup");
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
          $(document).undelegate("input[type=checkbox]", "click");
          $(document).delegate("input[type=checkbox]", "click", function _callee10() {
            return regeneratorRuntime.async(function _callee10$(_context10) {
              while (1) {
                switch (_context10.prev = _context10.next) {
                  case 0:
                    if ($(this).prop("checked") == false) {
                      $(this).removeAttr("checked");
                    } else {
                      $(this).prop("checked", true);
                      $(this).attr("checked", true);
                    }

                  case 1:
                  case "end":
                    return _context10.stop();
                }
              }
            }, null, this);
          });
          $(document).undelegate("[rows=auto]", "keydown keyup focus");
          $(document).delegate("[rows=auto]", "keydown keyup focus", function () {
            this.style.overflow = "hidden";
            this.style.height = "1px";
            this.style.height = this.scrollHeight + "px";
          });
          $(document).undelegate("[rows=auto]", "focusout");
          $(document).delegate("[rows=auto]", "focusout", function _callee11() {
            return regeneratorRuntime.async(function _callee11$(_context11) {
              while (1) {
                switch (_context11.prev = _context11.next) {
                  case 0:
                    this.style.overflow = "hidden";
                    this.style.height = "auto";

                  case 2:
                  case "end":
                    return _context11.stop();
                }
              }
            }, null, this);
          });

        case 12:
        case "end":
          return _context12.stop();
      }
    }
  });
};

wbapp.ajaxAuto = function _callee14() {
  var func,
      _args14 = arguments;
  return regeneratorRuntime.async(function _callee14$(_context14) {
    while (1) {
      switch (_context14.prev = _context14.next) {
        case 0:
          func = _args14.length > 0 && _args14[0] !== undefined ? _args14[0] : null;
          $(document).find("[data-ajax][auto]").each(function _callee13() {
            return regeneratorRuntime.async(function _callee13$(_context13) {
              while (1) {
                switch (_context13.prev = _context13.next) {
                  case 0:
                    $(this).trigger("click");
                    if ($(this).attr('once') !== undefined) $(this).removeAttr('data-ajax').removeAttr('once');
                    $(this).removeAttr("auto");

                  case 3:
                  case "end":
                    return _context13.stop();
                }
              }
            }, null, this);
          });

        case 2:
        case "end":
          return _context14.stop();
      }
    }
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

wbapp.alive = function _callee16() {
  return regeneratorRuntime.async(function _callee16$(_context16) {
    while (1) {
      switch (_context16.prev = _context16.next) {
        case 0:
          $.post("/ajax/alive", {}, function _callee15(data) {
            return regeneratorRuntime.async(function _callee15$(_context15) {
              while (1) {
                switch (_context15.prev = _context15.next) {
                  case 0:
                    if (data.result == false || data.result == undefined) {
                      wbapp.console("Trigger: session_close");
                      $(document).trigger("session_close");
                      clearInterval(alive);
                    }

                  case 1:
                  case "end":
                    return _context15.stop();
                }
              }
            });
          });

        case 1:
        case "end":
          return _context16.stop();
      }
    }
  });
};

wbapp.store = function () {
  var storage = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
  var key = arguments.length > 1 ? arguments[1] : undefined;
  var value = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : undefined;
  var binds = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : true;
  if (storage == null) storage = localStorage;
  key = str_replace('-', '__', key);

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
    var data = storage.getItem(_list[0]);

    if (data !== null) {
      data = JSON.parse(data);
    } else {
      data = {};
    }

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
        data = storage.getItem(key);

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

      _typeof(data) == 'object' ? null : data = {};

      try {
        eval("branch = data.".concat(key));
        if (_typeof(branch) == 'object') branch = Object.assign({}, branch);
        if (i + 1 < last && _typeof(branch) !== "object") eval("data.".concat(key, " = {}"));
      } catch (err) {
        data ? null : data = {};
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

    storage.setItem(list[0], json_encode(data));

    var checkBind = function checkBind(bind, key) {
      if (bind == key) return true;
      if (key.substr(0, bind.length) == bind) return true;
      return false;
    };

    if (binds == true) {
      $.each(wbapp.template, function (i, tpl) {
        if (tpl.params.bind !== undefined && tpl.params.bind !== null && checkBind(tpl.params.bind, key) && tpl.params.render !== undefined && tpl.params.render == 'client') {
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

wbapp.storage = function (key) {
  var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;
  var binds = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
  return wbapp.store(localStorage, key, value, binds);
};

wbapp.data = function (key) {
  var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;
  var binds = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
  return wbapp.store(sessionStorage, key, value, binds);
};

wbapp.save = function _callee17(obj, params) {
  var func,
      that,
      data,
      form,
      result,
      method,
      _args17 = arguments;
  return regeneratorRuntime.async(function _callee17$(_context17) {
    while (1) {
      switch (_context17.prev = _context17.next) {
        case 0:
          func = _args17.length > 2 && _args17[2] !== undefined ? _args17[2] : null;
          wbapp.console("Trigger: wb-save-start");
          $(obj).trigger("wb-save-start", params);
          that = this;
          method = "POST";
          params.form !== undefined ? form = $(params.form) : form = $(obj).parents("form");

          if (!($(form).length && !$(form).verify())) {
            _context17.next = 9;
            break;
          }

          $(obj).trigger("wb-save-error", {
            params: params
          });
          return _context17.abrupt("return", false);

        case 9:
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
              wbapp.updateView(params, data);
              if (data._id !== undefined) $(obj).data('saved-id', data._id);
              wbapp.console("Trigger: wb-save-done");
              $(obj).trigger("wb-save-done", {
                params: params,
                data: data
              });
              wbapp.console("Trigger: wb-form-save " + params.form);
              $(params.form).trigger("wb-form-save", {
                params: params,
                data: data
              });
              if (func !== null) return func(data);
            });
          }, 50);

        case 12:
        case "end":
          return _context17.stop();
      }
    }
  }, null, this);
};

wbapp.updateView = function () {
  var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  console.log('Update view');
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
      var prms = Object.assign({}, tpl.params);
      var post = null;
      prms.route == undefined ? prms.route = [] : null;
      if (prms.bind !== undefined && prms.update == undefined) prms.update = prms.bind;
      if (prms._params !== undefined && prms._params.bind !== undefined) prms.update = prms._params.bind;
      if (prms._params !== undefined && prms._params.update !== undefined) prms.update = prms._params.update;
      if (prms.route !== undefined && prms.route._post !== undefined) post = prms.route._post;
      if (prms.url == undefined && prms.route.url !== undefined) prms.url = prms.route.url;

      if (params.update == prms.update) {
        var target = prms.target;

        if (post && prms.url !== undefined) {
          fetch(prms.url, {
            method: 'POST',
            body: post
          }).then(function (resp) {
            return resp.text();
          }).then(function (res) {
            var html = $(res).find(target).html();
            $(document).find(target).html(html);
            wbapp.refresh();
          });
        } else {
          wbapp.renderServer(prms, data);
        }
      }
    }
  });
};

wbapp.updateInputs = function () {
  $(document).find(":checkbox").each(function _callee18() {
    return regeneratorRuntime.async(function _callee18$(_context18) {
      while (1) {
        switch (_context18.prev = _context18.next) {
          case 0:
            if ($(this).attr("value") == "on") {
              $(this).attr("checked", true).prop("checked", true);
            } else {
              $(this).attr("checked", false).prop("checked", false);
            }

          case 1:
          case "end":
            return _context18.stop();
        }
      }
    }, null, this);
  });
};

wbapp.wbappScripts = function _callee19() {
  var done;
  return regeneratorRuntime.async(function _callee19$(_context19) {
    while (1) {
      switch (_context19.prev = _context19.next) {
        case 0:
          done = [];
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

            var that = this;

            if (src !== null && src > '') {
              var xhr = new XMLHttpRequest();
              xhr.open('GET', src, true);

              xhr.onload = function () {
                //eval(xhr.responseText);
                $(that).after('<script type="text/javascript">' + xhr.responseText + '</script>').remove();
              };

              xhr.send();
            } else {
              var script = $(this).text();
              var hash = md5(script);

              if (!in_array(hash, done)) {
                $(that).after('<script type="text/javascript">' + script + '</script>').remove(); //eval(script);

                done.push(hash);
              }
            }

            if ($(this).attr("remove") !== undefined) $(this).remove();
            if ($(this).attr("removable") !== undefined) $(this).remove();
          });

        case 2:
        case "end":
          return _context19.stop();
      }
    }
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

wbapp.post = function _callee20(url) {
  var data,
      func,
      _args20 = arguments;
  return regeneratorRuntime.async(function _callee20$(_context20) {
    while (1) {
      switch (_context20.prev = _context20.next) {
        case 0:
          data = _args20.length > 1 && _args20[1] !== undefined ? _args20[1] : {};
          func = _args20.length > 2 && _args20[2] !== undefined ? _args20[2] : null;

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
          $.post(url, data).then(function (res) {
            wbapp.unloading();
            if (func !== null) return func(res);
          });

        case 5:
        case "end":
          return _context20.stop();
      }
    }
  });
};

wbapp.get = function _callee21(url) {
  var data,
      func,
      _args21 = arguments;
  return regeneratorRuntime.async(function _callee21$(_context21) {
    while (1) {
      switch (_context21.prev = _context21.next) {
        case 0:
          data = _args21.length > 1 && _args21[1] !== undefined ? _args21[1] : {};
          func = _args21.length > 2 && _args21[2] !== undefined ? _args21[2] : null;

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
          return _context21.stop();
      }
    }
  });
};

wbapp.ajax = function _callee25(params) {
  var func,
      opts,
      token,
      _opts,
      target,
      clearval,
      _args25 = arguments;

  return regeneratorRuntime.async(function _callee25$(_context25) {
    while (1) {
      switch (_context25.prev = _context25.next) {
        case 0:
          func = _args25.length > 1 && _args25[1] !== undefined ? _args25[1] : null;

          if (!(!params.url && !params.tpl && !params.target)) {
            _context25.next = 3;
            break;
          }

          return _context25.abrupt("return");

        case 3:
          opts = Object.assign({}, params);
          delete opts._event;

          if (params.form !== undefined) {
            $(params.form).trigger("wb-ajax-start", params);
          } else if (params.target !== undefined) {
            $(params.target).trigger("wb-ajax-start", params);
          }

          wbapp.trigger("wb-ajax-start", params);

          if (!(params.request_type == 'remove_item')) {
            _context25.next = 11;
            break;
          }

          wbapp.post(params.url, opts, function _callee22(data) {
            return regeneratorRuntime.async(function _callee22$(_context22) {
              while (1) {
                switch (_context22.prev = _context22.next) {
                  case 0:
                    if (data._removed !== undefined && data._removed == true && params.update !== undefined) {
                      $.each(wbapp.template, function (i, tpl) {
                        var rend = false;
                        if (tpl.params !== undefined && tpl.params.bind !== undefined && tpl.params.bind == params.update) rend = true;
                        if (tpl.params !== undefined && tpl.params._params !== undefined && tpl.params._params.bind !== undefined && tpl.params._params.bind == params.update) rend = true;
                        if (rend) wbapp.renderServer(tpl.params);
                      });
                    }

                  case 1:
                  case "end":
                    return _context22.stop();
                }
              }
            });
          });
          _context25.next = 45;
          break;

        case 11:
          if (!(params.url !== undefined)) {
            _context25.next = 21;
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
          wbapp.post(params.url, _opts, function _callee24(data) {
            return regeneratorRuntime.async(function _callee24$(_context24) {
              while (1) {
                switch (_context24.prev = _context24.next) {
                  case 0:
                    wbapp.unloading();

                    if (!(count(data) == 2 && data.error !== undefined && data.callback !== undefined)) {
                      _context24.next = 7;
                      break;
                    }

                    eval(data.callback + '(params,data)');

                    if (!(func !== null)) {
                      _context24.next = 5;
                      break;
                    }

                    return _context24.abrupt("return", func(params, data));

                  case 5:
                    _context24.next = 8;
                    break;

                  case 7:
                    if (data.callback !== undefined) {
                      eval(data.callback);
                    }

                  case 8:
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
                    wbapp.setPag(params.target, data); //wbapp.console("Trigger: wb-ajax-done");

                    if (data.result == undefined) params['data'] = data;

                    if (params.form !== undefined) {
                      $(params.form).trigger("wb-ajax-done", params);
                    } else if (params.target !== undefined) {
                      $(params.target).trigger("wb-ajax-done", params);
                    } else {
                      $(document).trigger("wb-ajax-done", params);
                    }

                    setTimeout(function _callee23() {
                      var showmod;
                      return regeneratorRuntime.async(function _callee23$(_context23) {
                        while (1) {
                          switch (_context23.prev = _context23.next) {
                            case 0:
                              showmod = $(document).find(".modal.show:not(:visible)");
                              if (showmod.length) showmod.removeClass("show").modal('show');

                            case 2:
                            case "end":
                              return _context23.stop();
                          }
                        }
                      });
                    }, 50);

                    if (!(func !== null)) {
                      _context24.next = 29;
                      break;
                    }

                    return _context24.abrupt("return", func(params, data));

                  case 29:
                  case "end":
                    return _context24.stop();
                }
              }
            });
          });
          _context25.next = 45;
          break;

        case 21:
          if (!(params.target !== undefined)) {
            _context25.next = 45;
            break;
          }

          if (wbapp.template[params.target] !== undefined) {
            target = wbapp.template[params.target].params;
          }

          if (target) {
            _context25.next = 28;
            break;
          }

          wbapp.console("Template not found: " + params.target);
          return _context25.abrupt("return");

        case 28:
          target.target = params.target;

          if ($(target.target)[0].filter == undefined) {
            try {
              $(target.target)[0].filter = wbapp.template[target.target].params._params.filter;
            } catch (error) {
              $(target.target)[0].filter = {};
            }
          }

          target.filter = $(target.target)[0].filter;
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

            if (key == 'filter_remove') {
              if (typeof val == "string") {
                val = val.trim().split(' ');
                delete target.filter[val];
              }

              if (_typeof(val) == "object") {
                $.each(val, function (i, v) {
                  delete target.filter[v];
                });
              }
            }

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

          $(target.target)[0].filter = target.filter;
          if (target._params && target._params.page !== undefined) target._params.page = 1;
          if (target._params && target._params.pages !== undefined) delete target._params.pages;
          if (target._params && target._params.count !== undefined) delete target._params.count;
          if (target.tpl !== undefined) target._params.tpl = target.tpl;
          if (target._tid == undefined) target._tid = params.target; // чтобы срабатывал вариант ответа с json

          if (target.url == undefined && target.route !== undefined && target.route.uri !== undefined) target.url = target.route.uri;
          params.clear !== undefined && params.clear == "true" ? $(document).find(target._tid).html('') : null;
          wbapp.template[params.target].params = target;

          if (target._params == undefined || target._params.length == 0) {
            void 0;
          } else {
            if (target.filter) {
              console.log(params.target);
              wbapp.template[params.target].params._params.filter = target.filter;
            }

            if (wbapp.tmp.ajax_params == undefined || wbapp.tmp.ajax_params !== target) {
              wbapp.tmp.ajax_params = target;
              wbapp.ajax(target, function () {
                delete wbapp.tmp.ajax_params;
                func;
              }); // только если переданы предыдущие параметры
            }
          }

        case 45:
        case "end":
          return _context25.stop();
      }
    }
  });
};

wbapp.refresh = function () {
  var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
  setTimeout(function _callee26() {
    return regeneratorRuntime.async(function _callee26$(_context26) {
      while (1) {
        switch (_context26.prev = _context26.next) {
          case 0:
            wbapp.wbappScripts();
            wbapp.tplInit();
            wbapp.ajaxAuto();
            wbapp.lazyload();
            wbapp.modalsInit(); //        wbapp.fileinpInit();

            wbapp.wbappScripts();
            if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
            if (data !== null) paginationfix(data);

          case 8:
          case "end":
            return _context26.stop();
        }
      }
    });
  }, 1);

  var paginationfix = function paginationfix(data) {
    var $pag;
    return regeneratorRuntime.async(function paginationfix$(_context27) {
      while (1) {
        switch (_context27.prev = _context27.next) {
          case 0:
            if (!(data.pag == undefined || data.params == undefined)) {
              _context27.next = 2;
              break;
            }

            return _context27.abrupt("return");

          case 2:
            if (!(data.params.more == undefined || data.params.more < 'more')) {
              _context27.next = 4;
              break;
            }

            return _context27.abrupt("return");

          case 4:
            if (!(data.params.target == undefined || data.params.target < '#')) {
              _context27.next = 6;
              break;
            }

            return _context27.abrupt("return");

          case 6:
            $pag = $(document).find(".pagination[data-tpl=\"".concat(data.params.target, "\"]"));

            if ($pag) {
              _context27.next = 9;
              break;
            }

            return _context27.abrupt("return");

          case 9:
            if (data.params.page >= data.params.pages) data.pag = '';

            if (data.pag > '') {
              data.pag = $(data.pag).find(".pagination[data-tpl=\"".concat(data.params.target, "\"]")).html();
            }

            $pag.html(data.pag);

          case 12:
          case "end":
            return _context27.stop();
        }
      }
    });
  };
};

wbapp.renderFilter = function (tid, filter) {
  var tpl = wbapp.tpl(tid);
  tpl.params.filter = filter;
  if (tpl.params._params !== undefined) tpl.params._params.filter = filter;
  wbapp.tpl(tid, tpl);
  wbapp.data('wbapp.filter.' + tid.substr(1), filter);
  wbapp.render(tid);
};

wbapp.storageUpdate = function _callee27(key, data) {
  var store;
  return regeneratorRuntime.async(function _callee27$(_context28) {
    while (1) {
      switch (_context28.prev = _context28.next) {
        case 0:
          store = wbapp.storage(key);
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
              } catch (err) {
                wbapp.console('Error: wbapp.storageUpdate()');
              }
            }

            wbapp.storage(key, store);
          } else {
            wbapp.storage(key, data);
          }

        case 3:
        case "end":
          return _context28.stop();
      }
    }
  });
};

wbapp.loading = function _callee28() {
  return regeneratorRuntime.async(function _callee28$(_context29) {
    while (1) {
      switch (_context29.prev = _context29.next) {
        case 0:
          if (!(wbapp.loader !== true)) {
            _context29.next = 2;
            break;
          }

          return _context29.abrupt("return");

        case 2:
          $(document).find('body').addClass('loading');

          if (typeof topbar !== 'undefined') {
            topbar.hide();
            topbar.show();
          }

        case 4:
        case "end":
          return _context29.stop();
      }
    }
  });
};

wbapp.unloading = function _callee29() {
  return regeneratorRuntime.async(function _callee29$(_context30) {
    while (1) {
      switch (_context30.prev = _context30.next) {
        case 0:
          if (!(wbapp.loader !== true)) {
            _context30.next = 2;
            break;
          }

          return _context30.abrupt("return");

        case 2:
          $(document).find('body').removeClass('loading');

          if (typeof topbar !== 'undefined') {
            topbar.hide();
          }

        case 4:
        case "end":
          return _context30.stop();
      }
    }
  });
};

wbapp.fetch = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'body';
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var ret = arguments.length > 2 ? arguments[2] : undefined;

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

wbapp.toast = function _callee30(title, text) {
  var params,
      target,
      options,
      res,
      $tpl,
      toast,
      last,
      _args31 = arguments;
  return regeneratorRuntime.async(function _callee30$(_context31) {
    while (1) {
      switch (_context31.prev = _context31.next) {
        case 0:
          params = _args31.length > 2 && _args31[2] !== undefined ? _args31[2] : {};
          target = '.content-toasts';

          if (!$(document).find(target).length) {
            $('body').prepend('<div class="content-toasts position-fixed t-0" style="z-index:999999;right:0;"></div>');
          }

          options = {
            'target': target,
            'delay': 3000
          };

          if (wbapp.template['wb.toast'] == undefined) {
            res = wbapp.getForm("snippets", "toast");
            wbapp.tpl('wb.toast', {
              html: res.result,
              params: {}
            });
          }

          $tpl = $(wbapp.tpl('wb.toast').html);
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
          toast = Ractive({
            el: options.target,
            append: true,
            template: $tpl.outer(),
            data: {
              title: title,
              text: text,
              age: ''
            },
            on: {
              complete: function complete() {
                if (params.audio) {
                  var audio = new Audio(params.audio);
                  audio.autoplay = true;
                }
              }
            }
          });
          last = $(document).find(options.target).find(".toast:last-child");

          if (last.length && last.toast !== undefined) {
            last.toast('show').off('hidden.bs.toast').on('hidden.bs.toast', function (e) {
              $(e.currentTarget).remove();
            });
          }

        case 14:
        case "end":
          return _context31.stop();
      }
    }
  });
};

wbapp.formByObj = function (selector, data) {
  var form = $(document).find(selector, 0);
  $(form)[0].clear;
  $.each(data, function _callee31(key, value) {
    return regeneratorRuntime.async(function _callee31$(_context32) {
      while (1) {
        switch (_context32.prev = _context32.next) {
          case 0:
            $(form).find("[name='" + key + "']").val(value);

          case 1:
          case "end":
            return _context32.stop();
        }
      }
    });
  });
};

wbapp.objByForm = function (form) {
  form = $(form);
  var data = $(form).serializeJson();
  return data;
};

wbapp.tplInit = function _callee33() {
  var res;
  return regeneratorRuntime.async(function _callee33$(_context34) {
    while (1) {
      switch (_context34.prev = _context34.next) {
        case 0:
          if (!wbapp.template) wbapp.template = {};

          if (wbapp.template['wb.modal'] == undefined) {
            res = wbapp.getForm("snippets", "modal");
            wbapp.tpl('wb.modal', {
              html: res.result,
              params: {}
            });
          }

          $(document).find("template:not([nowb])").each(function _callee32() {
            var tid, params, html, prms;
            return regeneratorRuntime.async(function _callee32$(_context33) {
              while (1) {
                switch (_context33.prev = _context33.next) {
                  case 0:
                    if (!(this.done !== undefined)) {
                      _context33.next = 4;
                      break;
                    }

                    return _context33.abrupt("return");

                  case 4:
                    this.done = true;

                  case 5:
                    if (tid == undefined && $(this).is("template[id]")) tid = $(this).attr("id");
                    if (tid == undefined) tid = $(this).parent().attr("id");
                    if (tid == undefined && $(this).is("[data-target]")) tid = $(this).attr("data-target");

                    if (tid == undefined) {
                      $(this).attr("id", "fe_" + wbapp.newId());
                      tid = $(this).attr("id");
                    }

                    tid = "#" + tid;
                    params = [];

                    if ($(this).attr("data-params") !== undefined) {
                      try {
                        params = wbapp.parseAttr($(this).attr("data-params"));
                        params['target'] = tid;
                      } catch (error) {
                        null;
                      }
                    }

                    if (params.filter !== undefined) {
                      wbapp.data('wbapp.filter.' + tid.substr(1), params.filter);
                    }

                    html = $(this).html();
                    html = html.replace(/<template\b[^<]*(?:(?!<\/template>)<[^<]*)*<\/template>/gi, "");
                    html = str_replace('%7B%7B', '{{', html);
                    html = str_replace('%7D%7D', '}}', html);
                    $(this).removeAttr("data-params");

                    if ($(this).attr("data-ajax") !== undefined) {
                      prms = wbapp.parseAttr($(this).attr("data-ajax"));
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

                  case 22:
                  case "end":
                    return _context33.stop();
                }
              }
            }, null, this);
          });

        case 3:
        case "end":
          return _context34.stop();
      }
    }
  });
};

wbapp.getForm = function (form, mode) {
  var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var func = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

  if (!func) {
    var res = wbapp.postSync("/ajax/getform/" + form + "/" + mode, data);
  } else {
    var res = wbapp.post("/ajax/getform/" + form + "/" + mode, data, func);
  }

  return res;
};

wbapp.getTpl = function (tpl) {
  var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var func = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

  if (!func) {
    var res = wbapp.postSync("/ajax/gettpl/".concat(tpl), data);
  } else {
    var res = wbapp.post("/ajax/gettpl/".concat(tpl), data, func);
  }

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

wbapp.render = function _callee34(tid, data) {
  var params;
  return regeneratorRuntime.async(function _callee34$(_context35) {
    while (1) {
      switch (_context35.prev = _context35.next) {
        case 0:
          if (!(tid == undefined)) {
            _context35.next = 2;
            break;
          }

          return _context35.abrupt("return");

        case 2:
          params = wbapp.template[tid].params;

          if (data == undefined) {
            data = {};
            params.bind !== undefined ? data = wbapp.storage(params.bind) : null;
            params.update !== undefined ? data = wbapp.storage(params.update) : null;
          }

          if (params.render == undefined) params.render = null; // для рендера не списковых данных

          _context35.t0 = params.render;
          _context35.next = _context35.t0 === 'client' ? 8 : _context35.t0 === 'server' ? 10 : _context35.t0 === null ? 12 : 14;
          break;

        case 8:
          wbapp.renderClient(params, data);
          return _context35.abrupt("break", 14);

        case 10:
          wbapp.renderServer(params, data);
          return _context35.abrupt("break", 14);

        case 12:
          wbapp.renderServer(params, data);
          return _context35.abrupt("break", 14);

        case 14:
          wbapp.lazyload();
          wbapp.trigger('wb-render-done', tid, data);

        case 16:
        case "end":
          return _context35.stop();
      }
    }
  });
};

wbapp.setPag = function _callee35(target, data) {
  var pagert;
  return regeneratorRuntime.async(function _callee35$(_context36) {
    while (1) {
      switch (_context36.prev = _context36.next) {
        case 0:
          $(document).find('.pagination[data-tpl="' + target + '"]').parents('nav').remove();
          pagert = $(document).find(target);
          if ($(pagert).is('li')) pagert = $(pagert).parent();
          if ($(pagert).is('tbody')) pagert = $(pagert).parents('table');
          if (data.pos == 'both' || data.pos == 'top') $(pagert).before(data.pag);
          if (data.pos == 'both' || data.pos == 'bottom') $(pagert).after(data.pag);

        case 6:
        case "end":
          return _context36.stop();
      }
    }
  });
};

wbapp.renderServer = function _callee36(params) {
  var data,
      post,
      _args37 = arguments;
  return regeneratorRuntime.async(function _callee36$(_context37) {
    while (1) {
      switch (_context37.prev = _context37.next) {
        case 0:
          data = _args37.length > 1 && _args37[1] !== undefined ? _args37[1] : {};

          if (params.target !== undefined && params.target > '#' && $(document).find(params.target).length) {
            //delete params.data;
            params.bind ? params.update = params.bind : null;
            delete params.bind;
            params._tid = params.target;
            params.url == undefined && params.ajax !== undefined ? params.url = params.ajax : null;

            try {
              post = params._route._post;
            } catch (error) {
              post = null;
            }

            if (post) {
              wbapp.post(params.url, post, function (res) {
                var html = $(res).find(params.target).html();
                $(document).find(params.target).html(html);
                wbapp.refresh();
              });
            } else {
              wbapp.ajax(params, function (data) {
                wbapp.setPag(params.target, data.data);
              });
            }
          }

        case 2:
        case "end":
          return _context37.stop();
      }
    }
  });
};

wbapp.renderClient = function _callee37(params) {
  var _data,
      tid,
      newbind,
      from,
      html,
      pagination,
      page,
      _args38 = arguments;

  return regeneratorRuntime.async(function _callee37$(_context38) {
    while (1) {
      switch (_context38.prev = _context38.next) {
        case 0:
          _data = _args38.length > 1 && _args38[1] !== undefined ? _args38[1] : {};
          newbind = false;
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

          if (!(wbapp.template[tid] == undefined)) {
            _context38.next = 7;
            break;
          }

          return _context38.abrupt("return");

        case 7:
          if (params.from !== undefined && _data[params.from] == undefined) {
            from = {};
            from[params.from] = _data;
            _data = from;
          }

          if (wbapp.bind[params.bind] == undefined) {
            wbapp.bind[params.bind] = {};
            newbind = tid;
          }

          html = wbapp.template[tid].html;
          wbapp.bind[params.bind][tid] = new Ractive({
            target: params.target,
            template: html,
            data: function data() {
              return _data;
            }
          }); ///wbapp.storage(params.bind, data);

          wbapp.template[tid].params = params;
          pagination = $(tid).find(".pagination");

          if (pagination) {
            page = 1;
            $(pagination).data("tpl", tid);
            params.page ? page = params.page : page = 1;
            $(pagination).find(".page-item").removeClass("active");
            $(pagination).find("[data-page=\"".concat(page, "\"]")).parent(".page-item").addClass("active");
          }

          if (newbind) {
            wbapp.bind[params.bind][tid].set(_data);
            $(document).off("bind-" + params.bind);
            $(document).on("bind-" + params.bind, function (e, data) {
              try {
                wbapp.bind[params.bind][tid].set(data);
              } catch (error) {
                wbapp.bind[params.bind][tid].update(data);
              }
            });
          }

        case 15:
        case "end":
          return _context38.stop();
      }
    }
  });
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

wbapp.modalsInit = function _callee40() {
  return regeneratorRuntime.async(function _callee40$(_context41) {
    while (1) {
      switch (_context41.prev = _context41.next) {
        case 0:
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

              var bh = $(this).find('.modal-body').height();

              if (bh > 0) {
                $(this).find('.modal-body .modal-h').height(bh);
              }

              window.dispatchEvent(new Event('resize'));
            });
            $(document).delegate(".modal", "DOMSubtreeModified", function _callee38() {
              return regeneratorRuntime.async(function _callee38$(_context39) {
                while (1) {
                  switch (_context39.prev = _context39.next) {
                    case 0:
                      if ($(this).find('.modal-content').height() > $(window).height() - 80) {
                        $(this).addClass('h-100');
                      } else {
                        $(this).removeClass('h-100');
                      }

                    case 1:
                    case "end":
                      return _context39.stop();
                  }
                }
              }, null, this);
            });
            $(document).delegate(".modal", 'hidden.bs.modal', function _callee39() {
              var zndx;
              return regeneratorRuntime.async(function _callee39$(_context40) {
                while (1) {
                  switch (_context40.prev = _context40.next) {
                    case 0:
                      zndx = $(this).css("z-index") * 1;
                      $(".modal-backdrop[style*='z-index: " + (zndx - 5) + "']").remove();
                      $(this).removeAttr('data-zidx');

                    case 3:
                    case "end":
                      return _context40.stop();
                  }
                }
              }, null, this);
            });
            $(document).delegate(".modal [data-dismiss]", "click", function (event) {
              event.preventDefault();
              var zndx = $(this).attr("data-dismiss") * 1;
              var modal = $(document).find(".modal[data-zidx='" + $(this).attr("data-dismiss") + "']");
              modal.modal("hide");
              $(this).removeAttr('data-zidx');
            });
            $(document).delegate(".modal", "hidden.bs.modal", function (event) {
              var that = this;

              if ($(this).hasClass("removable") || $(this).hasClass("remove")) {
                $(that).modal("dispose").remove();
              } else {
                $(this).appendTo($(this).data("parent"));
              }
            });
          }

        case 4:
        case "end":
          return _context41.stop();
      }
    }
  });
};

wbapp.getModal = function () {
  var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
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
    async: false,
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

wbapp.console = function _callee41(text) {
  return regeneratorRuntime.async(function _callee41$(_context42) {
    while (1) {
      switch (_context42.prev = _context42.next) {
        case 0:
          if (wbapp._settings == undefined || wbapp._settings.devmode == 'on') {
            console.log(text);
          }

        case 1:
        case "end":
          return _context42.stop();
      }
    }
  });
};

wbapp.loadScripts = function _callee43() {
  var scripts,
      trigger,
      func,
      ready,
      stop,
      count,
      loadedArr,
      loadingArr,
      _args44 = arguments;
  return regeneratorRuntime.async(function _callee43$(_context44) {
    while (1) {
      switch (_context44.prev = _context44.next) {
        case 0:
          scripts = _args44.length > 0 && _args44[0] !== undefined ? _args44[0] : [];
          trigger = _args44.length > 1 && _args44[1] !== undefined ? _args44[1] : null;
          func = _args44.length > 2 && _args44[2] !== undefined ? _args44[2] : null;
          if (document.loadedScripts == undefined) document.loadedScripts = [];
          if (document.loadingScripts == undefined) document.loadingScripts = [];
          ready = [];
          stop = 0;
          count = scripts.length;
          loadedArr = JSON.parse(JSON.stringify(document.loadedScripts));
          loadingArr = JSON.parse(JSON.stringify(document.loadingScripts));
          scripts.forEach(function (src, i) {
            //    let name = src.split("/");
            //    name = name[name.length-1];
            var name = src + '';
            var loaded = in_array(name, loadedArr);
            var loading = in_array(name, loadingArr);
            ;
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
              var script = document.createElement('script');
              document.loadingScripts.push(name);
              wbapp._settings.devmode == 'on' ? script.src = name + "?" + wbapp.newId() : script.src = name;
              script.async = false;

              script.onload = function _callee42() {
                var pos;
                return regeneratorRuntime.async(function _callee42$(_context43) {
                  while (1) {
                    switch (_context43.prev = _context43.next) {
                      case 0:
                        document.loadedScripts.push(name);
                        pos = document.loadingScripts.indexOf(name);
                        delete document.loadingScripts[pos];
                        wbapp.console("Script loaded: " + name);
                        stop += 1;

                        if (!(stop >= count)) {
                          _context43.next = 9;
                          break;
                        }

                        if (trigger > '') {
                          $(document).find("script#" + trigger + "-remove").remove();
                          $(document).trigger(trigger);
                        }

                        if (!(func !== null)) {
                          _context43.next = 9;
                          break;
                        }

                        return _context43.abrupt("return", func(scripts));

                      case 9:
                      case "end":
                        return _context43.stop();
                    }
                  }
                });
              };

              document.head.appendChild(script);
            }
          });

        case 11:
        case "end":
          return _context44.stop();
      }
    }
  });
};

wbapp.loadStyles = function _callee44() {
  var styles,
      trigger,
      func,
      i,
      _args45 = arguments;
  return regeneratorRuntime.async(function _callee44$(_context45) {
    while (1) {
      switch (_context45.prev = _context45.next) {
        case 0:
          styles = _args45.length > 0 && _args45[0] !== undefined ? _args45[0] : [];
          trigger = _args45.length > 1 && _args45[1] !== undefined ? _args45[1] : null;
          func = _args45.length > 2 && _args45[2] !== undefined ? _args45[2] : null;
          if (wbapp.loadedStyles == undefined) wbapp.loadedStyles = [];
          i = 0;
          styles.forEach(function (src) {
            setTimeout(function () {
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
                style.async = false;

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
            }, 1);
          });

        case 6:
        case "end":
          return _context45.stop();
      }
    }
  });
};

wbapp.loadPreload = function () {
  var trigger = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
  var func = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var preloads = {};
  $('link[rel=preload][as][href]').each(function () {
    if (preloads[$(this).attr('as')] == undefined) {
      preloads[$(this).attr('as')] = [];
    }

    preloads[$(this).attr('as')].push($(this).attr('href'));
  });
  var preload_max = 0;
  var preload_count = 0;
  if (preloads.script !== undefined && preloads.script.length > 0) preload_max++;
  if (preloads.style !== undefined && preloads.style.length > 0) preload_max++;

  var preload_check = function preload_check() {
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
};

wbapp.on = function _callee45(trigger) {
  var func,
      _args46 = arguments;
  return regeneratorRuntime.async(function _callee45$(_context46) {
    while (1) {
      switch (_context46.prev = _context46.next) {
        case 0:
          func = _args46.length > 1 && _args46[1] !== undefined ? _args46[1] : null;
          if (func == null) func = function func() {
            return true;
          };
          $(document).on(trigger, func);

        case 3:
        case "end":
          return _context46.stop();
      }
    }
  });
};

wbapp.trigger = function _callee46(trigger) {
  var event,
      data,
      _args47 = arguments;
  return regeneratorRuntime.async(function _callee46$(_context47) {
    while (1) {
      switch (_context47.prev = _context47.next) {
        case 0:
          event = _args47.length > 1 && _args47[1] !== undefined ? _args47[1] : null;
          data = _args47.length > 2 && _args47[2] !== undefined ? _args47[2] : null;
          wbapp.console('Trigger: ' + trigger);

          if (event == null) {
            $(document).trigger(trigger, data);
          } else {
            $(document).trigger(trigger, event, data); //$(event).trigger(trigger, data);
          }

        case 4:
        case "end":
          return _context47.stop();
      }
    }
  });
};

String.prototype.replaceArray = function (find, replace) {
  var replaceString = this;
  var regex;

  for (var i = 0; i < find.length; i++) {
    regex = new RegExp(find[i], "g");
    replaceString = replaceString.replace(regex, replace[i]);
  }

  return replaceString;
};

wbapp.furl = function (str) {
  str = wbapp.transilt(str);
  str = str.replace("'", '');
  str = str.replace(/[^\/а-яА-Яa-zA-Z0-9_-]{1,}/gm, "-");
  str = str.replace('/', "-");
  str = str.replace(/[__]{1,}/gm, "_");
  str = str.replace(/[--]{1,}/gm, "-");
  str = str.replace(/[--]{1,}/gm, "-");

  if (str.substr(-1) == '-' || str.substr(-1) == '_') {
    str = str.substr(0, str.length - 1);
  } //str = str.replace('-', '_');


  return str.toLowerCase();
};

wbapp.transilt = function (word) {
  var cyr = ['ё', 'ж', 'ч', 'щ', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ы', 'ь', 'э', 'я', 'Ё', 'Ж', 'Ч', 'Щ', 'Ш', 'Ю', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ы', 'Ь', 'Э', 'Я'];
  var lat = ['yo', 'j', 'ch', 'sch', 'sh', 'u', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', '`', 'y', '', 'e', 'ya', 'yo', 'J', 'Ch', 'Sch', 'Sh', 'U', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', '`', 'Y', '', 'E', 'ya'];
  word = word + "";
  return word.replaceArray(cyr, lat);
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

var alive = setInterval(function _callee47() {
  return regeneratorRuntime.async(function _callee47$(_context48) {
    while (1) {
      switch (_context48.prev = _context48.next) {
        case 0:
          wbapp.alive();

        case 1:
        case "end":
          return _context48.stop();
      }
    }
  });
}, 84600);

wbapp.init = function _callee48() {
  return regeneratorRuntime.async(function _callee48$(_context49) {
    while (1) {
      switch (_context49.prev = _context49.next) {
        case 0:
          wbapp.wbappScripts();
          wbapp.tplInit();
          wbapp.lazyload();
          wbapp.modalsInit();

        case 4:
        case "end":
          return _context49.stop();
      }
    }
  });
};

wbapp.print = function _callee49(pid) {
  var divToPrint, newWin;
  return regeneratorRuntime.async(function _callee49$(_context50) {
    while (1) {
      switch (_context50.prev = _context50.next) {
        case 0:
          divToPrint = document.getElementById(pid);
          newWin = window.open('', 'Print-Window');
          newWin.document.open();
          newWin.document.write('<!DOCTYPE html><html><head><link rel="stylesheet" type="text/css" href="/engine/lib/bootstrap/css/bootstrap.min.css"></head><body onload="window.print()">' + divToPrint.innerHTML + '</body></html>');
          newWin.document.close();
          setTimeout(function () {
            newWin.close();
          }, 1000);

        case 6:
        case "end":
          return _context50.stop();
      }
    }
  });
};

wbapp.redirectPost = function _callee50(location, args) {
  var form;
  return regeneratorRuntime.async(function _callee50$(_context51) {
    while (1) {
      switch (_context51.prev = _context51.next) {
        case 0:
          form = '';
          $.each(args, function (key, value) {
            value == undefined ? value = "" : null;
            _typeof(value) == 'object' ? value = JSON.stringify(value) : null;
            value = value.split('"').join('\"');
            form += '<textarea style="display:none;" name="' + key + '">' + value + '</textarea>';
          });
          $('<form action="' + location + '" method="POST">' + form + '</form>').appendTo($(document.body)).submit();

        case 3:
        case "end":
          return _context51.stop();
      }
    }
  });
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

function is_visible(elem) {
  if (!(elem instanceof Element)) throw Error('DomUtil: elem is not an element.');
  var style = getComputedStyle(elem);
  if (style.display === 'none') return false;
  if (style.visibility !== 'visible') return false;
  if (style.opacity < 0.1) return false;

  if (elem.offsetWidth + elem.offsetHeight + elem.getBoundingClientRect().height + elem.getBoundingClientRect().width === 0) {
    return false;
  }

  var elemCenter = {
    x: elem.getBoundingClientRect().left + elem.offsetWidth / 2,
    y: elem.getBoundingClientRect().top + elem.offsetHeight / 2
  };
  if (elemCenter.x < 0) return false;
  if (elemCenter.x > (document.documentElement.clientWidth || window.innerWidth)) return false;
  if (elemCenter.y < 0) return false;
  if (elemCenter.y > (document.documentElement.clientHeight || window.innerHeight)) return false;
  var pointContainer = document.elementFromPoint(elemCenter.x, elemCenter.y);

  do {
    if (pointContainer === elem) return true;
  } while (pointContainer = pointContainer.parentNode);

  return false;
}

var loadPhpjs = function loadPhpjs() {
  var phpjs;
  return regeneratorRuntime.async(function loadPhpjs$(_context53) {
    while (1) {
      switch (_context53.prev = _context53.next) {
        case 0:
          if (_tmpphp == false) {
            _tmpphp = true;
            phpjs = document.createElement('script');
            phpjs.src = "/engine/js/php.js";
            phpjs.async = false;

            phpjs.onload = function _callee51() {
              return regeneratorRuntime.async(function _callee51$(_context52) {
                while (1) {
                  switch (_context52.prev = _context52.next) {
                    case 0:
                      setTimeout(function () {
                        wbapp.start();
                      }, 10);

                    case 1:
                    case "end":
                      return _context52.stop();
                  }
                }
              });
            };

            document.head.appendChild(phpjs);
          }

        case 1:
        case "end":
          return _context53.stop();
      }
    }
  });
};

var loadJquery = function loadJquery() {
  var jquery;
  return regeneratorRuntime.async(function loadJquery$(_context55) {
    while (1) {
      switch (_context55.prev = _context55.next) {
        case 0:
          if (_tmpjq == false) {
            _tmpjq = true;
            jquery = document.createElement('script');
            jquery.src = '/engine/js/jquery.min.js';
            jquery.async = false;

            jquery.onload = function _callee52() {
              return regeneratorRuntime.async(function _callee52$(_context54) {
                while (1) {
                  switch (_context54.prev = _context54.next) {
                    case 0:
                      setTimeout(function () {
                        wbapp.start();
                      }, 10);

                    case 1:
                    case "end":
                      return _context54.stop();
                  }
                }
              });
            };

            document.head.appendChild(jquery);
          }

        case 1:
        case "end":
          return _context55.stop();
      }
    }
  });
};