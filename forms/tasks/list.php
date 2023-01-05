<html>
<div class="app-tasks">
    <div id="tasks">
        <div class="chat-wrapper chat-wrapper-two">
            <div class="content-toasts pos-absolute t-10 r-10" style="z-index:5000;"></div>
            <div class="tasks-sidebar content-sidebar chat-sidebar">
                <div class="tasks-sidebar-body p-0">
                    <div class="scrollable" id="taskComments">
                        <wb-foreach wb="from=comments&bind=cms.list.taskComments&render=client">
                            <div class="border-bottom comment">
                                <div class="p-2">
                                    <button type="button" class="close tx-18">×</button>
                                    <div class="text tx-12 pr-2">{{comment}}</div>
                                    <div class="time tx-10 tx-secondary"><i class="ri-time-line"></i> {{time}}</div>
                                </div>
                            </div>
                        </wb-foreach>
                    </div>
                </div>
                <div class="tasks-sidebar-footer w-100" style="bottom:0;position:absolute;">
                    <form class="m-0 p-0">
                        <div class="input-group bg-light p-3 tx-12 col-auto">
                            <input type="text" name="comment" class="form-control bg-white input-sm" id="taskComment"
                                placeholder="{{_lang.type}}" disabled autocomplete="off">
                            <span class="input-group-append">
                                <button class="btn btn-success btn-sm btn-icon" type="button" id="taskCommentBtn"
                                    disabled>
                                    <i class="fa fa-pencil"></i></button>
                            </span>

                        </div>
                    </form>
                </div>
            </div>
            <!-- content-left -->
            <div class="chat-content bg-light text-dark">
                <div class="content-body-header">
                    <div class="d-flex">
                        <nav class="nav navbar col">
                            <span class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">{{_lang.tasks}}</span>
                            <button class="order-1 btn btn-success btn-sm pull-right btn-icon" id="newTask">
                                <i class="fa fa-plus"></i> {{_lang.new}}
                            </button>
                        </nav>
                    </div>
                </div>

                <div class="m-2" id="{{_form}}List">
                    <wb-foreach wb="table=tasks&bind=cms.tasks.list&sort=_created:d&render=server" wb-filter="{'_creator':'{{_sess.user.id}}'}">
                        <div class="card px-0" data-id="{{_id}}">
                            <form class="card-body row m-0 p-0">
                                <input class="col ml-1 mt-1" name="done" type="checkbox">
                                <div class="col-11">
                                    <input type="text" class="form-control" placeholder="{{_lang.new}}" name="task"
                                        autocomplete="off" value="{{task}}">
                                </div>
                                <button type="button" class="form-control close col mr-1 tx-18">×</button>
                            </form>
                        </div>
                    </wb-foreach>
                    <div class="card empty bg-info text-light d-none">
                        <div class="card-body">
                            {{_lang.empty}}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
<wb-lang>
    [ru]
    tasks = Задачи
    new = "Новая"
    type = "Напишите комментарий"
    empty = "Нет текущих задач"
    [en]
    tasks = Tasks
    new = "New"
    type = "Type a comment"
    empty = "No have tasks"

</wb-lang>
</div>
<script type="wbapp">
    wbapp.loadScripts(["/engine/forms/tasks/tasks.js?{{_env.new_id}}"],"tasks-js");
    wbapp.loadStyles(["/engine/forms/tasks/tasks.less?{{_env.new_id}}"]);
</script>


</html>