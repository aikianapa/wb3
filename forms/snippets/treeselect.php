<select name="{{name}}" placeholder="{{label}}" style="{{style}}" class="form-control" multiple 
    wb-tree="item={{prop.treeselect}}"
    wb-parent="{{prop.parent}}"
    wb-children="{{prop.childs}}"
    wb-multiple="{{prop.multiple}}"
    wb-branch="{{prop.branch}}">
    <option value="{{id}}">{{name}}</option>
</select>
