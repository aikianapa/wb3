<button type="button" class="cms btn-close btn btn-danger" data-dismiss="modal">
        <svg wb-module="myicons" class="mi mi-interface-essential-110 size-20" stroke="FFFFFF"></svg>&nbsp;
        {{_lang.close}}
</button>
<button type="button" class="cms btn-save btn btn-primary" wb-save="{'table':'{{_form}}','item':'{{_id}}','form':'#{{_form}}EditForm','update':'cms.list.{{_form}}' }">
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
