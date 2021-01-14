<html>
<div class="dropdown dropright pos-absolute r-10 p-0 m-0" style="line-height: normal;">
    <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false">
        <i class="ri-more-2-fill"></i>
    </a>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="#"
            data-ajax="{'url':'/cms/ajax/form/{{_form}}/edit/{{_id}}','html':'.{{_form}}-edit-modal'}">
            <i class="ri-file-edit-line"></i> Изменить</a>
        <!--a class="dropdown-item" href="#">Переименовать</a-->
        <a class="dropdown-item" href="javascript:"
            data-ajax="{'url':'/ajax/rmitem/{{_form}}/{{_id}}','update':'cms.list.{{_form}}','html':'.{{_form}}-edit-modal'}">
            <i class="ri-delete-bin-2-line"></i> Удалить</a>
    </div>
</div>

<script type="text/locale">
[ru]
        edit    = "Изменить"
        remove  = "Удалить"
        rename  = "Переименовать"
[en]
        edit    = "Edit"
        remove  = "Remove"
        rename  = "Rename"
</script>
</html>