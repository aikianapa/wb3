$(document).on("yamap-js", function () {
  wbapp.loadScripts([
    "https://api-maps.yandex.ru/2.1/?apikey=1a2c9fab-5efe-4df5-9b87-4dab6b652569&lang=ru_RU"
  ], "yandex-map-js");
});

$(document).on("yandex-map-js", function () {

  function yamap() {

    $.fn.yamap_autozoom = function (offset = 0) {
      if ($(this).find('.yamap_canvas').data("props") == undefined) return;
      let map = $(this).find('.yamap_canvas').data("props").map;
      var multi = $(this).find('wb-multiinput');
      setTimeout(function () {
        var bounds = map.geoObjects.getBounds();
        if (!bounds) return;
        if (bounds[0][0] == bounds[1][0] && bounds[0][1] == bounds[1][1]) return;
        map.setBounds(bounds, {
          checkZoomRange: true,
          zoomMargin: 10
        });
        var state = map.action.getCurrentState();
        map.setZoom(state.zoom + offset);
        map.setCenter(state.globalPixelCenter);
        $(multi).find('[wb-name=zoom]').val(state.zoom + offset);
        $(multi).find('[wb-name=center]').val(state.globalPixelCenter);
      }, 400);
    }

    $.fn.yamap_canvas = function () {
      var yamap = $(this).parents(".yamap");
      var mid = $(this).attr("id");
      var editor = false;
      var points = [];
      var epoints = [];
      var cc = [];
      var canvas = this;
      var zoom = $(this).attr("zoom") * 1;
      var autozoom = true;

      $(canvas).data("props", {});
      if (!$(yamap).find(".wb-multiinput-data").length) {
        if ($(yamap).find('geopos').length) {
          $(yamap).find('geopos').each(function (i) {
            try {
              var geo = json_decode($(this).attr("data"));
              if ($(this).html().trim() > " ") {
                geo.address = $(this).html();
              }
            } catch (error) {
              var geo = null;
            }
            if (geo) epoints.push(geo);
            $(this).remove();
          });
          epoints = json_encode(epoints);
        } else {
            epoints = $(this).html();
            $(this).html("");
        }

      } else if ($(yamap).find(".yamap_editor").length) {
        editor = true;
        epoints = $(yamap).find(".yamap_editor").find(".wb-multiinput-data").text();
      }
      try {
        epoints = json_decode(epoints, true);
        if (epoints[0].zoom !== undefined && epoints[0].zoom > 0) zoom = epoints[0].zoom;
      } catch (error) {
        epoints = [];
      }

      if ($(this).attr("geopos") > "") var ll = yamap_pos($(this).attr("geopos"));
      if ($(this).attr("center") > "") var cc = yamap_pos($(this).attr("center"));

      if ($(this).attr("width") > "") $(this).width($(this).attr("width"));
      if ($(this).attr("name") > "") $(this).find(".yamap_data").attr("name", $(this).attr("name"));

      var height = $(this).height();
      if (height == 0) {
        height = 300;
        $(this).height(height);
      }

      $(window).on("resize", function () {
        $(this).height = height;
      });


      if ($(epoints).length) {
        $(epoints).each(function (i, item) {
          var point = {
            pos: yamap_pos(item.geopos),
            content: item.address,
            title: item.title,
            geofld: $(yamap).find(".yamap_editor input[wb-name=geopos]:eq(" + i + ")")
          };
          if (point.pos.length == 2) {
            points.push(point);
            if (i == 0) cc = point.pos;
          }
        });
      } else {
        if ($(this).find("geopos").length) {
          $(this).find("geopos").each(function (i) {
            var point = {
              pos: yamap_pos($(this).attr("value")),
              content: $(this).html(),
              title: $(this).attr("title")
            };
            if (point.pos.length == 2) {
              points.push(point);
              if (i == 0) cc = point.pos;
            }
          });
        } else if (ll !== undefined && ll > "") {
          var point = {
            pos: yamap_pos(ll),
            content: $(this).html(),
            title: $(this).attr("title")
          };
        }
      }

      var map = new ymaps.Map(mid, {
        center: cc,
        zoom: zoom,
        controls: ["zoomControl", "fullscreenControl"]
      });
      if (cc.length !== 2) var ll = yamap_pos("44.894997 37.316259");
      map.behaviors.disable("scrollZoom");
      var clusterer = new ymaps.Clusterer();
      $(canvas).data("props", {
        map: map,
        clusterer: clusterer,
        editor: editor,
        zoom: zoom
      });
      if ($(points).length) {
        $(points).each(function (i, point) {
          $(canvas).yamap_addPoint(point, zoom);
        });
      }
      $(this).yamap_geo();
      $(this).css("opacity", 1);
      if (autozoom) $(yamap).yamap_autozoom();
      if ($(yamap).attr('center') == '') {
          let center = map.getCenter();
          center = center.join(' ');
          $(yamap).attr('center', center);
      }
    }

    $.fn.yamap_addPoint = function (point, zoom) {
      let canvas = this;
      let map = $(canvas).data("props").map;
      let clusterer = $(canvas).data("props").clusterer;
      let editor = $(canvas).data("props").editor;

      let myPlacemark = new ymaps.Placemark(point.pos, {
        balloonContentHeader: point.title,
        balloonContent: point.content,
        pos: point.pos
      },
      { balloonContentLayout: point.html },
      {
        draggable: editor, // метку можно перемещать
      });
      if (point.geofld !== undefined) {
        //clusterer.remove(myPlacemark);
        $(point.geofld).data("placemark", myPlacemark);
        myPlacemark.events.add('dragend', function (e) {
          var canvasPlacemark = e.get('target');
          var pos = canvasPlacemark.geometry.getCoordinates();
          $(point.geofld).val(pos[0] + " " + pos[1]);
          $(point.geofld).parents("wb-multiinput").store();
        });
      }
      clusterer.add(myPlacemark);
      map.geoObjects.add(clusterer);
      if (zoom > 0) {
        map.setZoom(zoom);
      } else {
        $(yamap).yamap_autozoom();
      }
    }

    $.fn.yamap_geo = function () {
      let canvas = this;
      let yamap = $(this).parents(".yamap");
      let map = $(canvas).data("props").map;

      let zoom = $(canvas).data("props").zoom;
      let clusterer = $(canvas).data("props").clusterer;

      map.events.add('boundschange', function (e) {
        // отлавливает событие изменения зума на карте
        if (e.get('newZoom') !== e.get('oldZoom')) {
          $(yamap).find("[wb-name=zoom]").val(e.get('newZoom')).trigger('change');
          //          console.log('zoomchange ' + e.get('newZoom'))
        }
      })

      $(yamap).undelegate(".yamap_editor", "before_remove");
      $(yamap).delegate(".yamap_editor", "before_remove", function (e, line) {
        // Удаление точки
        var geo = $(line).find("[wb-name=geopos]");
        var myPlacemark = $(geo).data("placemark");
        if (myPlacemark !== undefined) {
          clusterer.remove(myPlacemark);
        }
        $(yamap).yamap_autozoom();
      });

      $(yamap).find(".yamap_editor").delegate(".wb-multiinput-row", "click touch", function () {
        if ($(this).find(".finder").val() > "" && $(this).find("[wb-name=geopos]").val() > "") {
          var geo = $(this).find("[wb-name=geopos]");
          var pos = explode(" ", $(geo).val());
          map.setCenter(pos);
        }
      });

      $(yamap).find(".yamap_editor").delegate(".find", "click touch", function () {
        $(this).parents(".wb-multiinput-row").find(".finder").trigger("change");
      });

      $(yamap).find(".yamap_editor").delegate(".finder", "change", function (e) {
        // Вывод точек в редакторе
        var finder = $(this).val();
        var row = $(this).parents(".wb-multiinput-row");
        var geo = $(row).find("[wb-name=geopos]");
        var addr = $(row).find("[wb-name=address]");
        var zoom = $(row).find("[wb-name=zoom]");
        var title = $(row).find("[wb-name=title]").val();

        var myPlacemark = $(geo).data("placemark");
        if (myPlacemark !== undefined) clusterer.remove(myPlacemark);

        var pos = yamap_pos($(geo).val());

        if (title == undefined) var title = "";

        ymaps.geocode(finder, {
          results: 1
        }).then(function (res) {
          var obj = res.geoObjects.get(0);
          var pos = res.geoObjects.get(0).geometry._coordinates;
          var state = map.action.getCurrentState();
          $(geo).val(implode(" ", pos));
          $(zoom).val(state.zoom);
          $(addr).val(finder);
          var point = {
            pos: pos,
            content: finder,
            title: title,
            geofld: geo
          };
          $(canvas).yamap_addPoint(point, $(zoom).val());
          map.setCenter(pos);
          if (!($(zoom).val() > 0)) {
            $(yamap).yamap_autozoom();
          }
          $(row).parents("wb-multiinput").store();
        }).fail(function (err) {

          console.log(err.message);

        });
      });
    }

    function yamap_init() {

      $(".yamap:not(.done)").each(function (i) {
        var yamap = this;
        var width = "100%";
        var zoom = 10;

        if ($(this).attr("height") > "") {
          var height = $(this).attr("height");
        } else {
          var height = $(yamap).parent().height();
        }

        $(this).addClass("done");
        if ($(this).attr("id") == undefined) {
          $(this).attr("id", "ym-" + wbapp.newId());
        }
        if ($(this).attr('height') !== undefined) height = $(this).attr('height');
        if ($(this).attr('width') !== undefined) width = $(this).attr('width');
        if ($(this).attr("zoom") !== undefined) zoom = $(this).attr("zoom");

        if ($(this).find(".wb-multiinput-row [wb-name=zoom]").length && $(this).find(".wb-multiinput-row [wb-name=zoom]").val() > "0") {
          zoom = $(this).find(".wb-multiinput-row [wb-name=zoom]").val();
        }

        var id = $(this).attr("id");
        var editor = $(this).children(".yamap-editor");
        var canvas = $(this).children(".yamap_canvas");
        var mid = "c" + id;
        $(canvas)
          .attr("id", mid)
          .css("height", height)
          .width("width", width);

        $(canvas).attr("zoom", zoom * 1);
        if ($(this).attr("center") !== undefined) {
          $(canvas).attr("center", $(this).attr("center"));
          $(canvas).yamap_canvas();
        } else {
          ymaps.geolocation.get().then(function (res) {
            $(canvas).attr("center", res.geoObjects["position"][0] + " " + res.geoObjects["position"][1]);
            $(canvas).yamap_canvas();
          });
        }
        $(yamap).on('yamap_visible', () => {
          $(yamap).yamap_autozoom();
        });
      });
    }

    yamap_init();

    setInterval(() => {
      $(".yamap").each(function (i) {
        if (this.yamap_visible == undefined) this.yamap_visible = false;
        if ($(this).is(":visible")) {
          if (this.yamap_visible == false) {
            this.yamap_visible = true;
            console.log('Trigger: yamap_visible');
            $(this).trigger('yamap_visible');
          }
        } else {
          if (this.yamap_visible == true) {
            this.yamap_visible = false;
            console.log('Trigger: yamap_invisible');
            $(this).trigger('yamap_invisible');
          }
        }
      });
    }, 100);

    $(document).on('wb-ajax-done', function (e, data) {
      if (data.module && data.module == 'yamap') {
        let canvas = $(document).find(data.target).children('.yamap_canvas');
        $(document).find(data.target).removeClass('done').children('.yamap_canvas').css('opacity',0);
        yamap_init();
        $(document).find(data.target).yamap_autozoom();
      }
    });


    console.log("Trigger: yamap-plugin");
    $(document).trigger("yamap-plugin");



    function yamap_pos(ll) {
      ll = trim(ll);
      ll = str_replace(",", " ", ll);
      ll = str_replace("  ", " ", ll);
      var tmp = explode(" ", ll);
      if (tmp.length == 2) {
        return [tmp[0], tmp[1]];
      } else {
        return [];
      }
    }

  }
  ymaps.ready(yamap);
});