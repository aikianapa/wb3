<html>
<wb-include wb="{'src':'/engine/modules/cms/tpl/wrapper.inc.php'}" />
<div class="modal d-inline" id="" tabindex="-1" role="dialog" aria-labelledby=""
  aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
          <a href="#" class="aside-logo">
          <img src="/engine/modules/cms/tpl/assets/img/virus.svg" width="30">
          Pan<span>demic</span>
          </a>
        </h4>
      </div>
      <div class="modal-body">
        <form class="mg-t-10 mg-lg-0" id="formLogin" action="/ajax/auth/email" method="post">
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
              </div>
              <input name="login" class="form-control" type="text" placeholder="Логин">
            </div>
          </div>
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-unlock"></i></span>
              </div>
              <input name="password" class="form-control" type="password" placeholder="Пароль">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onClick="wbapp.auth('#formLogin')">
          <i class="fa fa-sign-in"></i>&nbsp; Вход
        </button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

</html>
