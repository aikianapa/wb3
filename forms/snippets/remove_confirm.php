<html>
<div class="modal fade show " id="confirm_{{_form}}_{{_item}}" data-keyboard="false" data-backdrop="true" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Удаление записи</h5>
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
				Пожалуйста, подтвердите удаление записи с идентификатором {{_item}} из таблицы {{_form}}
			</div>
		</div>
          <div class="alert alert-warning text-center d-none" style="margin-top:20px;">ВНИМАНИЕ! Ошибка удаления записи</div>
      </div>

		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> Отмена</button>
			<button type="button" class="btn btn-danger" data-dismiss="modal"
					   data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{_item}}?_confirm'}">
					 <span class="fa fa-trash"></span> Удалить
			</button>
		  </div>
		</div>
</div>
</div>
<script type="text/locale">
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
</script>
</html>
