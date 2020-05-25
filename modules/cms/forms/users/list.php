<html>

<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">Пользователи</a>
  <button class="navbar-toggler order-2" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <i class="wd-20 ht-20 fa fa-ellipsis-v"></i>
  </button>

  <div class="collapse navbar-collapse order-2" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="#">Все <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Активные</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Скрытые</a>
      </li>
    </ul>
    <form class="form-inline mg-t-10 mg-lg-0">
      <input class="form-control wd-150-f mg-r-5" type="search" placeholder="Поиск..." aria-label="Поиск...">
      <button class="btn btn-success" type="submit"
      data-ajax="{'url':'/cms/ajax/form/users/edit/_new','html':'.users-edit-modal','modal':'#{{_form}}ModalEdit','callback':'ajaxModalShow'}">Создать</button>
    </form>
  </div>
</nav>


<div class="list-group m-2">
  <wb-foreach wb-table="users" wb-orm="sortBy('id')">
    <div class="list-group-item d-flex align-items-center">
      <wb-var edit="{'url':'/cms/ajax/form/users/edit/{{_id}}','html':'.users-edit-modal','modal':'#{{_form}}ModalEdit','callback':'ajaxModalShow'}"/>
      <div>
        <a href="javascript:" data-ajax="{{_var.edit}}" class="tx-13 tx-inverse tx-semibold mg-b-0">{{_id}}</a>
        <span class="d-block tx-11 text-muted">{{header}}&nbsp;</span>
      </div>
      <a href="javascript:" data-ajax="{{_var.edit}}" class="pos-absolute r-40"><i class="fa fa-edit"></i></a>
      <div class="dropdown dropright pos-absolute r-10 p-0 m-0" style="line-height: normal;">
          <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-ellipsis-v"></i>
          </a>
        <div class="dropdown-menu" >
          <a class="dropdown-item" href="#" data-ajax="{{_var.edit}}">Изменить</a>
          <a class="dropdown-item" href="#">Переименовать</a>
          <a class="dropdown-item" href="#">Удалить</a>
        </div>
      </div>
    </div>
  </wb-foreach>
</div>
<div class="users-edit-modal">

</div>
<script>
  function ajaxModalShow(params,data) {
    $(params.modal).modal('show');
  }
</script>
</html>
