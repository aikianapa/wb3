<html wb-allow="admin">
<div class="form-group row">
    <label class="form-control-label col-sm-4">Защита доступа к API</label>
    <div class="col-sm-8">
        <wb-module wb="module=switch" name="active" />
    </div>
</div>

<div class="form-group row">
    <label class="col-lg-4 form-control-label">Режимы свободного доступа</label>
    <div class="col-lg-8">
        <wb-multiinput name="allowmode" />
    </div>
</div>

<div class="form-group row">
    <label class="col-lg-4 form-control-label">Постоянные токены</label>
    <div class="col-lg-8">
        <wb-multiinput name="tokens">
            <div class="input-group">
                <input type="text" class="form-control" name="tokens">
                <div class="input-group-append">
                    <span class="p-1 cursor-pointer input-group-text">
                        <img src="/module/myicons/24/3b6998/key-circle.1.svg">
                    </span>
                </div>
            </div>
        </wb-multiinput>
    </div>
</div>
<div class="divider-text">Разрешения</div>
<div class="form-group">
    <div class="pr-3 ml-4 mr-5 row">

        <div class="col-sm-4">
            Таблицы
        </div>
        <div class="col-sm-4">
            Роли
        </div>
        <div class="col-4">
            Методы
        </div>
    </div>
    <wb-var roles="{{wbRoleList()}}" />
    <wb-var tables="{{wbTableList()}}" />
    <wb-multiinput name="allow">
        <div class="mb-1 col-sm-4">
            <select name="table" class="form-control" placeholder="Таблицы" wb-select2 multiple>
                <option value="*">All</option>
                <wb-foreach wb="from=_var.tables&tpl=false">
                    <option value="{{_val}}" wb-if="'{{substr({{_val}},0,1)}}' !== '_'">{{_val}}</option>
                </wb-foreach>
            </select>
        </div>
        <div class="mb-1 col-sm-4">
            <select name="role" class="form-control" placeholder="Роли" wb-select2 multiple>
                <option value="*">All</option>
                <wb-foreach wb="from=_var.roles&tpl=false">
                    <option value="{{_val}}">{{_val}}</option>
                </wb-foreach>
            </select>
        </div>
        <div class="mb-1 col-4">
            <select name="mode" wb-select2 multiple>
                <option value="*">All</option>
                <option value="c">Create</option>
                <option value="r">Read</option>
                <option value="u">Update</option>
                <option value="d">Delete</option>
                <option value="l">List</option>
                <option value="f">Func</option>
            </select>
        </div>
    </wb-multiinput>
</div>
<div class="divider-text">Запреты</div>
<div class="form-group">
    <div class="pr-3 ml-4 mr-5 row">

        <div class="col-sm-4">
            Таблицы
        </div>
        <div class="col-sm-4">
            Роли
        </div>
        <div class="col-4">
            Методы
        </div>
    </div>
    <wb-var roles="{{wbRoleList()}}" />
    <wb-var tables="{{wbTableList()}}" />
    <wb-multiinput name="disallow">
        <div class="mb-1 col-sm-4">
            <select name="table" class="form-control" placeholder="Таблицы" wb-select2 multiple>
                <option value="*">All</option>
                <wb-foreach wb="from=_var.tables&tpl=false">
                    <option value="{{_val}}" wb-if="'{{substr({{_val}},0,1)}}' !== '_'">{{_val}}</option>
                </wb-foreach>
            </select>
        </div>
        <div class="mb-1 col-sm-4">
            <select name="role" class="form-control" placeholder="Роли" wb-select2 multiple>
                <option value="*">All</option>
                <wb-foreach wb="from=_var.roles&tpl=false">
                    <option value="{{_val}}">{{_val}}</option>
                </wb-foreach>
            </select>
        </div>
        <div class="mb-1 col-4">
            <select name="mode" wb-select2 multiple>
                <option value="*">All</option>
                <option value="c">Create</option>
                <option value="r">Read</option>
                <option value="u">Update</option>
                <option value="d">Delete</option>
                <option value="l">List</option>
                <option value="f">Func</option>
            </select>
        </div>
    </wb-multiinput>
</div>

</html>