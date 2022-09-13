<div class="filepicker">
    <textarea type="json" name class="d-none filepicker-data"></textarea>
    <!-- Button Bar -->
    <div class="button-bar">
        <div class="btn btn-success fileinput d-none">
            <i class="fa fa-image"></i><span class="d-none d-md-inline btn-text"> {{_lang.choose}}</span>
            <input type="file" name="files[]" class="wb-unsaved">
            <input type="hidden" name="upload_url" class="wb-unsaved">
            <input type="hidden" name="prevent_img" class="wb-unsaved">
        </div>

        <button type="button" class="btn btn-primary camera-show" wb-if='"{{_route.scheme}}" == "https"'>
            <i class="fa fa-camera"></i><span class="d-none d-md-inline"> {{_lang.camera}}</span>
        </button>
    </div>

    <!-- Files -->
    <div class="listview row py-0">

    </div>


    <template id="fp-listviewItem">
        {{#each images}}
        <div class="card p-1 m-1 rounded-10">
            <figure class="pos-relative mg-b-0">
                <img class="card-img-top img-fluid rounded-10" width="{{width}}" height="{{height}}" data-src="{{img}}" data-img='{{name}}' loading title="{{title}}"
                    alt="{{alt}}" onload="$(this).removeAttr('loading onload')">
                <figcaption>
                    <div class="btn-group">
                        <a href="javascript:void(0)" class="btn btn-dark btn-icon action crop"><i
                                class="fa fa-crop"></i></a>
                        <div class="dropdown dropup">
                            <button type="button" class="btn btn-dark btn-icon action info rounded-0"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                    class="fa fa-info"></i></button>
                            <div class="dropdown-menu">
                                <form class="wd-250 pd-15">
                                    <div class="row">
                                        <div class="col-12">
                                            <label>title</label>
                                            <input class="form-control mg-b-2" data-title type="text"
                                                placeholder="title">
                                        </div>
                                        <div class="col-12">
                                            <label>alt</label>
                                            <input class="form-control mg-b-2" data-alt type="text" placeholder="alt">
                                        </div>
                                        <div class="col-12">
                                            <label>path</label>
                                            <input class="form-control mg-b-2" data-path="{{img}}" type="text" placeholder="path" readonly>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-dark btn-icon action delete"><i
                                class="fa fa-trash-o text-danger"></i></a>
                    </div>
                </figcaption>
            </figure>
        </div>

        {{else}}
        <div class="card p-1 m-1 rounded-10">
            <figure class="pos-relative mg-b-0">
                <img class="card-img-top rounded-10" width="{{width}}" height="{{height}}" data-src="" data-img='{{name}}' loading title="{{title}}"
                    alt="{{alt}}" onload="$(this).removeAttr('loading onload')">
                <figcaption>
                    <div class="btn-group">
                        <a href="javascript:void(0)" class="btn btn-dark btn-icon pd-2 action crop">
                            <img src="/module/myicons/crop-square.svg?size=24&stroke=FFFFFF">
                        </a>
                        <div class="dropdown dropup">
                            <button type="button" class="btn btn-dark btn-icon pd-2 action info rounded-0"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="/module/myicons/info-information.svg?size=24&stroke=FFFFFF">
                            </button>
                            <div class="dropdown-menu">
                                <form class="wd-250 pd-15">
                                    <div class="row">
                                        <div class="col-12">
                                            <label>title</label>
                                            <input class="form-control mg-b-2" data-title type="text"
                                                placeholder="title">
                                        </div>
                                        <div class="col-12">
                                            <label>alt</label>
                                            <input class="form-control mg-b-2" data-alt type="text" placeholder="alt">
                                        </div>
                                        <div class="col-12">
                                            <label>path</label>
                                            <input class="form-control mg-b-2" data-path="{{img}}" type="text" placeholder="path" readonly>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-dark btn-icon pd-2 action delete">
                            <img src="/module/myicons/trash-delete-bin-square.svg?size=24&stroke=dc3545">
                        </a>
                    </div>
                </figcaption>
            </figure>
        </div>

        {{/each}}
    </template>

    <!-- Drop Window -->
    <div class="drop-window">
        <div class="drop-window-content">
            <h3><i class="fa fa-upload"></i> Drop files to upload</h3>
        </div>
    </div>


    <!-- Crop Modal -->
    <div id="crop-modal" class="modal fade" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="close crop-hide" data-dismiss="modal">&times;</span>
                    <h4 class="modal-title">Make a selection</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning crop-loading">Loading image...</div>
                    <div class="crop-preview"></div>
                </div>
                <div class="modal-footer">
                    <div class="crop-rotate">
                        <button type="button" class="btn btn-default btn-sm crop-rotate-left" title="Rotate left">
                            <i class="fa fa-undo"></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm crop-flip-horizontal"
                            title="Flip horizontal">
                            <i class="fa fa-arrows-h"></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm crop-flip-vertical" title="Flip vertical">
                            <i class="fa fa-arrows-v"></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm crop-rotate-right" title="Rotate right">
                            <i class="fa fa-repeat"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-default crop-hide pull-left"
                        data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success crop-save" data-loading-text="Saving...">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end of #crop-modal -->

    <!-- Camera Modal -->
    <div id="camera-modal" class="modal fade" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="close" data-dismiss="modal">&times;</span>
                    <h4 class="modal-title">Take a picture</h4>
                </div>
                <div class="modal-body">
                    <div class="camera-preview"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left camera-hide"
                        data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success camera-capture">Take picture</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end of #camera-modal -->

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