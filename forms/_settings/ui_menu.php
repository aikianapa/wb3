<html>
<wb-data wb="{'table':'_settings','item':'settings'}">
  <form>

    <nav class="nav navbar navbar-expand-md col px-0 t-0 position-sticky " style="z-index:1;background:#f8f9fc;">
      <h3 class="tx-bold tx-spacing--2 order-1">{{_lang.menu}}</h3>
      <div class="ml-auto order-2 float-right">
        <button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
          <i class="fa fa-save"></i> {{_lang.save}}
        </button>
      </div>
    </nav>

    <input wb-tree name="cmsmenu">

  </form>
</wb-data>
<wb-lang>
  [en]
  save = Save
  menu = CMS menu
  [ru]
  save = Сохранить
  menu = Меню CMS
</wb-lang>

</html>