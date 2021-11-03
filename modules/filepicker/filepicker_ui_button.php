<div class="filepicker">
    <textarea type="json" name class="d-none filepicker-data"></textarea>
    <!-- Button Bar -->
    <div class="button-bar">
        <button class="btn btn-success fileinput">
            <i class="fa fa-image"></i><span class="d-none d-md-inline"> {{_lang.choose}}</span>
            <input type="file" name="files[]" class="wb-unsaved">
            <input type="hidden" name="upload_url" class="wb-unsaved">
            <input type="hidden" name="prevent_img" class="wb-unsaved">
        </button>
    </div>

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