<html>
<div class="modal fade effect-scale show removable" id="modalPagesEdit" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <i class="fa fa-close wd-20"  data-dismiss="modal" aria-label="Close"></i>
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}SwitchItemActive" onchange="$('#{{_form}}ValueItemActive').prop('checked',$(this).prop('checked'));">
            <label class="custom-control-label" for="{{_form}}SwitchItemActive">Активирован</label>
          </div>
      </div>
      <div class="modal-body pd-20">

        <form id="{{_form}}EditForm">
          <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}ValueItemActive">
          <div class="form-group row">
          <div class="input-group col-12">
            <div class="input-group-prepend">
              <span class="input-group-text">{{_route.host}}/</span>
            </div>
            <input type="text" name="id" class="form-control" placeholder="Страница">
          </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-2 form-control-label">Заголовок</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="header" placeholder="Заголовок">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-12 form-control-label">Текст</label>
            <div class="col-12">
              <wb-module wb="{'module':'jodit'}" name="text"/>
            </div>
          </div>


        </form>

      </div>
      <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
        <button type="button" class="btn btn-secondary tx-13" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary tx-13" wb-save="{'table':'{{_table}}','id':'{{_id}}','form':'#{{_form}}EditForm','update':'cms.list.{{_form}}' }">Сохранить</button>
      </div>
    </div>
  </div>
</div>
</html>
