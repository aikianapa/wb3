<html>
<div class="mod-langinp mod-langinp-init">
    <span wb-off class="p-1 m-1 text-transparent rounded-sm pos-absolute wd-30 ht-30 btn btn-secondary switch" on-click="switch">
        {{lang}}
    </span>

    <textarea type="json" class="mod-langinp-data d-none"></textarea>
    <textarea class="pl-5 form-control mod-langinp" type="text" data-lang="{{_sess.lang}}" on-change="edit" on-keyup="keyup"></textarea>
</div>
<script wb-app remove>
    wbapp.loadScripts(["/engine/modules/langinp/langinp_mod.js"], 'mod-langinp', function() {
        modLangInp()
    });
</script>

</html>