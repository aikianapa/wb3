<html>
<wb-include wb="{'src':'/engine/modules/cms/tpl/wrapper.inc.php'}" />
<div class="modal d-inline" id="" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">

                    <div class="row">
                        <div class='col-auto p-0 px-2'>
                            <img data-src="/engine/modules/cms/tpl/assets/img/virus.svg" width="50"
                                wb-if='"{{_sett.logo.0.img}}" == ""' />
                            <img data-src="{{_sett.logo.0.img}}" width="50" wb-if='"{{_sett.logo.0.img}}" > ""'>
                        </div>
                        <div class='col-auto p-0'>
                            <h2 class="tx-30 p-0 wblogo">
                                <nobr>
                                    <i class="text-dark" wb-if='"{{_sett.logo1}}" == ""'>Web</i>
                                    <i class="text-dark" wb-if='"{{_sett.logo1}}" > ""'>{{_sett.logo1}}</i>

                                    <i class="text-primary" wb-if='"{{_sett.logo2}}" > ""'>{{_sett.logo2}}</i>
                                    <i class="text-primary" wb-if='"{{_sett.logo2}}" == ""'>Basic</i>
                                </nobr><br>
                                <i class="tx-13 text-dark pt-2" wb-if='"{{_sett.slogan}}" > ""'>{{_sett.slogan}}</i>
                                <i class="tx-13 text-dark pt-2" wb-if='"{{_sett.slogan}}" == ""'>Pandemic edition</i>

                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <form class="mg-t-10 mg-lg-0" id="formLogin" action="/ajax/auth/email" method="post">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ri-user-3-line"></i></span>
                            </div>
                            <input name="login" class="form-control" type="text" placeholder="Логин">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ri-lock-unlock-line"></i></span>
                            </div>
                            <input name="password" class="form-control" type="password" placeholder="Пароль" onchange="$('.btn-primary').trigger('click');">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onClick="wbapp.auth('#formLogin')">
                    <i class="ri-login-box-line"></i>&nbsp; Вход
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

</html>