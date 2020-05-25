<html>
<div class="modal fade effect-scale removable" id="{{_form}}ModalEdit" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <i class="fa fa-close wd-20"  data-dismiss="modal" aria-label="Close"></i>

          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="{{_form}}SwitchItemActive" onchange="$('#{{_form}}ValueItemActive').prop($(this).prop());">
            <label class="custom-control-label" for="{{_form}}SwitchItemActive">Активирован</label>
          </div>


      </div>
      <div class="modal-body pd-20">

        <form id="{{_form}}EditForm">
          <input type="checkbox" class="custom-control-input" id="#{{_form}}ValueItemActive">
          <div class="form-group row">
          <div class="input-group col-12">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fa fa-user"></i></span>
            </div>
            <input type="text" name="id" class="form-control" placeholder="Идентификатор">
            <div class="input-group-append d-sm-flex d-none">
              <span class="input-group-text"><i class="fa fa-users"></i></span>
            </div>
            <div class="input-group-append">
              <select class="btn btn-outline-light input-group-text" name="group">
                <option class="dropdown-item" value="admin">Администратор</option>
                <option class="dropdown-item" value="user">Пользователь</option>
              </select>
            </div>
          </div>
          </div>

          <div class="form-group row">
            <div class="input-group col-sm-6 col-12">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-at"></i></span>
              </div>
              <input type="text" name="email" class="form-control" placeholder="Электронная почта">
            </div>
            <p class="d-block d-sm-none p-1" />
            <div class="input-group col-sm-6 col-12">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-phone"></i></span>
              </div>
              <input type="text" name="phone" class="form-control" placeholder="Телефон">
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
              <wb-module wb="{'module':'jodit'}" name="text"/>
            </div>
          </div>


        </form>

      </div>
      <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
        <button type="button" class="btn btn-secondary tx-13" data-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary tx-13" wb-save="{'table':'{{_table}}','id':'{{_id}}','form':'#{{_form}}EditForm'}">Сохранить</button>
      </div>
    </div>
  </div>
</div>
</html>
