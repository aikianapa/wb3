<html>
<div class="app-filemgr">

    <h6 class="element-header">
        {{_lang.filemanager}}
    </h6>
    <div id="filemanager">
        <div class="chat-wrapper chat-wrapper-two">
            <div class="content-sidebar chat-sidebar">
                <div class="filemgr-sidebar-header">
                    <div id="filemanagerUploader" class="dropdown dropdown-icon flex-fill mg-l-10">
                        <div class="uploader">
                            <button id="pickfiles" class="btn btn-xs btn-block btn-primary">
                                {{_lang.upload}} <i class="ri-upload-2-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="filemgr-sidebar-body ps">
                    <div class="pd-t-20 pd-b-10 pd-x-10">
                        <label
                            class="tx-sans tx-uppercase tx-medium tx-10 tx-spacing-1 tx-color-03 pd-l-10">{{_lang.actions}}</label>
                        <ul class="nav nav-sidebar tx-13">
                            <li class="nav-item">
                                <a href="#refresh" class="nav-link">
                                    <i class="ri-refresh-line tx-16"></i>
                                    <span>{{_lang.refresh}}</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="#newdir" class="nav-link">
                                    <i class="ri-folder-add-line tx-16"></i>
                                    <span>{{_lang.title_new_dir}}</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="#newfile" class="nav-link">
                                    <i class="ri-file-add-line tx-16"></i>
                                    <span>{{_lang.title_new_file}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-single allow-file allow-file1"
                                data-no-ext="zip tar arj rar gzip jpg jpeg png gif tif tiff">
                                <a href="#edit" class="nav-link">
                                    <i class="fa fa-edit"></i>
                                    <span>{{_lang.edit}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-single allow-dir allow-file allow-dir1 allow-file1">
                                <a href="#rename" class="nav-link">
                                    <i class="fa fa-i-cursor"></i>
                                    <span>{{_lang.rename}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-all">
                                <a href="#remove" class="nav-link">
                                    <i class="fa fa-trash-o"></i>
                                    <span>{{_lang.remove}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-all" data-no-ext="zip">
                                <a href="#zip" class="nav-link">
                                    <i class="fa fa-file-archive-o"></i>
                                    <span>{{_lang.zip}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-single allow-file" data-ext="zip">
                                <a href="#unzip" class="nav-link">
                                    <i class="fa fa-file-archive-o"></i>
                                    <span>{{_lang.unzip}}</span>
                                </a>
                            </li>


                            <li class="nav-item hidden allow-all">
                                <a href="#copy" class="nav-link">
                                    <i class="fa fa-copy"></i>
                                    <span>{{_lang.copy}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-all">
                                <a href="#cut" class="nav-link">
                                    <i class="fa fa-cut"></i>
                                    <span>{{_lang.cut}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-buffer">
                                <a href="#paste" class="nav-link">
                                    <i class="fa fa-paste"></i>
                                    <span>{{_lang.paste}}</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <!-- content-left -->
            <div class="content-body chat-content" id="panel">
                <div class="content-body-header">
                    <div class="d-flex">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb" data-wb-role="foreach" data-wb-from="path" data-wb-tpl="false"
                                data-wb-size="false" data-wb-hide="wb">
                                <li class="breadcrumb-item" prepend="nav .breadcrumb"><a href="#" data-name=""><i
                                            class="fa fa-home"></i></a></li>
                                <wb-foreach wb="from=path">
                                    <li class="breadcrumb-item" data-name="{{_parent.path[{{_ndx}}]}}"><a
                                            href="#">{{_parent.path[{{_ndx}}]}}</a></li>
                                    <!--li class="breadcrumb-item active" aria-current="page">Data</li-->
                                </wb-foreach>
                            </ol>
                        </nav>
                    </div>
                </div>

                <table id="list" class="table table-striped tx-13">
                    <tbody>
                        <wb-foreach wb="from=result&size=10">
                            <tr class="col-12 {{type}}{{link}} {{ext}}" data-name="{{name}}" data-ext="{{ext}}">
                                <td class="valign-middle">
                                    <label class="ckbox mg-b-0">
                                        <input type="checkbox"><span></span>
                                    </label>
                                </td>
                                <td class="col name">
                                    <i class="fa {{type}} {{ext}} tx-22 tx-primary lh-0 valign-middle"></i>
                                    <span class="pd-l-5">{{name}}</span>
                                </td>
                                <td class="hidden-xs">
                                    <nobr>{{perms}}</nobr>
                                </td>
                                <td class="hidden-xs">
                                    <nobr>{{size}}</nobr>
                                </td>
                                <td class="hidden-xs-down">{{ext}}</td>
                                <td class="dropdown">
                                    <a href="#" data-toggle="dropdown" class="btn pd-y-3 tx-gray-500 hover-info"
                                        wb-if='"{{type}}"!=="back"'><i class="ri-more-2-fill"></i></a>
                                    <div class="dropdown-menu dropdown-menu-right pd-10" wb-if='"{{type}}"!=="back"'>
                                        <nav class="nav nav-style-1 flex-column">
                                            <a href="#edit" class="nav-link" wb-if='"{{type}}"=="file"'><i
                                                    class="fa fa-edit"></i> {{_lang.edit}}</a>
                                            <a href="#rendir" class="nav-link" wb-if='"{{type}}"=="dir"'><i
                                                    class="fa fa-i-cursor"></i> {{_lang.rename}}</a>
                                            <a href="#renfile" class="nav-link" wb-if='"{{type}}"=="file"'><i
                                                    class="fa fa-i-cursor"></i> {{_lang.rename}}</a>
                                            <a href="#renlink" class="nav-link"
                                                wb-if='"{{type}}"=="dir1" OR "{{type}}"=="file1"'><i
                                                    class="fa fa-i-cursor"></i> {{_lang.rename}}</a>
                                            <a href="#clone" class="nav-link" wb-if='"{{type}}"=="file"'><i
                                                    class="fa fa-copy"></i> {{_lang.duplicate}}</a>
                                            <a href="{{href}}" download="{{name}}" class="nav-link"
                                                wb-if='"{{type}}"=="file"'><i class="fa fa-download"></i>
                                                {{_lang.download}}</a>
                                            <a href="#rmfile" class="nav-link" wb-if='"{{type}}"=="file"'><i
                                                    class="fa fa-remove"></i> {{_lang.remove}}</a>
                                            <a href="#rmdir" class="nav-link" wb-if='"{{type}}"=="dir"'><i
                                                    class="fa fa-remove"></i> {{_lang.remove}}</a>
                                            <a href="#rmlink" class="nav-link"
                                                wb-if='"{{type}}"=="dir1" OR "{{type}}"=="file1"'><i
                                                    class="fa fa-trash-o"></i> {{_lang.remove}}</a>
                                        </nav>
                                    </div>
                                </td>
                            </tr>
                        </wb-foreach>
                    </tbody>
                </table>



            </div>
        </div>


        <div id="filemanagerModalDialog" class="modal fade hidden" tabindex="-1" role="dialog" aria-hidden="true">
            <meta name="newdir" title="{{_lang.title_new_dir}}" content="{{_lang.prompt_new_dir}}:<br> {{newname}}">
            <meta name="newfile" title="{{_lang.title_new_file}}" content="{{_lang.prompt_new_file}}:<br> {{newname}}">
            <meta name="rmdir" title="{{_lang.title_rmdir}}"
                content="{{_lang.prompt_rmdir}} <b>{{_post.name}}</b>? {{dirname}}" invisible="dirname">
            <meta name="rmfile" title="{{_lang.title_rmfile}}"
                content="{{_lang.prompt_rmfile}} <b>{{_post.name}}</b>? {{filename}}" invisible="filename">
            <meta name="remove" title="{{_lang.title_rmlist}}"
                content="<span class='text-danger'>{{_lang.prompt_rmlist}}</span> {{_lang.prompt_rmlist1}}? {{filename}}">
            <meta name="rendir" title="{{_lang.title_rendir}}"
                content="{{_lang.prompt_rendir}} <b>{{_post.name}}</b> {{_lang.prompt_to}}: {{dirname}} {{oldname}}"
                visible="dirname" invisible="oldname">
            <meta name="renfile" title="{{_lang.title_renfile}}"
                content="{{_lang.prompt_renfile}} <b>{{_post.name}}</b> {{_lang.prompt_to}}: {{filename}} {{oldname}}"
                visible="filename" invisible="oldname">
            <meta name="paste" title="{{_lang.title_rewrite}}" content="{{_lang.prompt_paste}}">
            <meta name="zip" title="{{_lang.title_zip}}" content="{{_lang.prompt_zip}} {{filename}}" visible="filename">
            <meta name="unzip" title="{{_lang.title_unzip}}" content="{{_lang.prompt_unzip}}">
            <input type="text" class="form-control" name="newname">
            <input type="hidden" class="form-control" name="dirname" value="{{_post.name}}">
            <input type="hidden" class="form-control" name="filename" value="{{_post.name}}">
            <input type="hidden" class="form-control" name="oldname" value="{{_post.name}}">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form></form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{_lang.cancel}}</button>
                        <button type="button" class="btn btn-primary">{{_lang.ok}}</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="filemanagerModalSrc" class="modal fade hidden" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <button class="btn btn-sm btn-default btn-edit-close" data-toggle="modal"
                    data-target="#filemanagerModalSrc"><i class="fa fa-close"></i></button>
                <div class="modal-content">
                    <div class="nav-active-primary">
                        <ul class="nav nav-tabs" role="tablist" id="filemanagerTabs">
                            <li class="nav-item"><a class="nav-link" href="" data-toggle="tab" aria-expanded="false"> <i
                                        class="fa fa-close"></a></i>
                            </li>
                        </ul>
                    </div>
                    <div id="filemanagerSrc" class="tab-content  p-a m-b-md">
                        <script type="text/template" id="filemanagerTabsContentTpl">
                            <div class="tab-pane fade show active" id="{{id}}" role="tabpanel">
                                                <div data-wb-role="module" src="editarea" data-wb-file="{{file}}" data-wb-height="500" data-wb-id="{{id}}"></div>
                                        </div>
                                </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="wbapp">
    wbapp.loadScripts(["/engine/modules/filemanager/filemanager.js?{{_env.new_id}}"],"filemanager-js");
    wbapp.loadStyles(["/engine/modules/filemanager/filemanager.css"]);
</script>
<wb-lang>
    [ru]
    refresh = Обновить
    upload = Загрузить
    actions = Действия
    edit = Редактировать
    rename = Переименовать
    remove = Удалить
    zip = Архивировать
    unzip = Разархивировать
    copy = Копировать
    cut = Вырезать
    paste = Вставить
    download = Скачать
    duplicate = Дублировать
    filemanager = Файловый менеджер
    cancel = Отмена
    ok = Выполнить
    saved = Сохранено
    create = Создать
    title_new_dir = Новая директория
    title_new_file = Новый файл
    title_rmdir = Удаление директории
    title_rmfile = Удаление файла
    title_rmlist = Множественное удаление
    title_rendir = Переименование директории
    title_renfile = Переименование файла
    title_rewrite = Переизапись
    title_zip = Архивация
    title_unzip = Распаковка архива
    prompt_new_dir = Создать новую директорию с именем
    prompt_new_file = Создать новый файл с именем
    prompt_rmdir = Удалить рекурсивно директорию
    prompt_rmfile = Удалить файл
    prompt_rmlist = Выполнить удаление
    prompt_rmlist1 = выбранных объектов
    prompt_rendir = Переименовать директорию
    prompt_renfile = Переименовать файл
    prompt_paste = Некоторые объекты уже существуют в этой директории.<br> Выполнить перезапись существующих объектов?
    prompt_zip = Сжать выбранные объекты в архив?
    prompt_unzip = Извлечь файлы и папки из архива?<br>Существующие объекты будут перезаписаны.
    prompt_to = в

    [en]
    refresh = Refresh
    upload = Upload
    actions = Actions
    edit = Edit
    rename = Rename
    remove = Remove
    zip = Zip
    unzip = UnZip
    copy = Copy
    cut = Cut
    paste = Insert
    download = Download
    duplicate = Duplicate
    filemanager = File Manager
    cancel = Cancel
    ok = Ok
    saved = Saved
    create = Create
    title_new_dir = New folder
    title_new_file = New file
    title_rmdir = Remove folder
    title_rmfile = Remove file
    title_rmlist = Remove objects
    title_rendir = Rename folder
    title_renfile = Rename file
    title_rewrite = Rewrite
    title_zip = Zip
    title_unzip = Unzip
    prompt_new_dir = "Create new folder with name"
    prompt_new_file = "Create new file with name"
    prompt_rmdir = Remove folder recursive
    prompt_rmfile = Remove file
    prompt_rmlist = Remove
    prompt_rmlist1 = selected objects
    prompt_rendir = Rename folder
    prompt_renfile = Rename file
    prompt_paste = Some objects already exists in this folder.<br> Rewrite exists objects?
    prompt_zip = Zip selected objects?
    prompt_unzip = Unzip objects from archive?<br>Exists objects will be rewrite.
    prompt_to = to
</wb-lang>

</html>