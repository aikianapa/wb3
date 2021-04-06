<html>
<nav class="nav navbar navbar-expand-md col">
    <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">{{_lang.header}}</a>
</nav>

<div>
    <wb-foreach wb="call=wbListModules()&group=liter&total=_idx">
        
        <div class="divider-text" wb-if='"{{divider}}">""'>{{divider}}</div>
        
        <button type="button" class="btn btn-sm btn-light d-inline m-1" wb-if='"{{sett}}" > ""'
           data-ajax="{'url':'/module/{{id}}/_settings/','html':'#editSettingsForm'}">
           {{id}}
        </button>
    </wb-foreach>
</div>
<template id='modSettingsWrapper'>
    <div>
        <nav class="nav navbar navbar-expand-md col">
            <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">{{_lang.header}} [ {{module}} ]</a>
        </nav>
        <div>
            <wb-data wb="{'table':'_settings','item':'settings','field':'modules.{{module}}'}">
                <form class="col">
                    <div>

                    </div>
                    <nav class="mt-3">
                        <button type="button" class="btn btn-primary tx-13 r-0"
                            wb-save="{'table':'_settings','item':'settings','field':'modules.{{_parent.module}}'}">
                            <i class="fa fa-save"></i> {{_lang.save}}
                        </button>
                    </nav>

                </form>
            </wb-data>
        </div>
        <wb-lang>
            [en]
            save = "Save"
            header = "Module settings"
            [ru]
            save = "Сохранить"
            header = "Настройки модуля"
        </wb-lang>
    </div>
</template>

<wb-lang>
    [en]
    header = "Modules settings"
    [ru]
    header = "Настройки модулей"
</wb-lang>

</html>