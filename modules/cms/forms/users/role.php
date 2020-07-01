<html>
<div class="modal fade effect-scale show removable" id="{{_form}}ModalEdit" tabindex="-1"
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
          <input type="hidden" name="isgroup" value="on" />
          <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}ValueItemActive">
          <div class="form-group row">
            <div class="input-group col-12 col-sm-4">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-users"></i></span>
              </div>
              <input type="text" name="id" class="form-control" placeholder="Идентификатор роли">
            </div>
            <p class="d-block d-sm-none p-1" />
            <div class="input-group col-sm-8 col-12">
              <div class="input-group-prepend">
                <span class="input-group-text">Наименование</span>
              </div>
              <input type="text" name="name" class="form-control" placeholder="Наименование">
            </div>
          </div>

          <div class="form-group row">
            <div class="input-group col-sm-6 col-12">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-sign-in"></i></span>
              </div>
              <input type="text" name="url_login" class="form-control" placeholder="Точка входа">
            </div>
            <p class="d-block d-sm-none p-1" />
            <div class="input-group col-sm-6 col-12">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-sign-out"></i></span>
              </div>
              <input type="text" name="url_logout" class="form-control" placeholder="Точка выхода">
            </div>
          </div>
        </form>

      </div>
      <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
        <button type="button" class="btn btn-secondary tx-13" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary tx-13" wb-save="{'table':'{{_table}}','id':'{{_id}}','form':'#{{_form}}EditForm','update':'cms.list.roles' }">Сохранить</button>
      </div>
    </div>
  </div>
</div>

</html>
