
    <div class="input-group">
        <div class="dropdown mod-langinp">
            <div class="input-group-prepend" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="input-group-text text-blue" style='border-radius:0.25rem 0rem 0rem 0.25rem;'>
                    <i class="ri-translate"></i>
                </span>
            </div>
            <div class="dropdown-menu">
                    <wb-foreach wb-from="_env.locale">
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
        <script wb-app>
            wbapp.loadScripts(["/engine/modules/langinp/langinp_mod.js?{{_env.new_id}}"],"langinp-mod-js");
        </script>
    </div>

