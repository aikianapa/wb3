<div class="filepicker">
    <textarea type="json" name class="d-none filepicker-data"></textarea>
    <!-- Button Bar -->
    <div class="button-bar">
        <button class="btn btn-success fileinput">
            <span>
                <svg class="mi-upload-loading-arrow size-24" stroke="FFFFFF" wb-module="myicons"></svg>
                <span class="d-none d-md-inline btn-text"> {{_lang.choose}}</span>
            </span>
            <input type="file" name="files[]" class="wb-unsaved">
            <input type="hidden" name="upload_url" class="wb-unsaved">
            <input type="hidden" name="prevent_img" class="wb-unsaved">
        </button>
    </div>

    <div class="listview row py-0">

    </div>


    <template id="fp-listviewItem">
        {{#each images}}
            <div class="card p-1 m-1 rounded-10 tx-10">
                {{img}}
            </div>

        {{else}}
            <div class="card p-1 m-1 rounded-10 tx-10">
                Пусто
            </div>

        {{/each}}
    </template>



    <wb-lang>
        [en]
        camera = "Camera"
        choose = "Files"
        [ru]
        camera = "Камера"
        choose = "Файлы"
    </wb-lang>
    <script wb-app>
        wbapp.loadScripts(["/engine/modules/filepicker/filepicker.js"], "filepicker-js")
    </script>
</div>
<!-- end of #filepicker -->