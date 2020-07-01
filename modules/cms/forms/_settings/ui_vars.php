<html>
<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">Переменные</a>
</nav>
<wb-data wb="{'table':'_settings','item':'settings'}">
<form class="m-3">
<wb-multiinput name="variables">
  <div class="col-sm-3 col-xs-12">
      <input class="form-control" placeholder="Переменная" type="text" name="var"> </div>
  <div class="col-sm-4 col-xs-12">
      <input class="form-control" placeholder="Значение" type="text" name="value"> </div>
  <div class="col-sm-5 col-xs-12">
      <input class="form-control" placeholder="Описание" type="text" name="header"> </div>
</wb-multiinput>

<nav class="text-right mt-3">
<button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
  <i class="fa fa-save"></i> Сохранить
</button>
</nav>
</form>
</wb-data>
</html>
