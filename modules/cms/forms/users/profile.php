<html>
<wb-data wb="table=users&item={{_sess.user.id}}">
<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:"><i class="ri-user-settings-line"></i> Ваш профиль</a>
  <button class="cms btn btn-success order-2" type="button"
    wb-save="{'table':'users','item':'{{id}}','form':'#userProfile','______update':'cms.user.profile' }">
    <i class="wd-20 ht-20 fa fa-save"></i> {{_lang.save}}
  </button>
</nav>

<div class="col-12 p-2">
<form id="userProfile" autocomplete="off">

<div class="row">
<div class="col-sm-8">

  <div class="form-group row">
    <div class="input-group col-12">
      <div class="input-group-prepend">
        <span class="input-group-text">Имя</span>
      </div>
      <input type="text" name="first_name" class="form-control" placeholder="Имя">
    </div>
  </div>
  <div class="form-group row">
    <div class="input-group col-12">
      <div class="input-group-prepend">
        <span class="input-group-text">Фамилия</span>
      </div>
      <input type="text" name="last_name" class="form-control" placeholder="Фамилия">
    </div>
  </div>

  <div class="form-group row">
    <div class="input-group col-12">
      <div class="input-group-prepend">
        <span class="input-group-text">Эл.почта</span>
      </div>
      <input type="text" name="email" class="form-control" placeholder="Электронная почта">
    </div>
  </div>
  <div class="form-group row">
    <div class="input-group col-12">
      <div class="input-group-prepend">
        <span class="input-group-text">Телефон</span>
      </div>
      <input type="text" name="phone" wb-mask='9 (999) 999-9999' class="form-control" placeholder="Телефон">
    </div>
  </div>
</div>

<div class="col-sm-4">
  <wb-module wb="module=filepicker&mode=single" name="avatar" />
</div>

</div>





  <div class="form-group row">
    <div class="col-12 pt-3">
      <wb-module wb="{'module':'jodit'}" name="text"/>
    </div>
  </div>


</form>

</div>
</wb-data>
<wb-lang>
[ru]
        close           = "Закрыть"
        save            = "Сохранить"
[en]
        close           = "Close"
        save            = "Save"
</wb-lang>


</html>
