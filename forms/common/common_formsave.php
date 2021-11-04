<button type="button" class="cms btn btn-danger" data-dismiss="modal">
        <svg wb-module="myicons" class="mi mi-interface-essential-110 size-20" stroke="FFFFFF"></svg>&nbsp;
        {{_lang.close}}
</button>
<button type="button" class="cms btn btn-primary" wb-save="{'table':'{{_route.form}}','item':'{{_route.id}}','form':'#{{_route.form}}EditForm','update':'cms.list.{{_route.form}}' }">
        <svg wb-module="myicons" class="mi mi-floppy-save size-20" stroke="FFFFFF"></svg>&nbsp;
        {{_lang.save}}
</button>

<wb-lang>
[ru]
        close           = "Закрыть"
        save            = "Сохранить"
[en]
        close           = "Close"
        save            = "Save"
</wb-lang>
