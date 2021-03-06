<html>
<div class="modal fade effect-scale show removable" id="{{_form}}ModalEdit" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <i class="fa fa-close wd-20"  data-dismiss="modal" aria-label="Close"></i>
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}SwitchItemActive" onchange="$('#{{_form}}ValueItemActive').prop('checked',$(this).prop('checked'));">
            <label class="custom-control-label" for="{{_form}}SwitchItemActive">{{_lang.active}}</label>
          </div>
      </div>
      <div class="modal-body pd-20">

        <form id="{{_form}}EditForm">
          <input type="checkbox" class="custom-control-input" name="active" id="{{_form}}ValueItemActive">


          <div class="form-group row">
            <label class="col-sm-2 form-control-label">{{_lang.id}}</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="_id" placeholder="{{_lang.id}}" wb="module=smartid" required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-2 form-control-label">{{_lang.name}}</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="name" placeholder="{{_lang.name}}">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-12">
              <input wb-tree name="tree">
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
<wb-lang>
[ru]
name = Наименование
active = Активный
id = Идентификатор
[en]
name = Nane
active = Active
id = ID
</wb-lang>
</html>
