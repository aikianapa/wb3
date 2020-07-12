"use strict"
if (typeof $ === 'undefined') {
    alert("jQuery required!");
} else {

var wbapp = new Object();
var data = {};

wbapp.bind = {};

wbapp.lazyload = function() {
  $("img[data-src]").lazyload();
}

wbapp.eventsInit = function() {
  $(document).delegate("[data-ajax]","tap click",function(e,tid){
      if (!$(this).is("input,select")) {
        e.preventDefault();
        let params = wbapp.parseAttr($(this).attr("data-ajax"));
        params._event = e;
        if (tid !== undefined) params._tid = tid;
        wbapp.ajax(params);
        console.log("Trigger: data-ajax");
        $(document).trigger("data-ajax",params);
      }
  })

  $(document).delegate("input[data-ajax],select[data-ajax]","change",function(e,tid){
      e.preventDefault();
      let search = $(this).attr("data-ajax");
      search = str_replace('$value',$(this).val(),search);
      let params = wbapp.parseAttr(search);
      params._event = e;
      if (tid !== undefined) params._tid = tid;
      wbapp.ajax(params);
      return false;
  })


  $(document).delegate(".pagination .page-link","tap click",function(e){
      e.preventDefault();
      let paginator = $(this).closest(".pagination");
      let tid = $(paginator).data("tpl");
      let params = wbapp.template[tid].params;
      params.page = $(this).attr("data-page");
      params._tid = tid;
      wbapp.ajax(params);
  });

  $(document).delegate("input[type=search][data-ajax]","keyup",function(){
    var minlen = 0;
    var that = this;
    var val = $(this).val();
    that.waitajax = false;
    if ($(this).attr("minlength")) minlen = $(this).attr("minlength") * 1;
    if (that.waitajax == false && val.length >= minlen) {
        that.waitajax = true;
        $(this).trigger("change");
        setTimeout(function(){
          that.waitajax = false;
        },500);
    }
  })

  $(document).delegate("input[type=checkbox]","tap click",function(){
      if ($(this).prop("checked") == false) {
          $(this).removeAttr("checked");
      } else {
          $(this).prop("checked",true);
          $(this).attr("checked",true);
      }
  });

}

wbapp.ajaxAuto = function() {
    $(document).find("[data-ajax][auto]").each(function(){
        $(this).trigger("click");
        $(this).removeAttr("auto");
    })
}

wbapp.auth = function(form) {
    var data = $(form).serializeJson();
    var url = "/ajax/auth/email";
    if ($(form).attr("action") !== undefined) url = $(form).attr("action");
    $.post(url,data,function(res){
          if (res.login) {
              if (res.redirect) document.location.href = res.redirect;
          } else {
              console.log("Login error: " + res.error );
              $(form)[0].reset();
          }

    })
}

wbapp.alive = function() {
  $.get("/ajax/alive",function(data){
      if (data.result == false) {
          console.log("Trigger: session_close");
          $(document).trigger("session_close");
      }
  });
}

wbapp.storage = function (key, value = undefined) {
  function getKey(list) {
      key = "";
      $(list).each(function(i,k){
        if (k.substr(0,1)*1 > -1) {
            key += `['${k}']`
        } else {
            if (i > 0) key += '.'
            key += k
        }
      })
      return key
  }

  if (value == undefined) {
    // get data
    let list = key.split(".");
    var res;
    data = json_decode(localStorage.getItem(list[0]));
    if (list.length) {
      key = getKey(list);
      try {
        eval (`res = data.${key}`);
        return res;
      } catch(err) {
        return undefined
      }
    }
    return data;
  } else {
      // set data
      var path, branch, type;
      var list = key.split(".");
      var last = list.length;

      $(list).each(function(i,k){
          if (i == 0) {
              key = k;
              data = localStorage.getItem(key);
              if (i+1 !== last && data == null) {
                data = {}
              } else {
                try {
                    data = json_decode(data);
                } catch(err) {
                    data = {}
                }
              }
          } else {
              if (k.substr(0,1)*1 > -1) {
                  key += `['${k}']`
              } else {
                  if (i > 0) key += '.'
                  key += k
              }
          }

          try {
              eval (`branch = data.${key}`);
              if (i+1 < last && typeof branch !== "object") eval (`data.${key} = {}`);
          } catch(err) {
              eval (`data.${key} = {}`);
          }
      })
      eval (`data.${key} = value`);
      localStorage.setItem(list[0],json_encode(data));
      $(document).trigger("bind",key,value);
      console.log("Trigger: bind ["+key+"]");
      $(document).trigger("bind-"+key,value);
      console.log("Trigger: bind-"+key);
      return data;
  }
}

wbapp.save = function(obj,params,event) {
  console.log("Trigger: wb-save-start");
  $(obj).trigger("wb-save-start",params);
  let that = this;
  let data, form, result;
  let method = "POST";
          console.log(params);
  if (params.form !== undefined) {
      form = $(params.form);
  } else {
      form = $(obj).parents("form");
  }

  if ($(form).length && !$(form).verify()) return false;

  if ($(form).attr("method") !== undefined) method = $(form).attr("method");
  if ($(form).parents('.modal.saveclose').length) {
      params.dismiss = $(form).parents('.modal.saveclose').attr('id');
  }
  data = wbapp.objByForm(form);
  if (data._idx) delete data._idx;

  if ($(obj).is(":input") && params.table && params.id && params.field) {
        let fld = $(obj).attr("name");
        let value = $(obj).val();
        if ($(obj).is(":checkbox") &&  $(obj).prop("checked")) value = "on";
        if ($(obj).is(":checkbox") && !$(obj).prop("checked")) value = "";
        if ($(obj).is("textarea")) value = $(obj).html();
        data = {};
        data['id'] = params.id;
        eval (`data.${fld} = value;`);
        params.url = `/ajax/save/${params.table}`;
  }

  $.post(params.url,data,function(data) {
          if (params.callback) eval('params = '+params.callback+'(params,data)');
          if (params.data && params.error !== true) {
              var update = [];
              var dataname;
              $.each(data,function(key,value){
                  update[key] = value
              });
              eval('var checknew = (typeof ' +params.data+');');
              if (checknew == "undefined") {
                  eval(`dataname = str_replace("['`+data._id+`']","","`+params.data+`");`);
                  console.log(dataname);
                  eval(dataname+'.push(update)');
              } else {
                  eval(params.data + ' = update;');
              }
          }
          console.log(params)
          if (params.dismiss && params.error !== true) $("#"+params.dismiss).modal("hide");
          if (params.bind) wbapp.storage(params.bind,data);
          if (params.update) wbapp.storageUpdate(params.update,data);
          console.log("Trigger: wb-save-done");
          $(obj).trigger("wb-save-done",{params:params,data:data});
  });
}


wbapp.updateInputs = function(){
    $(document).find(":checkbox").each(function(){
        if ($(this).attr("value") == "on") {
            $(this).attr("checked",true).prop("checked",true);
        } else {
            $(this).attr("checked",false).prop("checked",false);
        }
    })
}

wbapp.wbappScripts = function() {
  var done = [];
  $(document).find("script[type=wbapp]").each(function(){
      var script = $(this).text();
      var hash = md5(script);
      if (!in_array(hash,done)) {
          eval(script);
          done.push(hash);
      }
      if ($(this).attr("visible") == undefined) $(this).remove();
  });
}

wbapp.checkJson = function(queryString) {
    queryString = str_replace(" ","",queryString.trim());
    if (queryString == "{}") return true;
    if (queryString.substr(0,1) == "{" && queryString.substr(-1) == "}" && strpos(queryString,":")) return true;
    return false;
}

wbapp.parseAttr = function(queryString = null) {
    if (queryString == null) queryString = $(this).attr("data-wb");
    queryString = str_replace("'",'"',queryString);
    var params = {};
    if (wbapp.checkJson(queryString)) {
        params = JSON.parse(queryString);
    } else {
        var queries, temp, i, l;
        queries = queryString.split("&");
        // Convert the array of strings into an object
        for ( i = 0, l = queries.length; i < l; i++ ) {
            temp = queries[i].split('=');
            params[temp[0]] = temp[1];
        }
    }
    $(this).data("wb-params",params);
    return params;
}

wbapp.ajax = async function(params) {
    if (!params.url && !params.tpl && !params.target) return;
    if (params.url !== undefined) {
        if (params.form !== undefined) {
            params.formdata = wbapp.objByForm(params.form);
        }
        let opts = Object.assign({}, params);
        delete opts._event;
        $.post(params.url, opts,function(data){
            if (count(data) == 2 && data.error !== undefined && data.callback !== undefined) {
                eval(data.callback);
            }
            if (params.html) eval(`$(document).find('${params.html}').html(data);`);
            if (params.append) eval(`$(document).find('${params.append}').append(data);`);
            if (params.prepend) eval(`$(document).find('${params.prepend}').prepend(data);`);
            if (params.replace) eval(`$(document).find('${params.replace}').replaceWith(data);`);
            //if (params.form) wbapp.formByObj(params.form,data);
            if (params.bind) wbapp.storage(params.bind, data);
            if (params.update && typeof data == "object") wbapp.storageUpdate(params.update, data);
            if (params._trigger !== undefined && params._trigger == "remove") eval( 'delete ' + params.data ); // ???
            if (params.dismiss && params.error !== true) $("#"+params.dismiss).modal("hide");
            if (params.render !== undefined && params.render == 'client') wbapp.renderTemplate(params,data);
            if (params._event !== undefined && $(params._event.target).parent().is(":input")) {
                $inp = $(params._event.target).parent();
                // тут нужна обработка значений на клиенте
            }
            if (params.callback !== undefined ) eval(params.callback+'(params,data)');
            wbapp.tplInit();
            wbapp.ajaxAuto();
            console.log("Trigger: ajax-done");
            params['data'] = data;
            if (params.form !== undefined) {
                $(params.form).trigger("ajax-done", params);
            } else {
                $(document).trigger("ajax-done", params);
            }
            let showmod = $(document).find(".modal.show:not(:visible)");
            if (showmod.length) showmod.removeClass("show").modal('show');
        });
    } else if (params.target !== undefined) {
        var target = wbapp.template[params.target];
        if (!target) {
            console.log("Template not found: " + params.target);
            return;
        } else {
            if (target.params.filter == undefined) target.params.filter = {};
            $.each(params,function(key,val){
                if (key == 'filter') {
                    if (val == 'clear') {target.params.filter = {};} else {
                        target.params.filter = val;
                    }
                }
                if (key == 'filter_remove' && target.params.filter[val] !== undefined) delete target.params.filter[val];
                if (key == 'filter_add') {
                    $.each(val,function(k,v){
                        target.params.filter[k] = v;
                    })
                }
                if (target.params.page !== undefined) delete target.params.page
            });
            wbapp.ajax(target.params);
        }
    }

wbapp.storageUpdate = function(key,data) {
    var store = wbapp.storage(key);
    if (store._id == undefined && store.result !== undefined && data !== null && data._id !== undefined) {
        if (data._removed !== undefined && data._removed == true) {
            try {
                delete store.result[data._id]
            } catch(err) {}
        } else if (data._renamed !== undefined && data._renamed == true) {
            /// rename
        } else {
          try {
              store.result[data._id] = data
          } catch(err) {}
        }
        wbapp.storage(key,store);
    } else {
        wbapp.storage(key,data);
    }
}


}

wbapp.toast = function(title,text,params={}) {
    let options = {
        target: 'body',
        delay: 3000
    }
    if (params.target) options.target = params.target;
    if (params.delay) options.delay = params.delay;
    let $tpl = $(wbapp.tpl('wb.toast').html);
    $tpl.attr('data-delay',options.delay);
    let toast = Ractive({
        el: options.target,
        append: true,
        template: $tpl.outer(),
        data: {title: title, text:text, age:''}
    });

    $(options.target)
    .find(".toast")
    .toast('show')
    .on('hidden.bs.toast',function(){
        $(this).remove()
    });
}


wbapp.formByObj = function(selector,data) {
    var form = $(document).find(selector,0);
    $(form)[0].clear;
    $.each(data,function(key,value){
        $(form).find("[name='"+key+"']").val(value);
    });
}

wbapp.objByForm = function(form) {
    form = $(form);
    let data = $(form).serializeJson();
    return data;
}

wbapp.tplInit = function() {
    if (!wbapp.template) wbapp.template = {};
    wbapp.getForm("snippets","toast").then(function(res){
        wbapp.tpl('wb.toast',{html:res.result,params:{}});
    });
    $(document).find("template").each(function(){
        var tid
        if (tid == undefined) tid = $(this).parent().attr("id");
        if (tid == undefined && $(this).is("template[id]")) tid = $(this).attr("id");
        if (tid == undefined) {
            $(this).attr("id","fe_"+wbapp.newId());
            var tid = $(this).attr("id");
        }
        tid = "#"+tid;
        var params = [];
        if ($(this).attr("data-params") !== undefined) params = json_decode($(this).attr("data-params"));
        $(this).removeAttr("data-params");
        if ($(this).attr("data-ajax") !== undefined) {
            params = wbapp.parseAttr($(this).attr("data-ajax"));
            wbapp.tpl(tid, {
                html:$(this).html(),
                params:params
            });
            $(this).trigger("click",tid);
        } else {
            wbapp.tpl(tid, {
                html:$(this).html(),
                params:params
            });
        }
        if ($(this).attr("visible") == undefined) $(this).remove();
    });
    wbapp.wbappScripts();
}

wbapp.getForm = async function(form,mode,data={}) {
    return await wbapp.postSync(`/ajax/getform/${form}/${mode}`,data).then(function(res){
        return res
    });
}

wbapp.tpl = function(tid,data=null) {
  if (data == null) {
      return wbapp.template[tid];
  } else {
      wbapp.template[tid] = data;
  }
}

wbapp.renderTemplate = function(params,data = {}) {
  var tid;
  if (params._tid !== undefined) tid = params._tid;
  if (params.target !== undefined) tid = params.target;


  /*
  var that = params._event.target;
  var tpl = $(that).parent();
  if ($(that).is("template")) tpl = $(that);
  var tid = "#"+$(tpl).parent().attr("id");
  if (params.target !== undefined) tid = params.target;
  */

  if (wbapp.template[tid] == undefined) return;

  if (wbapp.bind[params.bind] == undefined) wbapp.bind[params.bind] = {};
  wbapp.storage(params.bind,data);

  wbapp.bind[params.bind][tid] = new Ractive({
                target: tid,
                template: wbapp.template[tid].html,
                data: () => {return wbapp.storage(params.bind)}
  })
  wbapp.template[tid].params = params;
  var pagination = $(tid).find(".pagination");
  if (pagination) {
      let page = 1;
      $(pagination).data("tpl",tid);
      if (params.page) page = params.page;
      $(pagination).find(".page-item").removeClass("active");
      $(pagination).find(`[data-page="${page}"]`).parent(".page-item").addClass("active");
  }

  $(document).on("bind-"+params.bind,function(e,data){
//        console.log(params.bind,tid,data);
        wbapp.bind[params.bind][tid].set(data);
  })
}

wbapp.newId = function(separator, prefix) {
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
}

wbapp.modalsInit = function() {
  var zndx = $(document).data("modal-zindex");
  if (zndx == undefined) $(document).data("modal-zindex", 2000);

  $(document).delegate(".modal-header","dblclick",function(event){
      var that = $(event.target);
      $(that).closest(".modal").find(".modal-content").toggleClass("modal-wide");
  });


  $(document).delegate(".modal", "shown.bs.modal", function(event) {
      var that = $(event.target);
      if ($(that).is("[data-zndx]")) return;
      $(that).find('.modal-content')
  //      .resizable({
  //        minWidth: 300,
  //        minHeight: 175,
  //        handles: 'n, e, s, w, ne, sw, se, nw',
  //      })
        .draggable({
          handle: '.modal-header'
        });

      var zndx = $(document).data("modal-zindex");
      if (zndx == undefined) {
        var zndx = 4000;
      } else {
        zndx += 10;
      }
      if (!$(this).closest().is("body")) {
          if ($(this).data("parent") == undefined) $(this).data("parent", $(this).closest());
          $(this).appendTo("body");
      }
      $(this).data("zndx", zndx).css("z-index", zndx).attr("data-zndx",zndx);
      $(that).find("[data-dismiss]").attr("data-dismiss",zndx);
      $(document).data("modal-zindex", zndx);
      if ($(that).attr("data-backdrop") !== undefined && $(that).attr("data-backdrop") !== "false") {
        setTimeout(function() {
          $(".modal-backdrop:not([id])").css("z-index", (zndx - 5)).attr("id", "modalBackDrop" + (zndx - 5));
        }, 0);
      }
  });

  $(document).delegate(".modal [data-dismiss]","click",function(event){
      var zndx =  $(this).attr("data-dismiss");
      var modal = $(document).find(".modal[data-zndx='"+$(this).attr("data-dismiss")+"']");
        modal.modal("hide");
  });

  $(document).delegate(".modal", "hide.bs.modal", function(event) {
    var that = $(event.target);
    var zndx = $(that).attr("data-zndx");
    $("#modalBackDrop" + (zndx - 5) + ".modal-backdrop").remove();
    $(document).data("modal-zindex", zndx - 10);
  });
  $(document).delegate(".modal", "hidden.bs.modal", function(event) {
    var that = $(event.target);
    if ($(this).hasClass("removable")) {$(that).modal("dispose").remove();}
    else {$(this).appendTo($(this).data("parent"));}
  });
  $(document).off("wb-ajax-done");
  $(document).on("wb-ajax-done",function(){
      console.log("Trigger: wb-ajax-done");
      if (wbapp !== undefined) {
        wbapp.tplInit();
        wbapp.watcherInit();
        wbapp.wbappScripts();
        wbapp.pluginsInit();
        wbapp.lazyload();
      }
      $(".modal.show:not(:visible),.modal[data-show=true]:not(:visible)").modal("show");
      if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
  });
}

wbapp.getSync = async function(url,data = {}) {
    var result;
    await $.get(url,data).then(function(value){
        result = value
    })
    return result
}

wbapp.postSync = async function(url,data = {}) {
    var result;
    await $.post(url,data).then(function(value){
        result = value
    })
    return result;
}

wbapp.session = async function() {
    if (wbapp._session == undefined) wbapp._session = await wbapp.getSync("/ajax/getsess/");
    return wbapp._session;
}

wbapp.settings = async function() {
    if (wbapp._settings == undefined) wbapp._settings = await wbapp.getSync("/ajax/getsett/");
    return wbapp._settings;
}

wbapp.loadScripts = async function(scripts = [], trigger = null, func = null) {
  if (wbapp.loadedScripts == undefined) wbapp.loadedScripts = [];
  let i = 0;
  scripts.forEach(function(src) {
//    let name = src.split("/");
//    name = name[name.length-1];
    let name = src;
    if (wbapp.loadedScripts.indexOf(name) !== -1) {
      i++;
      if (i >= scripts.length) {
        if (func !== null) return func(scripts);
        if (trigger !== null) {
          $(document).find("script#" + trigger + "-remove").remove();
          $(document).trigger(trigger);
          console.log("Trigger: " + trigger);
        }
      }
    } else {
      var script = document.createElement('script');
      script.src = src;
      script.async = false;
      script.onload = function() {
        i++;
        console.log("Script loaded: " + name);
        wbapp.loadedScripts.push(name);
        if (i >= scripts.length) {
          if (func !== null) return func(scripts);
          if (trigger !== null) {
            $(document).find("script#" + trigger + "-remove").remove();
            console.log("Trigger: " + trigger);
            $(document).trigger(trigger);
          }
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
    if (wbapp.loadedStyles.indexOf(src) !== -1) {
      i++;
      if (i >= styles.length) {
        if (func !== null) return func(styles);
        if (trigger !== null) {
          console.log("Trigger: " + trigger);
          $(document).find("script#" + trigger + "-remove").remove();
          $(document).trigger(trigger);
        }
      }
    } else {
      var style = document.createElement('link');
      wbapp.loadedStyles.push(src);
      style.href = src;
      style.rel = "stylesheet";
      style.type = "text/css";
      style.async = false;
      style.onload = function() {
        i++;
        if (i >= styles.length) {
          if (func !== null) return func(styles);
          if (trigger !== null) {
            $(document).find("script#" + trigger + "-remove").remove();
            $(document).trigger(trigger);
            console.log("Trigger: " + trigger);
          }
        }
      }
      document.head.appendChild(style);
    }
  });
}


var array_column = function (list, column, indice){
    var result, key;
if (list.length) {
    if(typeof indice !== "undefined"){
        result = {};

        for(key in list)
            if(typeof list[key][column] !== 'undefined' && typeof list[key][indice] !== 'undefined')
                result[list[key][indice]] = list[key][column];
    }else{
        result = [];

        for(key in list)
            if(typeof list[key][column] !== 'undefined')
                result.push( list[key][column] );
    }
}
    return result;
}

$.fn.verify = function() {
    var form = this;
    var res = true;
    var idx = 0;
    $(form).find("[required],[type=password],[minlength],[min],[max]").each(function(i) {
        idx++;
		var label = $(this).attr("data-label");
		if (label == undefined || label == "") label = $(this).prev("label").text();
		if (label == undefined || label == "") label = $(this).next("label").text();
		if ((label == undefined || label == "") && $(this).attr("id") !== undefined) label = $(this).parents("form").find("label[for="+$(this).attr("id")+"]").text();
		if (label == undefined || label == "") label = $(this).parents(".form-group").find("label").text();
		if (label == undefined || label == "") label = $(this).attr("placeholder");
		if (label == undefined || label == "") label = $(this).attr("name");

        $(this).data("idx", idx);
        if ($(this).is(":not([disabled],[readonly],[min],[max],[maxlength],[type=checkbox]):visible")) {
            if ($(this).val() == "") {
                res = false;
                console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
                $(document).trigger("wb-verify-false", [this]);
            } else {
                if ($(this).attr("type") == "email" && !wb_check_email($(this).val())) {
                    res = false;
                    $(this).data("error", wbapp.settings.sysmsg.email_correct);
                    console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
                    $(document).trigger("wb-verify-false", [this]);
                } else {
					          console.log("trigger: wb-verify-true");
                    $(document).trigger("wb-verify-true", [this]);
                }
            }
        }
        if ($(this).is("[type=checkbox]") && $(this).is(":not(:checked)")) {
            res = false;
            console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
            $(document).trigger("wb-verify-false", [this]);
        }
				if ($(this).is("[type=radio]") && $(this).is(":not(:checked)")) {
            res = false;
						var fld = $(this).attr("name");
						if (fld > "") {
								$("[type=radio][name='"+fld+"']").each(function(){
									if ($(this).is(":checked")) {res = true;}
								});
						}
						if (!res) {
            		console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
            		$(document).trigger("wb-verify-false", [this]);
						}
        }
        if ($(this).is("[type=password]")) {
            var pcheck = $(this).attr("name") + "_check";
            if ($("input[type=password][name='" + pcheck + "']").length) {
                if ($(this).val() !== $("input[type=password][name=" + pcheck + "]").val()) {
                    res = false;
                    $(this).data("error", wbapp.settings.sysmsg.pass_match);
                    console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
                    $(document).trigger("wb-verify-false", [this]);
                }
            }
        }
        if ($(this).is("[min]:not([readonly],[disabled]):visible") && $(this).val() > "") {
			var min = $(this).attr("min") * 1;
			var minstr = $(this).val() * 1;
			if (minstr < min) {
                res = false;
                $(this).data("error", ucfirst(label) + " " + wbapp.settings.sysmsg.min_val+": " + min);
                console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
                $(document).trigger("wb-verify-false", [this]);
			}
		}

        if ($(this).is("[max]:not([readonly],[disabled]):visible")  && $(this).val() > "") {
			var max = $(this).attr("max") * 1;
			var maxstr = $(this).val() * 1;
			if (maxstr > max) {
                res = false;
                $(this).data("error", ucfirst(label) + " " + wbapp.settings.sysmsg.max_val+": " + max);
                console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
                $(document).trigger("wb-verify-false", [this]);
			}
		}

        if ($(this).is("[minlength]:not([readonly],[disabled]):visible") && $(this).val() > "") {
            var minlen = $(this).attr("minlength") * 1;
            var lenstr = strlen($(this).val());
            if (lenstr < minlen) {
                res = false;
                $(this).data("error", ucfirst(label) + " " + wbapp.settings.sysmsg.min_length+": " + minlen);
                console.log("trigger: wb-verify-false ["+$(this).attr("name")+"]");
                $(document).trigger("wb-verify-false", [this]);
            }
        }

        if ($(this).is("button")) {
    			if (
    				($(this).attr("value") !== undefined && $(this).val() == "")
    				||
    				($(this).attr("value")  == undefined && $(this).html() == "")
    			) {
    				res = false;
    			}
		    }
    });
    if (res == true) {
	       console.log("trigger: wb-verify-success");
        $(document).trigger("wb-verify-success", [form]);
    }
    if (res == false) {
	       console.log("trigger: wb-verify-fail");
         $(document).trigger("wb-verify-fail", [form]);
    }
    return res;
}

$.fn.outer = function(s) {
  return s ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
};

$.fn.runScripts = function() {
  $(this).find("script").each(function() {
    var type = $(this).attr("type");
    if (type !== "text/locale" && type !== "text/template") {
      eval($(this).text());
      if ($(this).attr("removable")!==undefined) $(this).remove();
    }
  });
}

$.fn.serializeJson = function(data = {}) {
  var form = this;
  $(form).find("form [name], .wb-unsaved [name], .wb-tree-item [name]").each(function(){
      $(this).attr("wb-tmp-name",$(this).attr("name"));
      $(this).removeAttr("name");
  });
  var branch = $(form).serializeArray();
  $(branch).each(function(i, val) {
    data[val["name"]] = val["value"];
    if ($(form).find("textarea[type=json][name='" + val["name"] + "']").length && strpos(data[val["name"]],"}")) {
          data[val["name"]] = json_decode(data[val["name"]]);
        }
  });

  var check = $(form).find('input[name][type=checkbox],input[name][type=radio]');
  // fix unchecked values
  $.each(check,function(){
      data[this.name] = "";
      if (this.checked) data[this.name] = "on";
  });
  $(form).find("form [wb-tmp-name], .wb-unsaved [wb-tmp-name], .wb-tree-item [wb-tmp-name]").each(function(){
      $(this).attr("name",$(this).attr("wb-tmp-name"));
      $(this).removeAttr("wb-tmp-name");
  });
  return data;
}

$.fn.jsonVal = function(data = undefined) {
  if (strtolower($(this).attr("type")) !== "json") {
    return $(this).val();
  }
  if (data == undefined) {
    var data = $(this).val();
    if (data > "") {
      data = json_decode(data);
    } else {
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

  setTimeout(function(){
      if (typeof str_replace == 'undefined') {
          wbapp.loadScripts([
            `/engine/js/php.js`
            ,`/engine/js/jquery-ui.min.js`
            ,`/engine/js/ractive.js`
            ,`/engine/js/lazyload.js`
          ],"wbapp-go");
      } else {
          $(document).trigger("wbapp-go");
      }
  },1500);

  setInterval(function(){
    wbapp.alive();
  },84600);

  $(document).on("wbapp-go",function(){
        wbapp.tplInit();
        wbapp.eventsInit();
        wbapp.wbappScripts();
        wbapp.modalsInit();
        wbapp.ajaxAuto();
        wbapp.session();
        wbapp.settings();
        wbapp.lazyload();
  });
}
