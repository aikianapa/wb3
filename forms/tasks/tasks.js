$(document).ready(function () {

    $('#tasks').delegate('form','submit',function(){
        if ($(this).find('#taskCommentBtn').length) {
            $(this).find('#taskCommentBtn').trigger('click');
        }
        return false;
    });

    $('#taskComments').delegate('.close', 'tap click', function () {
        var id = $('#tasks .card.active').attr('data-id');
        let idx = $(this).closest('.comment').index();
        $.post("/api/call/tasks/removeComments/", { 'id': id, 'idx':idx }, function (res) {
            console.log(res);
        })
        $(this).closest('.comment').remove();
    });

$('#tasks').delegate('.card', 'tap click', function () {
    $('#tasks .card').removeClass('active');
    $('#tasks').find('#taskComment, #taskCommentBtn').prop('disabled',false);
    $(this).addClass('active');
    var id = $('#tasks .card.active').attr('data-id');
    $.post("/api/call/tasks/listComments/",{'id':id},function(res){
        taskListComments(res)
    })
});

$('#tasks').delegate('#taskCommentBtn', 'tap click', function () {
    var id = $('#tasks .card.active').attr('data-id');
    var data = wbapp.objByForm($(this).closest('form'));
    if (data.comment == '') return;
    data.id = id;
    $(this).closest('form')[0].reset();
    let res = wbapp.postSync("/api/call/tasks/addComment/", data );
    if (res.comment !== undefined) {
        taskPushComments(res)
    }
})

$('#tasks').delegate('#newTask','tap click',function(){
    let tpl = wbapp.tpl('#tasksList');
    let nid = wbapp.newId();
    tpl = str_replace('{{_id}}', nid, $(tpl.html).html());
    $('#tasks').find('.card.new').remove();
    $('#tasks #tasksList').prepend(tpl);
    $('#tasks').find('.card:first-child').trigger('click')
    $('#tasks').find('.card:first-child').addClass('new');
    $('#tasks').find('.card:first-child input[name=task]').focus();
    $('#tasks').find('.card.empty').addClass('d-none');
})

$('#tasks').delegate('input', 'change', function () {
    let id = $(this).closest('.card').attr('data-id');
    if (id == undefined) return;
    $(this).closest('.card').removeClass('new');
    wbapp.save($(this), {"silent":true,"url": "\/ajax\/save\/tasks\/"+id });
    return false;
})

$('#tasks').delegate('button.close', 'tap click', function () {
    var card = $(this).closest('.card');
    let id = $(card).attr('data-id');
    if (id == undefined) return;
    if ($(card).hasClass('new')) {
        $(card).remove();
        $('#tasks').find('#taskComment, #taskCommentBtn').prop('disabled', true);
        return;
    }
    var self = this;
    wbapp.ajax({"url": "\/ajax\/rmitem\/tasks\/" + id +'?_confirm' },function(res){
        if (res.data._removed !== undefined && res.data._removed == true) {
            $(card).remove();
            $('#tasks').find('#taskComment, #taskCommentBtn').prop('disabled', true);
            if ($('#tasks .card:not(.empty)').length == 0) {
                $('#tasks .card.empty').removeClass('d-none');
            }
        }
    });
    return false;
})


$('#tasks').delegate('input', 'keyup', function () {
    var self = this;
    if (this.timer !== undefined) {
        clearTimeout(this.timer);
    }
    this.timer = setTimeout(function(){
        $(self).trigger('change')
    },1000)
});

if ($('#tasks .card:not(.empty)').length == 0) {
    $('#tasks #newTask').trigger('click');
}

    var taskListComments = function(data) {
        storage = { 'comments': [] };
        storage.comments = data;
        wbapp.storage('cms.list.taskComments', storage);
    }

    var taskPushComments = function (data) {
        let storage = wbapp.storage('cms.list.taskComments');
        if (storage == undefined) storage = { 'comments': [] };
        storage.comments.push(data);
        wbapp.storage('cms.list.taskComments', storage);
    }


})