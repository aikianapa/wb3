<html>
<div class="formgroup row">
    <label class="form-control-label col-sm-4">{{_lang.editor}}</label>
    <div class="col-sm-8">
        <select name="editor" class="form-control">
            <wb-foreach wb-from="_env.editors">
                <option value="{{name}}">{{label}}</option>
            </wb-foreach>
        </select>
    </div>
</div>

<wb-lang>
    [en]
    editor = "Default editor"
    [ru]
    editor = "Редактор по-умолчанию"
</wb-lang>

</html>