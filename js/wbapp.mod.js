await import("/engine/js/jquery.min.js")
import Vue from "./vue.esm.browser.min.js"
var wbapp = new Object();
var $ = wbapp.jq = window.$ = window.jQuery
wbapp.vue = window.Vue = Vue

wbapp.css = function(url) {
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
}

wbapp.js = function (src) {
    let script = document.createElement('script');
    let head = document.getElementsByTagName('head')[0];
    script.async = false;
    script.type = 'text/javascript';
    script.src = src;
    script.onload = function () {
        head.appendChild(script)
    }
}

wbapp.html = async function (src, target = null) {
    if (target) {
        return await fetch(src)
            .then(function (response) {return response.text()})
            .then(function (html) {$(target).html(html)})
            .catch(function (err) {
                console.log('Failed to fetch page: ' + src, err);
            });
    } else {
        return await fetch(src)
            .then(function (response) {return response.text()})
            .then(function (html) {
                if (target) {
                    $(target).html(html)
                } else {
                    return html
                }
            })
            .catch(function (err) {
                console.log('Failed to fetch page: ' + src, err);
            });
    }
}

wbapp.start = async function () {
    let mods = import.meta.url.split('?')
    mods = mods[1] !== undefined ? mods[1].replace(' ', '').split(',') : null;
    if (mods) {
        if (mods.indexOf('jquery') !== -1) {
            await import("/engine/js/jquery.min.js")
            wbapp.jq = $ = window.jQuery
        }
        if (mods.indexOf('vue') !== -1) {
            //window.Vue = await import("./vue.esm.browser.min.js")
            //console.log(Window.Vue); 
        }

        if (mods.indexOf('alpine') !== -1) {
            document.addEventListener('alpine:init', () => {
                wbapp.alpine = window.Alpine = Alpine
            })
            await import("./alpine.min.js")
        }

        if (mods.indexOf('bs') !== -1) {
            wbapp.css("https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css")
            wbapp.bs = import("./bootstrap.esm.min.js")
        }
        if (mods.indexOf('semantic') !== -1) {
            await wbapp.css("/src/semantic/semantic.css")
            await import("/engine/js/jquery.min.js")
            wbapp.jq = $ = window.jQuery
            await import("/src/semantic/semantic.min.js")

        }
    } 
    wbapp.metadata('wbsess')
    wbapp.events()
}

wbapp.newId = function (separator, prefix) {
    (separator == undefined) ? separator = "" : null;
    var mt = explode(" ", microtime());
    var md = substr(str_repeat("0", 2) + dechex(ceil(mt[0] * 10000)), -4);
    var id = dechex(time() + rand(100, 999));
    id = (prefix !== undefined && prefix > "") ? prefix + separator + id + md : "id" + id + separator + md;
    return id;
}

wbapp.isJson = function (queryString) {
    queryString = queryString.trim().replace(" ", "");
    queryString = queryString.replaceAll("'", '"');
    if (queryString == "{}") return true;
    if (queryString.substr(0, 1) == "{" && queryString.substr(-1) == "}" && queryString.indexOf(":")) return true;
    return false;
}

wbapp.parseAttr = function (queryString = null) {
    queryString = queryString.replaceAll("'", '"');
    queryString = queryString.replaceAll("&amp;", '&');
    var params = {};
    if (wbapp.isJson(queryString)) {
        params = JSON.parse(queryString);
    } else {
        parse_str(queryString, params);
    }
    return params;
}

wbapp.events = function() {
    $(document).undelegate('[data-ajax]','tap click touchstart')
    $(document).delegate('[data-ajax]', 'tap click touchstart',function(){
        let params = $(this).attr('data-ajax')
        params = wbapp.parseAttr(params)
        return fetch(params.url)
            .then(function (response) {return response.text()})
            .then(function (data) {
                if (params.html) $(document).find(params.html).html(data);
                if (params.append) $(document).find(params.append).append(data);
                if (params.prepend) $(document).find(params.prepend).prepend(data);
                if (params.replace) $(document).find(params.replace).replaceWith(data);
            })
            .catch(function (err) {
                console.log('Failed to fetch page: ' + params.url, err);
            });
    })
}

wbapp.store = function (storage = null, key, value = undefined, binds = true) {
    if (storage == null) storage = localStorage
    key = key.replace('-', '__')
    function getKey(list) {
        key = "";
        $(list).each(function (i, k) {
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
        data = (data !== null) ? JSON.parse(data) : data = {}

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
        $(list).each(function (i, k) {
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

        let checkBind = function (bind, key) {
            if (bind == key) return true;
            if (key.substr(0, bind.length) == bind) return true;
            return false;
        }

        if (binds == true) {
            $.each(wbapp.template, function (i, tpl) {
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

wbapp.metadata = function () {
    let meta = document.querySelector(`meta[name="wbmeta"]`)
    let data = {}
    if (meta) {
        let data = meta.getAttribute('content')
        data = data > '' ? JSON.parse(atob(data)) : {}
        wbapp._sess = data.sess
        wbapp._sett = data.sett
        wbapp._route = data.rout
    } else {
        wbapp._sess = {}
        wbapp._sett = {}
        wbapp._route = {}
    }
    return data;
}

wbapp.storage = function (key, value = undefined, binds = true) {
    return wbapp.store(localStorage, key, value, binds)
}

wbapp.data = function (key, value = undefined, binds = true) {
    return wbapp.store(sessionStorage, key, value, binds)
}

wbapp.transilt = function (word) {
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

wbapp.isEmail = function (email) {
    if (email.match(/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/i)) {
        return true;
    } else {
        return false;
    }
}

wbapp.formInit = function(form) {
    return
    let setName = function (tag, parent = false) {
        $(tag).find('[name]:not(wb-set-name)').each(function () {
            let name = $(this).attr('name')
            if (name > '') {
                name = parent ? parent+'.'+name : name
                console.log(name);
                $(this).attr('name',name)
                setName(this,name)
            }
            $(this).addClass('wb-set-name')
        })
    }
    setName(form)
    $(form).find('.wb-set-name').removeClass('wb-set-name');
}


wbapp.formData = function (form, data = {}) {
    form = $(form)
    $(form).find("form [name], .wb-unsaved[name], .wb-tree-item [name]").each(function () {
        $(this).attr("wb-tmp-name", $(this).attr("name"));
        $(this).removeAttr("name");
    });
    var branch = $(form).serializeArray();

    $(branch).each(function (i, val) {
        let value = val["value"];
        let name = val["name"];
        data[name] = value;
        let $textarea = $(form).find("textarea[name='" + name + "']");
        if ($textarea.length && $textarea.is("[type=json]")) {
            let _val = $textarea.val();
            let _text = $textarea.text();
            _val == 'null' ? data[name] = _text : data[name] = _val;
            if (['null', '', '{}', '[]'].includes(data[name], )) {
                data[name] = '';
            } else {
                try {
                    data[name] = JSON.parse(data[name], true);
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
    $.each(sel, function () {
        data[this.name] = $(this).val();
    });

    let multi = $(form).find('select[name][multiple]');
    $.each(multi, function () {
        data[this.name] = $(this).val();
    });

    let muinp = $(form).find('wb-multiinput[name]');
    $.each(muinp, function () {
        data[$(this).attr('name')] = $(this).data('value');
    });

    let attaches = $(form).find('input[name][type=file]');
    let reader = new FileReader();
    $.each(attaches, function () {
        let file = $(this)[0].files[0];
        if (file) {
            let that = this;
            reader.readAsDataURL(file);
            reader.onload = function () {
                data[that.name] = reader.result.toString(); //base64encoded string
            };
        }
    });

    var check = $(form).find('input[name][type=checkbox]');
    // fix unchecked values
    $.each(check, function () {
        data[this.name] = "";
        if (this.checked) data[this.name] = "on";
    });

    var check = $(form).find('input[name][type=radio]');
    // fix unchecked values
    $.each(check, function () {
        if (this.checked) data[this.name] = $(this).attr('value');
    });

    $(form).find("form [wb-tmp-name], .wb-unsaved [wb-tmp-name], .wb-tree-item [wb-tmp-name]").each(function () {
        $(this).attr("name", $(this).attr("wb-tmp-name"));
        $(this).removeAttr("wb-tmp-name");
    });
    // fix dot notation
    let obj = {}
    $.each(data, function (name, value) {
        if (name.indexOf(".") !== -1) {
            let chunks = name.split(".")
            let idx = ""
            $.each(chunks, function (i, key) {
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



await wbapp.start()

$(document).undelegate("[rows=auto]", "keydown keyup focus");
$(document).delegate("[rows=auto]", "keydown keyup focus", function () {
    this.style.overflow = "auto";
    this.style.height = "5rem";
    this.style.height = (this.scrollHeight) + "px";
});

export default wbapp