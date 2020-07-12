var test;
$(document).on("filepicker-js", function() {
  wbapp.loadStyles([
    "/engine/modules/filepicker/assets/css/cropper.min.css",
    "/engine/modules/filepicker/assets/css/fileicons.css",
    "/engine/modules/filepicker/assets/css/filepicker.css",
    "/engine/modules/filepicker/filepicker.less"
  ]);

  wbapp.loadScripts([
    "/engine/modules/filepicker/assets/js/cropper.min.js",
    "/engine/modules/filepicker/assets/js/filepicker.js",
    "/engine/modules/filepicker/assets/js/filepicker-drop.js",
    "/engine/modules/filepicker/assets/js/filepicker-crop.js",
    "/engine/modules/filepicker/assets/js/filepicker-camera.js"
  ],"filepicker-init");
});

$(document).on("filepicker-init", function() {
      var selector = ".filepicker:not([done])";
      var uploader = "/engine/modules/filepicker/uploader/index.php";
      var size = 200;
      var width = size*1;
      var height = size*1;

      $(document).find(selector).each(async function(){
            let $filepicker = $(this);
            let $listview = $(this).find(".listview");
            let input = $filepicker.find(".filepicker-data");
            let inpfile = $filepicker.find("input[type=file]");
            let save;
            let field = "images";
            var path = $filepicker.find("[name=upload_url]").val();

            $filepicker.prop("done",true);

            var getparams = function() {
              let params = {};
              $filepicker.find(".fileinput input[type=hidden]").each(function(i){
                  params[$(this).attr("name")] = $(this).val();
              });
              $filepicker.params = params;
              return params;
            }

            var getdir = function(file) {
                let dir = explode("/",file);
                dir.pop();
                dir = implode("/",dir)+"/";
                return dir;
            }

            var getimg = function(file) {
                let dir = explode("/",file);
                return dir.pop();
            }

            var getthumb = function (file,update = false) {
              var thumb;
              if (update == true) {
                thumb = "/thumbc/"+width+"x"+height+"/src"+file+"?"+wbapp.newId();
              } else {
                thumb = "/thumbc/"+width+"x"+height+"/src"+file;
              }
              return thumb;
            }

            var setdata = function(update = false) {
              let data = [];
              $filepicker.find(".listview img[data-src]").each(function(){
                  let file = explode("?",$(this).attr("data-src"));
                  file = file[0];
                  file = $(this).attr("data-src");
                  let tmp = file;
                  if (tmp.split("/").length == 1) file = path + file;
                  let thumb = getthumb(file,update);
                  let src = $(this).attr("src");
                  if  (!src) src = "";
                  src = explode("?",src);
                  src = src[0];
                  if (update || src !== thumb) {
                      $(this).attr("src",thumb).attr("data-src",file);
                  }
                  data.push({
                      img: file,
                      alt:  $(this).attr("alt"),
                      title:  $(this).attr("title"),
                  });
              });
              input.html(json_encode(data));
              //$filepicker.find("[name=prevent_img]").val("");
              //$filepicker.find("[name=upload_url]").val("");
            }

            var fixoldimg = function() {
                let path = $filepicker.find("[name=upload_url]").val();
                $($filepicker.list).each(function(i,img){
                    let tmp = img.img;
                    if (tmp !== undefined && tmp.split('/').length == 1) {
                        img.img = path + img.img;
                        $filepicker.list[i] = img;
                    }
                });
            }


            var listview = function() {
                let lvid = "lv-"+wbapp.newId();
                let tpl = wbapp.template["#fp-listviewItem"].html;
                $filepicker.list = input.html();
                if ($filepicker.list == "") {
                    $filepicker.list = [];
                } else {
                    $filepicker.list = json_decode($filepicker.list);
                }
                if (!$(inpfile).is("[multiple]") && $filepicker.list.length) {
                    $filepicker.list = [$filepicker.list[0]];
                }
                fixoldimg();
                $listview.attr("id",lvid);
                var ractive = Ractive({
                  target: '#'+lvid,
                  template: tpl,
                  data: {
                    images:$filepicker.list
                  }
                })
                $filepicker.ractive = ractive;
                setdata();

                if ($(inpfile).is("[multiple]")) {
                  $listview.sortable({
                    update: function(){
                        setdata(false);
                    }
                  });
                }


                $filepicker.filePicker({
                    url: uploader
                    ,data: getparams()
                    ,ui: {
                        autoUpload: true
                    }
                    ,plugins: ['ui', 'drop', 'camera', 'crop']
                }).on('done.filepicker', function (e, data) {
                  if (data.files[0].original == null && data.files[0].size == 0) {
                      // ошибка загрузки
                      let j=0;
                      $.each($filepicker.list,function(i,line){
                          if (line.name !== undefined && line.name == data.files[0].name) {
                              let $card = $listview.find(".card:eq("+j+")");
                              $card.children("img")
                                  .attr("src","/engine/modules/filepicker/assets/img/error.png")
                                  .removeAttr("loading");
                              setTimeout(function(){
                                  $card.remove();
                              },2000)
                              return;
                          }
                          j++;
                      });
                  } else {
                    if (!$(inpfile).is("[multiple]")) {
                        let oldfile = $filepicker.find("[name=prevent_img]").val();
                        if (oldfile > "" && data.files[0].url !== undefined) {
                            fp.delete(oldfile).done(function(){
                                $filepicker.find("[name=prevent_img]").val(data.files[0].url);
                            });
                        }
                        $filepicker.list = {"0":{
                          img: data.files[0].url,
                          name: data.files[0].name,
                          title: "",
                          alt: ""
                        }};
                    } else {
                      let j = 0;
                      $.each($filepicker.list,function(i,line){
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
                    ractive.set({images:$filepicker.list});
                    setdata();
                  }
                }).on('cropsave.filepicker', function (e,file) {
                    let thumb = getthumb(file.url,true);
                    $filepicker.find(".listview .card img[data-src='"+file.url+"']").attr("src",thumb);
                }).on('add.filepicker', function(e,data){
                    if (data.files[0].name !== undefined) {
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
                    fixoldimg();
                    $filepicker.ractive.set({images:$filepicker.list});
                    setdata();

                  }).on('fail.filepicker', function(e,data){
                    $filepicker.find(".listview img[data-img='"+data.files[0].name+"']")
                        .attr("src","/engine/modules/filepicker/assets/img/error.png")
                        .attr("title","Error")
                        .on("click",function(){$(this).parent(".card").remove();});
                  }).on('uploadfallback.filepicker', function(e,data){
                      console.log(data);
                  });



                var fp = $filepicker.filePicker();


                $filepicker.delegate("a.delete","click",function(){
                    var card = $(this).closest(".card");
                    var file = $(card).find("img").attr("data-src");
                    if (file == "") {
                        delete $filepicker.list[card.index()];
                        card.remove();
                    } else {
                      fp.delete(file).done(function(){
                            $.each($filepicker.list,function(i,line){
                              if (line !== undefined) {
                                if (line.img !== undefined && line.img == file) {
                                  if ($(inpfile).is("[multiple]")) {
                                      $filepicker.list.splice(i, 1);
                                  } else {
                                      $filepicker.list = [];
                                  }
                                }
                              }
                            })
                            $filepicker.ractive.set({images:$filepicker.list});
                            setdata();
                      });
                    }
                    return false;
                });

                $filepicker.delegate("a.crop","click",function(){
                    var card = $(this).closest(".card");
                    var file = $(card).find("img").attr("data-src");
                    $filepicker.find("[name=prevent_img]").val(getimg(file));
                    $filepicker.find("[name=upload_url]").val(getdir(file));
                    fp.plugins.crop.show(file);
                    return false;
                });

                $listview.delegate("img","load",function(){
                  $(this).removeAttr("loading")
                })
            }
            listview();
      });
});
