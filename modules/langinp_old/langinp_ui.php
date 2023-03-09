<html>
<div class="input-group dropdown mod-langinp mod-langinp-init">
    <div class="input-group-prepend" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" on-click="dropdown">
        <span class="input-group-text text-blue p-1">
            <svg class="d-inline mi mi-language-translate.5" size="24" stroke="323232" wb-on wb-module="myicons"></svg>
        </span>
    </div>
    <div class="dropdown-menu">
        <wb-foreach wb-from="_locales">
            <div class="dropdown-item px-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text text-blue">
                            {{_key}}
                        </span>
                    </div>
                    <input class="form-control" type="text" data-lang="{{_key}}" name="{{_key}}" placeholder="{{label}}" on-keyup="edit">
                </div>
            </div>

        </wb-foreach>
    </div>

    <textarea type="json" class="mod-langinp-data d-none"></textarea>
    <input class="form-control mod-langinp" type="text" data-lang="{{_sess.lang}}" on-keyup="edit" name="label">
</div>
<script wb-app remove>
wbapp.loadScripts(["/engine/modules/langinp/langinp_mod.js?{{wbNewId()}}"], 'mod-langinp', function(){
    modLangInp()
});
</script>
</html>