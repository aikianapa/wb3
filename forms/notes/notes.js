$(document).ready(function () {

    $('#notes').delegate('form', 'submit', function () {
        if ($(this).find('#noteCommentBtn').length) {
            $(this).find('#noteCommentBtn').trigger('click');
        }
        return false;
    });

    $('#noteComments').delegate('.close', 'tap click', function () {
        var id = $('#notes .card.active').attr('data-id');
        let idx = $(this).closest('.comment').index();
        $.post("/api/call/notes/removeComments/", { 'id': id, 'idx': idx }, function (res) {
            console.log(res);
        })
        $(this).closest('.comment').remove();
    });

    $('#notes').delegate('.card', 'tap click', function () {
        $('#notes .card').removeClass('active');
        $(this).addClass('active');
        let tid = '#' + $(this).parent().attr('id');
        let note = $(this).attr('data-id');
        let item;

        let params = wbapp.template[tid].params;
        let bind = params.bind;
        if (params.from !== undefined && params.from > '') {
            bind += '.' + params.from;
        }

        if (params.render == 'client') {
            item = wbapp.storage(bind + '.result.' + note)
        } else {
            item = wbapp.getSync("/api/query/notes/?_id=" + note);
            item = item[note];
        }
        wbapp.renderTemplate(wbapp.template['#notesPaper'].params, item);
    });

    $('#notes').delegate('#newNote', 'tap click', function () {
        let tpl = wbapp.tpl('#notesPaper');
        let nid = wbapp.newId();
        tpl = str_replace('{{_id}}', nid, tpl.html);
        tpl = str_replace('{{note}}', '', tpl);
        $('#notesPaper').html(tpl);
    })

    $('#notes').delegate('#notesPaper textarea', 'change', function () {
        let id = $(this).attr('data-id');
        if (this.dirty !== undefined && this.dirty == false) return;
        if (id == undefined) return;
        try {
            params = wbapp.parseAttr($(this).attr("wb-save"));
        } catch (error) { return }
        wbapp.save($(this), params);
        this.dirty = false;
        return false;
    })

    $('#notes').delegate('button.close', 'tap click', function () {
        var card = $(this).closest('.card');
        let id = $(card).attr('data-id');
        if (id == undefined) return;
        if ($(card).hasClass('new')) {
            $(card).remove();
            $('#notes').find('#noteComment, #noteCommentBtn').prop('disabled', true);
            return;
        }
        var self = this;
        wbapp.ajax({ "url": "\/ajax\/rmitem\/notes\/" + id + '?_confirm' }, function (res) {
            if (res.data._removed !== undefined && res.data._removed == true) {
                $(card).remove();
                $('#notes').find('#noteComment, #noteCommentBtn').prop('disabled', true);
                if ($('#notes .card:not(.empty)').length == 0) {
                    $('#notes .card.empty').removeClass('d-none');
                }
            }
        });
        return false;
    })


    $('#notes').delegate('#notesPaper textarea', 'keyup', function () {
        var self = this;
        this.dirty = true;
        if (this.timer !== undefined) {
            clearTimeout(this.timer);
        }
        this.timer = setTimeout(function () {
            $(self).trigger('change')
        }, 1000)
    });

    if ($('#notes .card:not(.empty)').length == 0) {
        $('#notes #newNote').trigger('click');
    }


})