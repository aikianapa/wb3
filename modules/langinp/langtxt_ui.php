<html>
    <div class="input-group dropdown mod-langinp mod-langinp-init">

            <div class="input-group-prepend" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" on-click="dropdown">
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
                                <textarea class="form-control" rows="auto" data-lang="{{_key}}" on-change="edit" name="{{_key}}" placeholder="{{label}}"></textarea>
                            </div>
                        </div>

                    </wb-foreach>
            </div>

        <textarea type="json" class="mod-langinp-data d-none"></textarea>
        <textarea class="form-control mod-langinp" type="text" data-lang="{{_sess.lang}}" on-change="edit" name="label"></textarea>
    </div>
    <script wb-app remove>
        wbapp.loadScripts(["/engine/modules/langinp/langinp_mod.js"], '', function(){
            modLangInp()
        });
    </script>
</html>