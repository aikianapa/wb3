<html>
<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">{{_lang.header}}</a>
</nav>
<wb-data wb="{'table':'_settings','item':'settings'}">
<form class="m-3">
<input wb-tree name="prop">

<nav class="text-right mt-3">
<button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
  <i class="fa fa-save"></i> {{_lang.save}}
</button>
</nav>
</form>
</wb-data>

<wb-lang>
[en]
save = Save
header = Properties
[ru]
save = Сохранить
header = Свойства
</wb-lang>
</html>
