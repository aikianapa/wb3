$(document).off("filepicker-js")
$(document).on("filepicker-js", async function() {
    if (document.filepicker !== undefined) {
        wbapp.trigger("filepicker-init");
        return;
    } else {
        wbapp.loadStyles([
            "/engine/modules/filepicker/assets/css/cropper.min.css",
            "/engine/modules/filepicker/assets/css/fileicons.css",
            "/engine/modules/filepicker/assets/css/filepicker.css",
            "/engine/modules/filepicker/filepicker.less"
        ]);

        wbapp.loadScripts([
            "/engine/modules/filepicker/assets/js/cropper.min.js",
            "/engine/modules/filepicker/assets/js/filepicker.min.js"
        ], "filepicker-load");

        $(document).on("filepicker-load", function() {
            wbapp.loadScripts([
                "/engine/modules/filepicker/assets/js/filepicker-drop.min.js",
                "/engine/modules/filepicker/assets/js/filepicker-camera.min.js",
                "/engine/modules/filepicker/assets/js/filepicker-crop.min.js",
            ], "filepicker-init");
        })

        wbapp.on("filepicker-init", function() {
            var selector = ".filepicker";
            var uploader = "/engine/wbuploader.php";
            //var uploader = "/engine/modules/filepicker/uploader/index.php";
            var size = 200;
            $(document).find(selector).each(async function() {
                if (this.done !== undefined) return;
                this.done = true;
                var width = size * 1;
                var height = size * 1;

                let $filepicker = $(this);
                let $listview = $(this).find(".listview");
                let input = $filepicker.find(".filepicker-data");
                let dpar;
                try {
                    dpar = json_decode(input.attr('data-params'));
                } catch (error) {
                    dpar = {}
                }
                input.removeAttr('data-params')
                if (dpar.width !== undefined) width = dpar.width * 1;
                if (dpar.height !== undefined) height = dpar.height * 1;
                $(this).removeAttr('data-params')
                var inpfile = $filepicker.find("input[type=file]");
                let save;
                let field = "images";
                var path = $filepicker.find("[name=upload_url]").val();
                var getparams = function() {
                    $form = $("<form/>");
                    $form.append($filepicker.find('.fileinput').clone());
                    $form.find('[wb-name]').each(function() {
                        $(this).attr('name', $(this).attr('wb-name'));
                    })
                    let params = {};
                    $form.find("input[type=hidden]").each(function(i) {
                        params[$(this).attr("name")] = $(this).val();
                        params[$(this).attr("wb-name")] = $(this).val();
                        params[$(this).attr("wb-tmp-name")] = $(this).val();
                    });
                    $filepicker.params = params;
                    return params;
                }

                var getdir = function(file) {
                    let dir = explode("/", file);
                    dir.pop();
                    dir = implode("/", dir) + "/";
                    return dir;
                }

                var getimg = function(file) {
                    let dir = explode("/", file);
                    return dir.pop();
                }

                var view = 'thumbc'; // cover
                if (dpar.view == 'contain') view = 'thumb';

                var getthumb = function(file, update = false) {
                    var thumb;
                    if (update == true) {
                        thumb = "/" + view + "/" + width + "x" + height + "/src" + file + "?" + wbapp.newId();
                    } else {
                        thumb = "/" + view + "/" + width + "x" + height + "/src" + file;
                    }
                    return thumb;
                }

                var setdata = function(update = false) {
                    let data = [];
                    $filepicker.find(".listview img[data-src]").each(function() {
                        let file = explode("?", $(this).attr("data-src"));
                        file = file[0];
                        file = $(this).attr("data-src");
                        filename = file.split('/').slice(-1);
                        let tmp = file;
                        if (tmp.split("/").length == 1) file = path + file;
                        let thumb = getthumb(file, update);
                        let src = $(this).attr("src");
                        let alt = $(this).attr("alt");
                        let title = $(this).attr("title");
                        if (!src) src = "";
                        src = explode("?", src);
                        src = src[0];
                        if (update || src !== thumb) {
                            $(this).attr("src", thumb).attr("data-src", file);
                        }
                        if (strpos(' ' + file, '.')) {
                            data.push({
                                img: file,
                                width: width,
                                height: height,
                                alt: alt,
                                title: title,
                            });
                        } else {
                            $(this).attr("data-src", 'null');
                        }
                    });
                    input.html(json_encode(data));
                    input.trigger('change');
                    //$filepicker.find("[name=prevent_img]").val("");
                    //$filepicker.find("[name=upload_url]").val("");
                }

                var fixoldimg = function() {
                    let path = $filepicker.find("[name=upload_url]").val();
                    $($filepicker.list).each(function(i, img) {
                        let tmp = img.img;
                        if (tmp !== undefined && tmp.split('/').length == 1) {
                            img.img = path + img.img;
                            $filepicker.list[i] = img;
                        }
                    });
                }

                var getList = function() {
                    $filepicker.list = input.html();

                    try {
                        $filepicker.list = json_decode($filepicker.list);
                    } catch (error) {
                        $filepicker.list = [];
                    }
                }


                var listview = function() {
                    let lvid = "lv-" + wbapp.newId();
                    if (wbapp.template["#fp-listviewItem"] == undefined) {
                        wbapp.template["#fp-listviewItem"] = { html: $(document).find("#fp-listviewItem").html(), params: {} };
                    }
                    let tpl = wbapp.template["#fp-listviewItem"].html;

                    getList();

                    if (!$(inpfile).is("[multiple]") && $filepicker.list.length) {
                        $filepicker.list = [$filepicker.list[0]];
                    }
                    fixoldimg();
                    $listview.attr("id", lvid);
                    var ractive = Ractive({
                        target: '#' + lvid,
                        template: tpl,
                        data: {
                            images: $filepicker.list
                        }
                    })
                    $filepicker.ractive = ractive;
                    setdata();

                    if ($(inpfile).is("[multiple]")) {
                        $listview.sortable({
                            update: function() {
                                setdata(false);
                            }
                        });
                    }


                    $filepicker.filePicker({
                        url: uploader,
                        data: () => { return getparams() },
                        ui: {
                            autoUpload: true
                        },
                        plugins: ['ui', 'drop', 'camera', 'crop']
                    }).on('done.filepicker', function(e, data) {
                        if (data.files[0].original == null || data.files[0].size == 0 || data.files[0].error !== undefined) {
                            // ошибка загрузки
                            let j = 0;
                            $.each($filepicker.list, function(i, line) {
                                if (line.name !== undefined && line.name == data.files[0].name) {
                                    let $card = $listview.find(".card:eq(" + j + ")");
                                    $card.find("img")
                                        .attr("src", "/engine/modules/filepicker/assets/img/error.png")
                                        .removeAttr("loading");
                                    wbapp.toast(wbapp._settings.sysmsg.error, data.files[0].error, { 'bgcolor': 'danger' });
                                    setTimeout(function() {
                                        if (!$(inpfile).is("[multiple]")) {
                                            $card.find('img').attr("src", "/thumbc/" + width + "x" + height + "/src/null");
                                        } else {
                                            $card.remove();
                                        }
                                    }, 1000)
                                    return;
                                }
                                j++;
                            });
                        } else {
                            if (!$(inpfile).is("[multiple]")) {
                                let oldfile = $filepicker.find("[name=prevent_img]").val();
                                if (oldfile > "" && data.files[0].url !== undefined) {
                                    fp.delete(oldfile).done(function() {
                                        $filepicker.find("[name=prevent_img]").val(data.files[0].url);
                                    });
                                }
                                $filepicker.list = {
                                    "0": {
                                        img: data.files[0].url,
                                        name: data.files[0].name,
                                        title: "",
                                        alt: ""
                                    }
                                };
                            } else {
                                let j = 0;
                                $.each($filepicker.list, function(i, line) {
                                    if (line.name !== undefined && line.name == data.files[0].original) {
                                        $filepicker.list[j] = {
                                            img: data.files[0].url,
                                            name: data.files[0].name,
                                            title: "",
                                            alt: "",
                                        };
                                        return;
                                    }
                                    j++;
                                });
                            }
                            fixoldimg();
                            ractive.set({ images: $filepicker.list });
                            setdata();
                        }
                        let $fibtn = $filepicker.find('button.fileinput');
                        if ($fibtn.length) {
                            $fibtn.find('> span .spinner-border').remove();
                            $fibtn.find('> span > svg').show();
                        }

                        console.log('Trigger: mod-filepicker-done');
                        $filepicker.trigger('mod-filepicker-done', $filepicker.list);
                    }).on('cropsave.filepicker', function(e, file) {
                        let thumb = getthumb(file.url, true);
                        $filepicker.find(".listview .card img[data-src='" + file.url + "']").attr("src", thumb);
                    }).on('add.filepicker', function(e, data) {
                        if (data.files[0].name !== undefined && !strpos(' ' + data.files[0].name, '_auto_undefined')) {
                            let line = {
                                img: "",
                                name: data.files[0].name,
                                title: "",
                                alt: ""
                            }
                            if ($(inpfile).is("[multiple]")) {
                                $filepicker.list.push(line);
                            } else {
                                $filepicker.list = [];
                                $filepicker.list[0] = line;
                            }
                        }
                        let $fibtn = $filepicker.find('button.fileinput:visible');
                        if ($fibtn.length) {
                            $fibtn.find('> span').prepend(wbapp.ui.spinner_sm);
                            $fibtn.find('> span > svg').hide();
                        }
                        fixoldimg();
                        $filepicker.ractive.set({ images: $filepicker.list });
                        setdata();
                        console.log('Trigger: mod-filepicker-add');
                        $filepicker.trigger('mod-filepicker-add', $filepicker.list);
                    }).on('fail.filepicker', function(e, data) {
                        let $fibtn = $filepicker.find('button.fileinput:visible');
                        if ($fibtn.length) {
                            $fibtn.find('> span .spinner-border').remove();
                            $fibtn.find('> span > svg').show();
                        }

                        $filepicker.find(".listview img[data-img='" + data.files[0].name + "']")
                            .attr("src", "/engine/modules/filepicker/assets/img/error.png")
                            .attr("title", "Error")
                            .on(wbapp.evClick, function() { $(this).parent(".card").remove(); });
                        console.log('Trigger: mod-filepicker-fail');
                        $filepicker.trigger('mod-filepicker-fail', $filepicker.list);
                    }).on('uploadfallback.filepicker', function(e, data) {
                        console.log(data);
                    });



                    var fp = $filepicker.filePicker();


                    $filepicker.delegate("a.delete", wbapp.evClick, function(e) {
                        e.stopPropagation();
                        var card = $(this).closest(".card");
                        var file = $(card).find("img").attr("data-src");
                        if (file == "") {
                            delete $filepicker.list[card.index()];
                            card.remove();
                        } else {
                            fp.delete(file).done(function(data) {
                                getList();
                                $.each($filepicker.list, function(i, line) {
                                    if (line !== undefined) {
                                        if (line.img == undefined || strpos(' ' + line.img, '_auto_undefined')) {
                                            $filepicker.list.splice(i, 1);
                                        } else if (line.img !== undefined && line.img == file) {
                                            if ($(inpfile).is("[multiple]")) {
                                                $filepicker.list.splice(i, 1);
                                            } else {
                                                $filepicker.list = [];
                                            }
                                        }
                                    }
                                })
                                $filepicker.ractive.set({ images: $filepicker.list });
                                setdata();
                            });
                        }
                        return false;
                    });

                    $filepicker.delegate("button.info", wbapp.evClick, function(e) {
                        let $card = $(this).parents('.card');
                        let $img = $card.find('img.card-img-top');
                        let $form = $card.find('form');
                        $form.find('[data-title]').val($img.attr('title'));
                        $form.find('[data-alt]').val($img.attr('alt'));
                        $form.find('[data-path]').val(urldecode($img.attr('data-src')));
                        $form.find('[data-title],[data-alt]').off('change');
                        $form.find(':input').on('change', function() {
                            $img.attr('title', $form.find('[data-title]').val());
                            $img.attr('alt', $form.find('[data-alt]').val());
                            setdata();
                        });
                        e.preventDefault();
                    });

                    $filepicker.delegate("a.crop", wbapp.evClick, function(e) {
                        e.stopPropagation();
                        var card = $(this).closest(".card");
                        var file = $(card).find("img").attr("data-src");
                        $filepicker.find("[name=prevent_img]").val(getimg(file));
                        $filepicker.find("[name=upload_url]").val(getdir(file));
                        fp.plugins.crop.show(file);
                        return false;
                    });

                    $filepicker.delegate('.card', wbapp.evClick, function(e) {
                        if ($(e.target).parents('.btn-group').length) return;
                        $(this).parents('.filepicker').find('input[type=file]').trigger('click');
                    });

                    $listview.delegate("img", "load", function() {
                        $(this).removeAttr("loading")
                    })
                }
                listview();
            });
            document.filepicker = true;
        });




    }

});