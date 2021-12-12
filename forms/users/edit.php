<html>
<div class="modal fade effect-scale show removable" id="{{_form}}ModalEdit" data-backdrop="static" tabindex="-1"
    role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa fa-close wd-20" data-dismiss="modal" aria-label="Close"></i>

                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}SwitchItemActive"
                        onchange="$('#{{_form}}ValueItemActive').prop('checked',$(this).prop('checked'));">
                    <label class="custom-control-label" for="{{_form}}SwitchItemActive">Активирован</label>
                </div>


            </div>
            <div class="modal-body pd-20">

                <form id="{{_form}}EditForm" autocomplete="off">
                    <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}ValueItemActive">
                    <div class="form-group row">
                        <div class="input-group col-sm-6" wb-allow="admin">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                            </div>
                            <input type="text" name="_id" class="form-control" readonly placeholder="Идентификатор">
                            <div class="input-group-append">
                                <span class="input-group-text dropdown-toggle" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-key"></i>
                                </span>
                                <div class="dropdown-menu p-3">
                                    <wb-include wb='form=common&mode=changePassword'>
                                </div>
                            </div>
                        </div>

                        <p class="d-block d-sm-none p-1"></p>
                        <div class="input-group col-sm-6" wb-allow="admin">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-users"></i></span>
                            </div>
                            <div class="input-group-append">
                                <select class="btn btn-outline-light bd-l-0" name="role">
                                    <wb-foreach wb="{'table':'users','filter':{'isgroup': 'on'}}">
                                        <option class="dropdown-item" value="{{_id}}">{{name}}</option>
                                    </wb-foreach>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="input-group col-sm-6 col-12">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-at"></i></span>
                            </div>
                            <input type="text" name="email" class="form-control" placeholder="Электронная почта" wb-enabled="admin">
                        </div>
                        <p class="d-block d-sm-none p-1" />
                        <div class="input-group col-sm-6 col-12">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                            </div>
                            <input type="tel" wb-mask="+9 (999) 999-99-99" name="phone" class="form-control" wb-enabled="admin"
                                placeholder="Телефон">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="input-group col-sm-6 col-12">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Имя</span>
                            </div>
                            <input type="text" name="first_name" class="form-control" placeholder="Имя">
                        </div>
                        <p class="d-block d-sm-none p-1" />
                        <div class="input-group col-sm-6 col-12">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Фамилия</span>
                            </div>
                            <input type="text" name="last_name" class="form-control" placeholder="Фамилия">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-12">
                            <wb-module wb="{'module':'jodit'}" name="text" />
                        </div>
                    </div>


                </form>
            </div>
            <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
                <wb-include wb="{'form':'common_formsave.php'}" />
            </div>
        </div>
    </div>
</div>

</html>