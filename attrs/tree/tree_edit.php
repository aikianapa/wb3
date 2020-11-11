<div class="modal right tree-edit" id="tree_{{_env.new_id}}" data-keyboard="false" data-backdrop="true" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <ul class="nav" data-wb-allow="admin">
                    <li class="nav-item"><a class="nav-link active data" href="#treeData_{{_env.new_id}}" data-toggle="tab">{{_lang.data}}</a></li>
                    <li class="nav-item"><a class="nav-link dict" href="#treeDict_{{_env.new_id}}" data-toggle="tab">{{_lang.dict}}</a></li>
                </ul>
                <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal" aria-label="Close"><i class="fa fa-close"></i> Закрыть </button>
            </div>

            <div class="modal-body">

                <form class="form-horizontal" role="form">
                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">{{_lang.id}}</label>
                        <div class="col-sm-9"><input type="text" wb-module="smartid" class="form-control" name="id" placeholder="{{_lang.id}}" required></div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">{{_lang.name}}</label>
                        <div class="col-sm-9"><input type="text" class="form-control" name="name" placeholder="{{_lang.name}}"></div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">{{_lang.active}}</label>
                        <div class="col-sm-9">
                            <label class="switch"><input wb-module="switch" name="active"><span></span></label>
                        </div>
                    </div>
                </form>

                <div class="tab-content  p-a m-b-md">
                    <div id="treeData_{{_env.new_id}}" class="treeData tab-pane show active" role="tabpanel" data-wb="role=formdata&from=data">
                        <form class="form-horizontal" role="form">

                        </form>
                    </div>

                    <div id="treeDict_{{_env.new_id}}" class="treeDict tab-pane" role="tabpanel">
                        <form class="form-horizontal" role="form">

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<wb-lang>
[en]
        id    = "ID"
        name    = "Name"
        active  = "Active"
        data    = "Data"
        dict    = "Dict"
[ru]
        id    = "Идентификатор"
        name    = "Наименование"
        active  = "Активен"
        data    = "Данные"
        dict    = "Словарь"
</wb-lang>
</div>
