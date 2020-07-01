<html>
<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">Основные настройки</a>
</nav>
<wb-data wb="{'table':'_settings','item':'settings'}">
  <form class="m-3">
    <div>
    </div>
    <fieldset class="form-group">
      <label for="_setSiteHeader">Название сайта</label>
      <input type="text" class="form-control" name="header" id="_setSiteHeader" placeholder="Заголовок">
    </fieldset>
    <fieldset class="form-group">
      <label for="_setSiteEmail">Эл.почта</label>
      <input type="text" class="form-control" name="email" id="_setSiteEmail" placeholder="Эл.почта">
    </fieldset>

    <div class="row">
      <div class="col-sm-5">
        <fieldset class="form-group">
          <label for="_setSiteLogo">Логотип</label>
          <input wb-module="module=filepicker&mode=single" name="logo">
        </fieldset>
      </div>
      <div class="col-sm-7">
        <div class="form-group row">
          <label class="col-sm-5 form-control-label">Надпись (I часть)</label>
          <div class="col-sm-7">
            <input type="text" class="form-control" name="logo1" placeholder="Надпись (I часть)">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-5 form-control-label">Надпись (II часть)</label>
          <div class="col-sm-7">
            <input type="text" class="form-control" name="logo2" placeholder="Надпись (II часть)">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-5 form-control-label">Размер шрифта (px)</label>
          <div class="col-sm-7">
            <input type="number" class="form-control" name="logofontsize" min="12" max="30" step="1" placeholder="Размер шрифта (px)">
          </div>
        </div>
      </div>
    </div>

    <nav class="text-right">
      <button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
        <i class="fa fa-save"></i> Сохранить
      </button>
    </nav>
  </form>
</wb-data>

</html>
