<html>
<div class="input-group dropdown mod-langinp mod-langinp-init">
    <div class="input-group-prepend" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="input-group-text text-blue p-1">
            <img src="/module/myicons/language-translate.5.svg?size=24&stroke=323232" width="24" height="24">
        </span>
    </div>
    <div class="dropdown-menu">
        <wb-foreach wb-from="_locales">
            <div class="dropdown-item">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text text-blue">
                            {{_key}}
                        </span>
                    </div>
                    <input class="form-control" type="text" data-lang="{{_key}}" name="{{_key}}" placeholder="{{label}}"
                        on-change="edit">
                </div>
            </div>

        </wb-foreach>
    </div>

    <textarea type="json" class="mod-langinp-data d-none"></textarea>
    <input class="form-control mod-langinp" type="text" data-lang="{{_sess.lang}}" on-change="edit" name="label">
</div>
<script wb-app remove>
wbapp.loadScripts(["/engine/modules/langinp/langinp_mod.js"], 'mod-langinp', function(){
    modLangInp()
});
</script>
</html>