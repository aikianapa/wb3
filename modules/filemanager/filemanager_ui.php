<html>
<wb-var iconsize="tx-24" />
<div class="app-filemgr">
    <div id="filemanager">
        <div class="chat-wrapper chat-wrapper-two">
            <div class="content-toasts pos-absolute t-10 r-10" style="z-index:5000;"></div>
            <div class="filemgr-sidebar content-sidebar chat-sidebar scroll-y">
                <div class="filemgr-sidebar-header">
                    <div id="filemanagerUploader" class="flex-fill mg-l-10">
                        <div class="uploader">
                            <wb-module id="pickfiles" wb="module=filepicker&mode=button&original=true&ext=*" class="btn btn-block btn-primary" wb-path="/">
                                {{_lang.upload}}
                                <svg class="mi-upload-loading-arrow size-24" stroke="FFFFFF" wb-module="myicons"></svg>
                            </wb-module>
                        </div>
                    </div>
                </div>
                <div class="filemgr-sidebar-body">
                    <div class="pd-10">
                        <ul class="nav nav-sidebar tx-medium tx-14 lh-14">
                            <li class="nav-item">
                                <a href="#refresh" class="nav-link">
                                    <svg class="mi mi-rotate-refresh-loading size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.refresh}}</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="#newdir" class="nav-link">
                                    <svg class="mi mi-folder-medical-cross size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.title_new_dir}}</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="#newfile" class="nav-link">
                                    <svg class="mi mi-documents-file.4 size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.title_new_file}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-single allow-file allow-file1" data-no-ext="zip tar arj rar gzip jpg jpeg png gif tif tiff">
                                <a href="#edit" class="nav-link">
                                    <svg class="mi mi-content-edit-pen size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.edit}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-single allow-dir allow-file allow-dir1 allow-file1">
                                <a href="#rename" class="nav-link">
                                    <svg class="mi mi-input-text size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.rename}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-all">
                                <a href="#remove" class="nav-link">
                                    <svg class="mi mi-trash-delete-bin.3 size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.remove}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-all" data-no-ext="zip">
                                <a href="#zip" class="nav-link">
                                    <svg class="mi mi-file-zip-rar-circle size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.zip}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-single allow-file" data-ext="zip">
                                <a href="#unzip" class="nav-link">
                                    <svg class="mi mi-file-zip-rar-1 size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.unzip}}</span>
                                </a>
                            </li>


                            <li class="nav-item hidden allow-all">
                                <a href="#copy" class="nav-link">
                                    <svg class="mi mi-copy-paste-select-add-plus size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.copy}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-all">
                                <a href="#cut" class="nav-link">
                                    <svg class="mi mi-scissors-cut.3 size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.cut}}</span>
                                </a>
                            </li>

                            <li class="nav-item hidden allow-buffer">
                                <a href="#paste" class="nav-link">
                                    <svg class="mi mi-copy-paste-select-add-plus.2 size-24" wb-module="myicons"></svg>
                                    <span>{{_lang.paste}}</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <!-- content-left -->
            <div class="chat-content p-2" id="panel">
                <div class="content-body-header">
                    <div class="d-flex">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb py-1 px-2">
                                <li class="breadcrumb-item" prepend="nav .breadcrumb">
                                    <a href="#" data-path="">
                                        <svg class="mi mi-home-house-big size-24" stroke="0168fa" wb-module="myicons"></svg>
                                    </a>
                                </li>
                                <wb-var path='' />
                                <wb-foreach wb-from="path">
                                    <wb-var path='{{_var.path}}/{{_parent.path[{{_ndx}}]}}' />
                                    <li class="breadcrumb-item">
                                        <a href="#" data-path="{{_var.path}}">{{_parent.path[{{_ndx}}]}}</a>
                                    </li>
                                </wb-foreach>
                            </ol>
                            <wb-jq wb="$dom->find('.breadcrumb .breadcrumb-item:last-child')->addClass('active')" />
                        </nav>
                    </div>
                </div>

                <table id="list" class="table table-striped table-hover tx-14 tx-gray-700">
                    <thead>
                        <tr>
                            <td>&nbsp;</td>
                            <td class="tx-normal">Название</td>
                            <td class="tx-normal">Изменён</td>
                            <td class="tx-normal">Права</td>
                            <td class="tx-normal">Размер</td>
                            <td>&nbsp;</td>
                        </tr>
                    </thead>
                    <tbody id="modFilemanagerList">
                        <wb-foreach wb="from=result&size=20">
                            <wb-var wr="disabled" wb-where='"{{wr}}"!=="1"' else="" />
                            <tr class="col-12 {{type}}{{link}} {{ext}}" data-name="{{name}}" data-ext="{{ext}}">
                                <td class="valign-middle">
                                    <label class="ckbox mg-b-0" wb-if="'{{type}}'!=='back'">
                                        <input type="checkbox" class="wd-20-f ht-20-f" wb-if='"{{wr}}"!="1"' disabled>
                                        <input type="checkbox" class="wd-20-f ht-20-f" wb-if='"{{wr}}"=="1"'>
                                        <span></span>
                                    </label>
                                </td>
                                <td class="col name ellipsis">
                                    <i class="fa {{type}} {{ext}} tx-24 tx-primary lh-0 valign-middle" wb-if="'{{type}}'!=='back'"></i>
                                    <svg class="mi mi-folder-group-arrow mr-2 size-24" wb-module="myicons" wb-if="'{{type}}'=='back'"></svg>
                                    <span class="pd-l-5">{{name}}</span>
                                </td>
                                <td class="hidden-xs">
                                    <nobr>{{modif}}</nobr>
                                </td>
                                <td class="hidden-xs">
                                    <nobr>{{perms}}</nobr>
                                </td>
                                <td class="hidden-xs tx-right">
                                    <nobr>{{size}}</nobr>
                                </td>
                                <!--td class="hidden-xs-down">{{ext}}</td-->
                                <td class="actions tx-right">
                                    <div class="d-block wd-150" wb-if='"{{type}}"!=="back"'>
                                        <a href="#edit" class="nobr nav-link" wb-if='"{{type}}"=="file"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.edit}}">
                                            <svg class="mi mi-content-edit-pen mr-2 size-24" wb-module="myicons"></svg>
                                        </a>
                                        <a href="#rendir" class="nobr nav-link" wb-if='"{{type}}"=="dir"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.rename}}">
                                            <svg class="mi mi-input-text size-24" wb-module="myicons"></svg>
                                        </a>
                                        <a href="#renfile" class="nobr nav-link" wb-if='"{{type}}"=="file"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.rename}}">
                                            <svg class="mi mi-input-text size-24" wb-module="myicons"></svg>
                                        </a>
                                        <a href="#renlink" class="nobr nav-link" wb-if='"{{type}}"=="dir1" OR "{{type}}"=="file1"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.rename}}">
                                            <svg class="mi mi-input-text size-24" wb-module="myicons"></svg>
                                        </a>
                                        <a href="{{href}}" download="{{name}}" class="nobr nav-link" wb-if='"{{type}}"=="file"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.download}}">
                                            <svg class="mi mi-download-arrow size-24" wb-module="myicons"></svg>
                                        </a>
                                        <a href="#rmfile" class="nobr nav-link" wb-if='"{{type}}"=="file"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.remove}}">
                                            <svg class="mi mi-trash-delete-bin.3 size-24" wb-module="myicons"></svg>
                                        </a>
                                        <a href="#rmdir" class="nobr nav-link" wb-if='"{{type}}"=="dir"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.remove}}">
                                            <svg class="mi mi-trash-delete-bin.3 size-24" wb-module="myicons"></svg>
                                        </a>
                                        <a href="#rmlink" class="nobr nav-link" wb-if='"{{type}}"=="dir1" OR "{{type}}"=="file1"' data-toggle="tooltip" data-placement="left" data-trigger="hover" title="{{_lang.remove}}">
                                            <svg class="mi mi-trash-delete-bin.3 size-24" wb-module="myicons"></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </wb-foreach>
                    </tbody>
                </table>
            </div>
        </div>


        <div id="filemanagerModalDialog" class="modal fade hidden" tabindex="-1" role="dialog" aria-hidden="true">
            <template name="newdir">
                <title>{{_lang.title_new_dir}}</title>
                <p>{{_lang.prompt_new_dir}}:<br> {{newname}}</p>
                <input type="text" class="form-control" name="newname">
            </template>
            <template name="rmdir">
                <title>{{_lang.title_rmdir}}</title>
                <p>{{_lang.prompt_rmdir}} <b>{{_post.name}}</b>? {{dirname}}</p>
                <input type="hidden" class="form-control" name="dirname" value="{{_post.name}}">
            </template>

            <template name="newfile">
                <title>{{_lang.title_new_file}}</title>
                <p>{{_lang.prompt_new_file}}:<br> {{newname}}</p>
                <input type="text" class="form-control" name="newname">
            </template>

            <template name="rmfile">
                <title>{{_lang.title_rmfile}}</title>
                <p>{{_lang.prompt_rmfile}} <b>{{_post.name}}</b>? {{filename}}</p>
                <input type="hidden" class="form-control" name="filename" value="{{_post.name}}">
            </template>

            <template name="remove">
                <title>{{_lang.title_rmlist}}</title>
                <p><span class='text-danger'>{{_lang.prompt_rmlist}}</span> {{_lang.prompt_rmlist1}}? {{filename}}</p>
            </template>

            <template name="rendir">
                <title>{{_lang.title_rendir}}</title>
                <p>{{_lang.prompt_rendir}} <b>{{_post.name}}</b> {{_lang.prompt_to}}: {{dirname}} {{oldname}}</p>
                <input type="text" class="form-control" name="dirname" value="{{_post.name}}">
                <input type="hidden" class="form-control" name="oldname" value="{{_post.name}}">
            </template>

            <template name="renfile">
                <title>{{_lang.title_renfile}}</title>
                <p>{{_lang.prompt_renfile}} <b>{{_post.name}}</b> {{_lang.prompt_to}}: {{filename}} {{oldname}}</p>
                <input type="text" class="form-control" name="filename" value="{{_post.name}}">
                <input type="hidden" class="form-control" name="oldname" value="{{_post.name}}">
            </template>

            <template name="paste">
                <title>{{_lang.title_rewrite}}</title>
                <p>{{_lang.prompt_paste}}</p>
            </template>

            <template name="zip">
                <title>{{_lang.title_renfile}}</title>
                <p>{{_lang.prompt_zip}} {{filename}}</p>
                <input type="text" class="form-control" name="filename" value="{{_post.name}}">
            </template>

            <template name="unzip">
                <title>{{_lang.title_unzip}}</title>
                <p>{{_lang.prompt_unzip}}</p>
            </template>

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

        <div id="filemanagerModalSrc" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header p-1">
                        <p class="modal-title text-secondary">
                            <button type="button" class="btn btn-primary btn-xs btn-edit-save">
                                {{_lang.save}}
                                <i class="fa fa-save"></i>
                            </button>
                            <span></span>
                        </p>
                        <button type="button" class="btn btn-sm btn-edit-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-close"></i></button>
                    </div>

                    <div class="nav-active-primary">
                        <ul class="nav" role="tablist" id="filemanagerTabs">
                            <li class="nav-item">
                                <a class="nav-link" href="" data-toggle="tab" aria-expanded="false">
                                    <i class="ml-2 fa fa-close"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div id="filemanagerSrc" class="tab-content  p-a m-b-md">
                        <div class="tab-pane show active" role="tabpanel">
                            <wb-module wb="{'module':'codemirror'}" id="filemanagerEditor" name="text" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        save = Сохранить
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
        save = Save
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
</div>
<script type="wbapp">
    wbapp.loadScripts(["/engine/modules/filemanager/filemanager.js?{{_env.new_id}}"],"filemanager-js");
    wbapp.loadStyles(["/engine/modules/filemanager/filemanager.less"]);
</script>


</html>