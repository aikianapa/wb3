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
    <nav class="text-right">
      <button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
        <i class="fa fa-save"></i> Сохранить
      </button>
    </nav>
  </form>
</wb-data>

</html>
