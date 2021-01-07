<select name="{{name}}" placeholder="{{label}}" style="{{style}}" class="form-control">
    <wb-foreach wb="from=enum">
    <option value="{{id}}">{{name}}</option>
    </wb-foreach>
</select>