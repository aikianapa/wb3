<a href="javascript:void(0);" class="mod-swico-{{swico_id}}">
    <input type="checkbox" class="mod-swico-input" name id="{{swico_id}}">
    <label class="mod-swico-label" for="{{swico_id}}"></label>
<style wb-module="scss">
.mod-swico-{{swico_id}} {
display: inline-block;
.mod-swico-input {
display:none;
}
.mod-swico-label {
margin:0;
padding:0;
background-image: url('{{swico_off}}');
width: {{swico_size}}px;
height: {{swico_size}}px;
cursor: pointer;
vertical-align: middle;
}
.mod-swico-input:checked+.mod-swico-label {
background-image: url('{{swico_on}}');
}
}
</style>
</a>