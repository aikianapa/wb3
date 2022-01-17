
    <div class="input-group">
        <div class="dropdown mod-langinp">
            <div class="input-group-prepend" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="input-group-text text-blue px-1" style='border-radius:0.25rem 0rem 0rem 0.25rem;'>
                    <img data-src="/module/myicons/language-translate.5.svg?size=24&stroke=323232" width="24" height="24">
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
                                <input class="form-control" type="text" data-name="{{_key}}" name="{{_key}}" placeholder="{{label}}">
                            </div>
                        </div>

                    </wb-foreach>
            </div>
        </div>
        <textarea type="json" class="mod-langinp d-none" name="lang"></textarea>
        <input class="form-control mod-langinp" type="text" name="label">
        <script wb-app remove>
            wbapp.loadScripts(["/engine/modules/langinp/langinp_mod.js"],"langinp-mod-js");
        </script>
    </div>

