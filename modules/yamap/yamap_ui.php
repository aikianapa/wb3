<html>
<div class="yamap">
  <wb-multiinput class="yamap_editor" name="yamap">
      <div class="col-sm-5">
        <div class="input-group input-group-sm">
          <input type="text" class="form-control finder" name="address" placeholder="Адрес">
          <div class="input-group-append find" style="cursor:alias;">
            <span class="input-group-text">
              <img src="/module/myicons/globe-search.svg?size=20&stroke=3b6998">
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-4"><input class="form-control form-control-sm" name="title"  placeholder="Описание"></div>
      <div class="col-sm-3"><input class="form-control form-control-sm" name="geopos" placeholder="Геопозиция" readonly></div>
      <input type='hidden' name='zoom'>
      <input type='hidden' name='center'>
  </wb-multiinput>
  <div class="yamap_canvas"></div>
</div>
<script type="text/wbapp" remove>
    wbapp.loadScripts(["/engine/modules/yamap/yamap.js"],"yamap-js");
</script>
<style>
  .yamap .yamap_canvas {
      opacity: 0;
      transition-duration: 0.3s;
  }
</style>
</html>