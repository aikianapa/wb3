<html>
<div class="app-notes">
    <div id="notes">
        <div class="chat-wrapper chat-wrapper-two">
            <div class="content-toasts pos-absolute t-10 r-10" style="z-index:5000;"></div>
            <div class="notes-sidebar content-sidebar chat-sidebar">
                <div class="notes-sidebar-body p-0">
                    <div class="scrollable" id="notesList">
                        <wb-foreach wb="table=notes&bind=cms.list.notes&sort=_created:d&tpl=true&render=server" wb-filter="{'_creator':'{{_sess.user.id}}'}">
                            <div class="card" data-id="{{_id}}">
                                <div class="card-body">
                                    <button type="button" class="close tx-18">×</button>
                                    <div class="text tx-12 pr-2">{{short}}</div>
                                    <div class="time tx-10 tx-secondary"><i class="ri-time-line"></i> {{time}}</div>
                                </div>
                            </div>
                            <wb-empty>
                                <div class="alert alert-info">
                                    {{_lang.empty}}
                                </div>
                            </wb-empty>
                        </wb-foreach>
                    </div>
                </div>
            </div>
            <!-- content-left -->
            <div class="chat-content text-dark">
                <div class="content-body-header">
                    <div class="d-flex">
                        <nav class="nav navbar navbar-light sticky-top col">
                            <button class="order-1 btn btn-success btn-sm pull-right btn-icon" id="newNote">
                                <i class="fa fa-plus"></i> {{_lang.new}}
                            </button>
                            <span class="mr-3"><i class="ri-sticky-note-line"></i> {{_lang.notes}}</span>
                        </nav>
                    </div>
                </div>


                <div class="paper" id="notesPaper">
                    <template data-params="render=client&bind=cms.notes.paper&target=#notesPaper">
                        <form>
                            <textarea type="text" name="note" class="form-control scrollable" placeholder="{{_lang.type}}" data-id="{{_id}}" wb-save="table=notes&item={{_id}}&silent=true&bind=cms.list.notes">{{note}}</textarea>
                        </form>
                    <template>
                </div>
                </div>
            </div>
        </div>

    </div>
    <wb-lang>
        [ru]
        notes = Заметки
        new = "Новая"
        empty = "Нет текущих зметок"
        type = "Пишите заметку здесь"
        [en]
        notes = Notes
        new = "New"
        empty = "No have notses"
        type = "Type your note here"

    </wb-lang>
</div>
<script type="wbapp">
    wbapp.loadScripts(["/engine/forms/notes/notes.js?{{_env.new_id}}"],"notes-js");
    wbapp.loadStyles(["/engine/forms/notes/notes.less?{{_env.new_id}}"]);
</script>


</html>