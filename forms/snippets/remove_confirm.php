<html>
<div class="modal fade show " id="confirm_{{_form}}_{{_item}}" data-keyboard="false" data-backdrop="true" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{_lang.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<div class="row">
			<div class="col-3">
				<i class="fa fa-warning fa-4x text-danger"></i>
			</div>
			<div class="col-9">
				{{_lang.confirm}}
			</div>
		</div>
          <div class="alert alert-warning text-center d-none" style="margin-top:20px;">{{_lang.error}}</div>
      </div>

		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> {{_lang.cancel}}</button>
			<button type="button" class="btn btn-danger" data-dismiss="modal"
					   data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{_item}}?_confirm','request_type':'remove_item'}">
					 <span class="fa fa-trash"></span> {{_lang.remove}}
			</button>
		  </div>
		</div>
</div>
</div>
<wb-lang>
[en]
	title		= "Remove item"
	error 		= "WARNING! Remove item error."
	remove		= "Remove"
	cancel		= "Cancel"
	confirm		= "Confirm remove item [ {{_item}} ]<br>in form [ {{_form}} ]"
[ru]
	title		= "Удаление записи"
	error 		= "ВНИМАНИЕ! Ошибка удаления записи"
	remove		= "Удалить"
	cancel		= "Отмена"
	confirm		= "Пожалуйста, подтвердите удаление записи с идентификатором {{_item}} из таблицы {{_form}}"
</wb-lang>
</html>
