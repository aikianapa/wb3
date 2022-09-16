<html>
<template type="lang">
    <form data-title="{{_lang.locales}}: " action="javascript:void(0);">
        <div class="form-group">
            <wb-multiinput name="labels">
                <div class="col-sm-5">
                    <select class="form-control" placeholder="{{_lang.lang}}" name="id">
                        <wb-foreach wb="from={{_env.locales}}">
                            <option value="{{id}}">{{_locale}}</option>
                        </wb-foreach>
                    </select>
                </div>
                <div class="col-sm-7">
                    <input class="form-control" placeholder="{{_lang.name}}" type="text" name="name">
                </div>
            </wb-multiinput>
        </div>
    </form>
</template>

<template type="enum">
    <form data-title="{{_lang.enum}}: " action="javascript:void(0);">
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.enum}}</label>
            <div class="col-sm-9">
                <input class="form-control" wb="module=tagsinput" placeholder="{{_lang.enum}}"
                    type="text" name="enum">
            </div>
        </div>
    </form>
</template>

<template type="include">
    <form data-title="{{_lang.include}}: " action="javascript:void(0);">
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.include}}</label>
            <div class="col-sm-9">
                <input class="form-control" placeholder="{{_lang.include}}" type="text" name="file">
            </div>
        </div>
    </form>
</template>

<template type="forms">
    <form data-title="{{_lang.form}}: " action="javascript:void(0);">
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.form}}</label>
            <div class="col-sm-4">
                <select name="form" class="form-control"
                    placeholder="{{_lang.form}}">
                    <wb-foreach wb="from=_env.forms">
                    <option value='{{_val}}'>{{_val}}</option>
                    </wb-foreach>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.mode}}</label>
            <div class="col-sm-4">
                <input class="form-control" placeholder="{{_lang.mode}}" type="text" name="mode">
            </div>
        </div>
    </form>
</template>


<template type="multiinput">
    <form data-title="{{_lang.multiinput}}: " action="javascript:void(0);">
        <wb-dict name="multiflds" />
    </form>
</template>

<template type="common">
    <form data-title="{{_lang.enum}}: " action="javascript:void(0);">
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.unwrap}}</label>
            <div class="col-sm-9">
                <wb-module wb="module=switch" name="unwrap" />
            </div>
        </div>


        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.class}}</label>
            <div class="col-sm-9">
                <input class="form-control" placeholder="{{_lang.class}}" type="text" name="class">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.style}}</label>
            <div class="col-sm-9">
                <input class="form-control" placeholder="{{_lang.style}}" type="text" name="style">
            </div>
        </div>
    </form>
</template>

<template type="treeselect">
    <form data-title="{{_lang.catalog}}: " action="javascript:void(0);">
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.catalog}}</label>
            <div class="col-sm-9">
                <select class="form-control" name="treeselect">
                    <wb-foreach wb="table=catalogs&sort=name">
                        <option value="{{_id}}">{{name}} [{{_id}}]</option>
                    </wb-foreach>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.branch}}</label>
            <div class="col-sm-9">
                <input class="form-control" placeholder="{{_lang.branch}}" type="text" name="branch">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Parent</label>
            <div class="col-sm-3">
                <select class="form-control" name="parent">
                    <option value="true">true</option>
                    <option value="false">false</option>
                </select> 
            </div>
            <label class="col-sm-3 form-control-label">Childrens</label>
            <div class="col-sm-3">
                <select class="form-control" name="childs">
                    <option value="true">true</option>
                    <option value="false">false</option>
                </select> 
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">{{_lang.multiple}}</label>
            <div class="col-sm-9">
                <input wb-module="switch" name="multiple">
            </div>
        </div>

    </form>
</template>



<wb-lang>
    [en]
    label = "Label"
    name = "Field name"
    type = "Field type"
    default = "Default value"
    json = "JSON data"
    css = "Style CSS"
    other = "Other"
    plugins = "Plugins"
    form = "Form"
    mode = "Mode"
    class = "Сlass"
    style = "Style"
    selector= "Selector"
    locales = "Locales"
    lang = "Language"
    prop = "Properties"
    multiinput = "Multiinput"
    enum = "Enum"
    unwrap = "Unwrap"
    langinp = "Input multi-language"
    catalog = "Catalog"
    branch = "Branch"
    multiple = "Multiple"
    include = "Include"
    [ru]
    label = "Метка"
    name = "Имя поля"
    type = "Тип поля"
    default = "Значение по-умолчанию"
    json = "Данные JSON"
    css = "Стиль CSS"
    other = "Другие"
    plugins = "Плагины"
    form = "Форма"
    mode = "Режим"
    class = "Класс"
    style = "Стиль"
    selector= "Селектор"
    locales = "Локализации"
    lang = "Язык"
    prop = "Свойства"
    multiinput = "Мультиполе"
    enum = "Перечисление"
    unwrap = "В колонку"
    langinp = "Ввод с переводом"
    catalog = "Справочник"
    branch = "Ветка"
    multiple = "Multiple"
    include = "Include"
</wb-lang>

</html>