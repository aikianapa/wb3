    var editor;
//    var locale = wbapp.getlocale("url","/module/filemanager/locale");
    filemanagerGetDir('');
    filemanagerSideMenu();
    filemanagerListEvents();
    filemanagerBreadcrumbs();
    filemanagerDialog();
    filemanagerBuffer();

    $("#filemanager").trigger("checkbox");
    $("#filemanagerTabs").data("tab", $("#filemanagerTabs").html());
    $("#filemanagerTabs").html("");
    $('body').addClass('chat-content-show');

    function filemanagerListEvents() {

    $('#filemanager').undelegate('#filemanagerModalDialog','shown.bs.modal');
    $('#filemanager').delegate('#filemanagerModalDialog','shown.bs.modal', function () {
      $('#filemanagerModalDialog input:visible:first').focus();
    });

        $('#filemanager').undelegate('#filemanagerModalSrc', 'shown.bs.modal');
        $('#filemanager').delegate('#filemanagerModalSrc', 'shown.bs.modal', function () {
            filemanagerEditorSize();
        });


    $("#filemanager").undelegate("#filemanagerModalDialog","keydown");
    $("#filemanager").delegate("#filemanagerModalDialog","keydown",function(e){
        if (e.keyCode == 13) {
            $("#filemanagerModalDialog .btn-primary").trigger("click");
            return false;
        }
    });


    $("#filemanager").off("checkbox");
    $("#filemanager").on("checkbox", function() {
            var menu = $("#filemanager .filemgr-sidebar .nav");
            var count = $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked").length;

            if (count > 1) {
                $(menu).find(".nav-item.allow-all").show();
            }
            if (count == 0) {
                $("#filemanager").data("buffer", undefined);
                $("#filemanager").data("bufferpath", undefined);
                $("#filemanager").find(".allow-buffer, .allow-single, .allow-all").hide();
            }
    });


    $("#filemanager").undelegate(".chat-wrapper", "click tap swipe");
    $("#filemanager").delegate(".chat-wrapper", "click tap swipe", function () {
        $('body').addClass('chat-content-show');    
    })

    $("#filemanager").undelegate(".filepicker", "mod-filepicker-done");
    $("#filemanager").delegate(".filepicker", "mod-filepicker-done", function () {
        filemanager_reload_list();
    })

    $("#filemanager").undelegate("#list tr", "dblclick");
    $("#filemanager").delegate("#list tr", "dblclick", function(e) {
        if ($(e.target).is('[type=checkbox]')) return;
            var path = $("#filemanager #list").data("path");
            if ($(this).is(".dir,.dir1")) {
                filemanagerGetDir(path + "/" + $(this).attr("data-name"));
            }
            if ($(this).is(".file")) {
                filemanagerCallEditor(path + "/" + $(this).attr("data-name"));
            }
            if ($(this).is(".back")) {
                path = explode("/", path);
                path.splice(path.length - 1, 1);
                path = implode("/", path);
                filemanagerGetDir(path);
            }
        });


        $("#filemanager").undelegate("#list tr.dir td.name", "click");
        $("#filemanager").delegate("#list tr.dir td.name", "click", function() {
            if (!$("#filemanager #list .dropdown-menu.show").length) {
                $(this).parents("tr").trigger("dblclick");
            }
        });
        $("#filemanager").undelegate("#list tr.dir1 td.name", "click");
        $("#filemanager").delegate("#list tr.dir1 td.name", "click", function() {
            if (!$("#filemanager #list .dropdown-menu.show").length) {
                $(this).parents("tr").trigger("dblclick");
            }
        });

        $("#filemanager").undelegate("#list a.nav-link, a.nav-link[href='#newdir'], a.nav-link[href='#newfile']", "click");
        $("#filemanager").delegate("#list a.nav-link, a.nav-link[href='#newdir'], a.nav-link[href='#newfile']", "click", function() {
            $("#filemanager #filemanagerModalDialog").remove();
            var href = $(this).attr("href");
            var post = {
                "path": $("#filemanager #list").data("path")
            };
            if ($(this).parents("tr[data-name]").length) {
                var parent = $(this).parents("tr[data-name]");
                post["name"] = $(parent).attr("data-name");
                if ($(parent).hasClass("dir")) {
                    post["type"] = "dir";
                }
                if ($(parent).hasClass("dir1")) {
                    post["type"] = "dir-link";
                }
                if ($(parent).hasClass("file")) {
                    post["type"] = "file";
                }
                if ($(parent).hasClass("file1")) {
                    post["type"] = "file-link";
                }
            }
            $("#filemanager").data("post", post);
            $("#filemanager").data("line", $(this).parents("tr"));
            if (substr(href, 0, 1) == "#" && !in_array(href, ["#edit"])) {
                wbapp.post("/module/filemanager/dialog/" + substr(href, 1), post, function(data) {
                    $("#filemanager").append(data);
                    $("#filemanager #filemanagerModalDialog").modal("show");
                });
            }
            if (href == "#edit") {
                $(this).parents("tr").find("td.name").trigger("dblclick");
            }
        });
        $("#filemanager").undelegate("#list tr.back td.name", "click");
        $("#filemanager").delegate("#list tr.back td.name", "click", function() {
            $(this).parents("tr").trigger("dblclick");
        });

        $("#filemanager").undelegate("#list tr", "contextmenu");
        $("#filemanager").delegate("#list tr", "contextmenu", function() {
            //$(this).find("td .dropdown > a").trigger("click");
            return false;
        });

    }

    function filemanagerBuffer() {
        $("#filemanager").undelegate("#list tr [type=checkbox]", "change");
        $("#filemanager").delegate("#list tr [type=checkbox]", "change", function(e) {
            var menu = $("#filemanager .filemgr-sidebar .nav");
            var count = $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked").length;
            var type;
            $(menu).find(".nav-item.hidden").hide();
            if (count == 1) {
                var check = $(this).parents("#list").find("[type=checkbox]:checked");
                var ext = $(check).parents("tr").attr("data-ext");
                if ($(check).parents("tr.dir").length) {
                    type = "dir";
                } else if ($(check).parents("tr.file").length) {
                    type = "file";
                } else if ($(check).parents("tr.dir1").length) {
                    type = "dir1";
                } else if ($(check).parents("tr.file1").length) {
                    type = "file1";
                }
                $(menu).find(".nav-item.allow-single.allow-" + type).show();
                $(menu).find(".nav-item.allow-all").show();
                if (ext !== undefined) {
                    $(menu).find(".nav-item[data-ext]").each(function() {
                        if (!in_array(ext, explode(",", $(this).attr("data-ext")))) {
                            $(this).hide();
                        }
                    });
                    $(menu).find(".nav-item[data-no-ext]").each(function() {
                        if (in_array(ext, explode(",", $(this).attr("data-no-ext")))) {
                            $(this).hide();
                        }
                    });
                }
            }
            $("#filemanager").trigger("checkbox");
            return false;
        });
    }

    function filemanagerDialogMulti(href) {
            $("#filemanager #filemanagerModalDialog").remove();
            var check = $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked");
            var post = {
                "path": $("#filemanager #list").data("path"),
                "multi": true
            };
            wbapp.post("/module/filemanager/dialog/" + substr(href, 1), post, function(data) {
                $("#filemanager").append(data);
                $("#filemanager #filemanagerModalDialog").modal("show");
            });
    }


    function filemanagerSetPostChecked() {
         var list = {};
         $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked").parents("tr").each(function (i) {
             var item = {
                 name: $(this).attr("data-name")
             };
             list[i] = item;
         });
         var post = {
             path: $("#filemanager #list").data("path")
             , list: json_encode(list)
         };
         $("#filemanager").data("post", post);
    }


    function filemanagerSideMenu() {
        $("#filemanager").undelegate(".filemgr-sidebar .nav a.nav-link", "click");
        $("#filemanager").delegate(".filemgr-sidebar .nav a.nav-link", "click", function() {
            $('body').addClass('chat-content-show');
            var check = $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked");
            var count = $(check).length;
            var href = $(this).attr("href");
            var type;
            if (count == 1) {
                if ($(check).parents("tr.dir").length) {
                    type = "dir";
                } else if ($(check).parents("tr.file").length) {
                    type = "file";
                } else if ($(check).parents("tr.dir1").length) {
                    type = "dir1";
                } else if ($(check).parents("tr.file1").length) {
                    type = "file1";
                }
            }

            switch (href) {
                case '#zip':
                    filemanagerSetPostChecked();
                    filemanagerDialogMulti(href);
                    break;
                case '#unzip':
                    filemanagerSetPostChecked();
                    filemanagerDialogMulti(href);
                    break;
                case '#rename':
                    if (count == 1) {
                        $(check).parents("tr").find("a[href='#ren" + type + "']").trigger("click");
                    }
                    break
                case '#remove':
                    if (type !== "dir" && type !== "file") {
                        type = "link";
                    }
                    if (count == 1) {
                        $(check).parents("tr").find("a[href='#rm" + type + "']").trigger("click");
                    } else {
                        filemanagerSetPostChecked();
                        filemanagerDialogMulti(href);
                    }
                    break
                case '#edit':
                    if (count == 1) {
                        $(check).parents("tr").find("a[href='#edit']").trigger("click");
                    }
                    break
                case '#dnload':
                    // не работает!!!!
                    if (count == 1) {
                        var dnl=$(check).parents("tr").find("a[download]");
                        if (dnl.length) {
                            console.log($(check).parents("tr").find("a[download]").attr("download"));
                            $(dnl).trigger("click");
                        }
                    }
                    break
                case '#copy':
                    $("#filemanager").data("buffer", $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked").parents("tr"));
                    $("#filemanager").data("bufferpath", $("#filemanager #list").data("path"));
                    $("#filemanager").data("buffertype", "copy");
                    $("#filemanager .allow-buffer").show();
                    break

                case '#cut':
                    $("#filemanager").data("buffer", $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked").parents("tr"));
                    $("#filemanager").data("bufferpath", $("#filemanager #list").data("path"));
                    $("#filemanager").data("buffertype", "cut");
                    $("#filemanager .allow-buffer").show();
                    break

                case '#paste':
                    if ($("#filemanager").data("bufferpath") !== $("#filemanager #list").data("path")) {
                        filemanagerPaste();
                    }
                    break
                case "#refresh":
                    filemanager_reload_list();
                    break
            }
            return false;
        });
    }

    function filemanagerDialog() {
        $("#filemanager").undelegate("#filemanagerModalDialog .btn-primary", "click");
        $("#filemanager").delegate("#filemanagerModalDialog .btn-primary", "click", function() {
            var action = $(this).attr("data-action");
            var post, data;
            if (action == "paste" || action == "remove") {
                post = $("#filemanager").data("post");
            } else if (action=="zip" || action=="unzip") {
                post = $("#filemanager #filemanagerModalDialog .modal-body form").serialize();
                post += "&path=" + $("#filemanager #list").data("path");
                $("#filemanager #list").find("tr:not(.back) [type=checkbox]:checked").parents("tr").each(function(){
                    post+="&list[]="+$(this).attr("data-name");
                });
            } else {
                post = $("#filemanager #filemanagerModalDialog .modal-body form").serialize();
                data = $("#filemanager").data("post");
                post += "&type=" + data["type"] + "&path=" + data["path"];
            }
            $("#filemanager #filemanagerModalDialog").modal("hide");
            wbapp.post("/module/filemanager/action/" + action, post, function(data) {
                data = json_decode(data);
                var line = $("#filemanager").data("line");
                $("#filemanager #filemanagerModalDialog").modal("hide");
                if (line !== undefined && data.action == "change_name") {
                    $(line).find("td.name > span").html(data.name);
                    $(line).attr("data-name", data.name);
                    if (data.ext !== undefined) {
                        $(line).find("td.name + td").html(data.ext);
                    }
                }
                $("#filemanager").data("post", undefined);
                $("#filemanager").data("line", undefined);
                if (data.action == "reload_list") {
                    filemanager_reload_list();
                }


            });
        });
    }


    function filemanagerBreadcrumbs() {
        $("#filemanager").undelegate(".breadcrumb-item a", "click");
        $("#filemanager").delegate(".breadcrumb-item a", "click", function(e) {
            var path = $(this).attr('data-path');
            filemanagerGetDir(path);
            return false;
        });
    }

    function filemanagerGetDir(dir) {
        wbapp.loading();
        var data = wbapp.postSync("/module/filemanager/getdir/?dir=" + urlencode(dir));
            $("#filemanager #panel").replaceWith(data);
            $("#filemanager #list").data("path", dir);
            $("#filemanager [name=upload_url]").val(dir);
//            $("#filemanager").noSelect();
            $("#filemanager").trigger("checkbox");
            if ($("#filemanager").data("buffer")!==undefined) {
                $("#filemanager .filemgr-sidebar .allow-buffer").show();
            };
//            wb_pagination();
            wbapp.unloading();
            $(document).trigger('wb-ajax-done');

    }
//--------------------------------------------//////////////////////////
    function filemanagerCallEditor(file) {
        wbapp.loading();
        var res = false;
        var fname, tab;
        var editor = $('#filemanagerEditor').data('editor');

        if (editor == undefined) {
            wbapp.toast('Error!', 'Codemirror module not found!', { bgcolor: 'danger' });
            return;
        }
        filemanagerStateSave();
        var tabact = $("#filemanagerTabs").find(".nav-link.active");
        if ($(tabact).length) {
            if ($(tabact).attr('data-path') !== urlencode(file)) {filemanagerStateSave(tabact);}
            $(tabact).removeClass("active");
        }
        $("#filemanagerTabs .nav-link").each(function() {
            if ($(this).attr("data-path") == urlencode(file)) {
                $("#filemanagerTabs").data('path', file);
                filemanagerStateLoad(file);
                $(this).addClass("active");
                res = true;
            }
        });

        if (res == false) {
                var fname = explode("/", file);
                fname = fname[fname.length - 1];
                var tab = $($("#filemanagerTabs").data("tab"));
                let text = wbapp.postSync("/module/filemanager/getfile/", { file: file })
                editor.setValue( text );
                editor.clearHistory();
                editor.setOption('mode','php');
                $("#filemanagerTabs").data('path',file);
                $('#filemanagerModalSrc .modal-title span').text(file);
                filemanagerStateSave();
                $(tab).find(".nav-link")
                .prepend(fname)
                .addClass("active")
                .attr("href", "#")
                .attr("data-path", urlencode(file));
                $("#filemanagerTabs").append($(tab));
        }
        

        filemanagerEditorSize();
        if (!$("#filemanagerModalSrc:visible").length) {
            $("#filemanagerModalSrc").modal("show");
        }
        
        $("#filemanagerTabs").undelegate(".fa-close", "click");
        $("#filemanagerTabs").delegate(".fa-close", "click", function() {

            $(this).parents(".nav-item").remove();
            if (!$("#filemanagerTabs .nav-item").length) {
                $("#filemanagerModalSrc").modal("hide");
            } else {
                $("#filemanagerTabs").find(".nav-item,.nav-link").removeClass("active");
                $("#filemanagerTabs").find(".nav-item:eq(0) .nav-link").trigger("click")
                filemanagerStateLoad(urldecode($("#filemanagerTabs .nav-item:eq(0) .nav-link").attr('data-path')));
            }
        });

        $("#filemanagerModalSrc").undelegate(".btn-edit-save", "click");
        $("#filemanagerModalSrc").delegate(".btn-edit-save", "click", function () {
            filemanagerSave();
        });

        $("#filemanagerTabs").undelegate(".nav-link:not(.active)", "click");
        $("#filemanagerTabs").delegate(".nav-link:not(.active)", "click", function() {
            filemanagerStateSave();
            filemanagerStateLoad(urldecode($(this).attr('data-path')));
        });



    }

    function filemanageListBuffer() {
        var spath = $("#filemanager").data("bufferpath");
        var list = [];
        $($("#filemanager").data("buffer")).each(function() {
            var type = "file";
            if ($(this).hasClass("dir")) {
                type = "dir";
            }
            if ($(this).hasClass("dir1")) {
                type = "dir";
            }
            if ($(this).hasClass("file")) {
                type = "file";
            }
            if ($(this).hasClass("file1")) {
                type = "file";
            }
            var item = {
                name: $(this).attr("data-name"),
                path: spath,
                type: type
            };
            list.push(item);
        });
        return list;
    }

    function filemanagerPaste() {
        var dpath = $("#filemanager #list").data("path");
        var method = $("#filemanager").data("buffertype");
        wbapp.post("/module/filemanager/dialog/paste", {
            list: filemanageListBuffer(),
            method: method,
            path: dpath
        }, function(data) {
            data = json_decode(data);
            $("#filemanager").data("post", data.post);
            if (data.res == "dialog") {
                $("#filemanager #filemanagerModalDialog").remove();
                $("#filemanager").append(data.action);
                $("#filemanager #filemanagerModalDialog").modal("show");
            }
            if (data.action == "reload_list") {
                filemanager_reload_list();
            }
        });
    }

    function filemanager_reload_list() {
        $("#filemanager").find(".breadcrumb .breadcrumb-item.active > a").trigger("click");
    }

    function filemanagerSave() {
        var editor = $('#filemanagerEditor').data('editor');
        var file = urldecode($("#filemanagerTabs").find(".nav-link.active").attr('data-path'));
        var data = wbapp.postSync("/module/filemanager/putfile/", {
            file: file,
            text: editor.getValue()
        });
        data = json_decode(data);
        if (data.result == true) {
            wbapp.toast('Ready','Saved',{'bgcolor':'success'});
        } else {
            wbapp.toast('Error', 'Error in saving', { 'bgcolor': 'danger' });
        }
    }


    function filemanagerEditorSize() {
        var editor = $('#filemanagerEditor').data('editor');
        var height = $(window).height();
        height = height - $('#filemanagerModalSrc .modal-header').height();
        height = height - $('#filemanagerModalSrc #filemanagerTabs').height();
        editor.setSize('100vw', height+'px');
    }


    function filemanagerStateSave() {
        var editor = $('#filemanagerEditor').data('editor');
        var path = $('#filemanagerTabs').data('path');
        var tabs = $('#filemanagerTabs');
        if (path !== undefined) {
            tabs.data('editorValue:' + path, editor.getValue());
            tabs.data("editorHistory:" + path, editor.getHistory());
            tabs.data("editorCursor:" + path, editor.getCursor());
        }

    }

    function filemanagerStateLoad(path) {
        var editor = $('#filemanagerEditor').data('editor');
        var tabs = $('#filemanagerTabs');
        if (path !== undefined) {
            let cur = $('#filemanagerTabs').data('editorCursor:' + path);
            if (cur == undefined) cur = {line:0,ch:0}

            let his = $('#filemanagerTabs').data('editorHistory:' + path);
            if (his == undefined) his = {}

            editor.setValue($('#filemanagerTabs').data('editorValue:' + path));
            $('#filemanagerModalSrc .modal-title span').text(path);
            setTimeout(() => {
                editor.focus();
                editor.setCursor(cur)
                editor.setHistory(his)
                filemanagerEditorSize()
            },10);
            tabs.data('path', path);
            //editor.getSession().setUndoManager($(tab).data("editorUndo"));
  //          var pos = $(tab).data("editorPos");
    //        editor.gotoLine(pos["row"] + 1, pos["column"]);
        }
    }
