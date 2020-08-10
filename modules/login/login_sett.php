  <div class="formgroup row">
          <label class="form-control-label col-sm-4">{{_lang.loginby}}</label>
          <div class="col-sm-8">
              <select class="form-control" name="loginby">
                <option value="email">email</option>
                <option value="phone">phone</option>
                <option value="userid">user id</option>
              </select>
          </div>
  </div>

  <div class="formgroup row mt-2">
      <label class="form-control-label col-sm-4">{{_lang.status}}</label>
      <div class="col-sm-8">
          <label class="switch"><input type="checkbox" wb-module='switch' name="status"><span></span></label>
      </div>
  </div>

  <div class="formgroup row mt-2">
      <label class="form-control-label col-sm-4">{{_lang.group}}</label>
      <div class="col-sm-8">
        <select class="form-control" placeholder="" name="group">
          <wb-foreach wb="{'table':'users','filter':{'isgroup': 'on'}}">
          <option class="dropdown-item" value="{{_id}}">{{name}}</option>
          </wb-foreach>
        </select>
      </div>
  </div>
<hr>
<div class="formgroup row">
<label class="form-control-label col-sm-4">{{_lang.blur}}</label>
<div class="col-sm-8">
    <div class="input-group">
      <input type="number" name="blur" class="form-control" placeholder="{{_lang.blur}}"  />
      <div class="input-group-append">
        <span class="input-group-text">px</span>
      </div>
    </div>
</div>
</div>
  <div class="formgroup row mt-2">
      <label class="form-control-label col-sm-4">{{_lang.bkg}}</label>
      <div class="col-sm-8">
          <input wb-module="module=filepicker&mode=single" name="background" data-wb-path="/uploads/_modules/login" data-wb-ext="jpg|png|gif|svg"/>
      </div>
  </div>

<wb-lang>
[en]
  loginby = "Login by"
  save = "Save"
  close = "Close"
  bkg = "Background"
  blur = "Blur"
  status = "Active without approve"
  group = "Default user role"
[ru]
  loginby = "Вход по"
  save = "Сохранить"
  close = "Закрыть"
  bkg = "Фон"
  blur = "Размытие"
  status = "Активировать без потдверждения"
  group = "Роль пользователя по-умолчанию"
</wb-lang>
