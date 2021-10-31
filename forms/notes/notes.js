$(document).ready(function() {

    var currentNote = wbapp.newId();
    $('#notes').delegate('form', 'submit', function(ev) {
        if ($(this).find('#noteCommentBtn').length) {
            $(this).find('#noteCommentBtn').trigger('click');
        }
        return false;
    });

    $('#noteComments').delegate('.close', wbapp.evClick, function() {
        var id = $('#notes .card.active').attr('data-id');
        let idx = $(this).closest('.comment').index();
        wbapp.ajax("/api/call/notes/removeComments/", { 'id': id, 'idx': idx }, function(res) {
            console.log(res);
        })
        $(this).closest('.comment').remove();
    });

    $('#notes').delegate('.card', wbapp.evClick, function() {
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
            item = wbapp.storage(bind + '.' + note)
        } else {
            item = wbapp.getSync("/api/query/notes/?_id=" + note);
            item = item[note];
        }
        wbapp.render('#notesPaper', item);
    });

    $('#notes').delegate('#newNote', wbapp.evClick, function() {
        let tpl = wbapp.tpl('#notesPaper');
        let nid = wbapp.newId();
        currentNote = nid;
        tpl = str_replace('{{_id}}', nid, tpl.html);
        tpl = str_replace('{{note}}', '', tpl);
        $('#notesPaper').html(tpl);
    })

    $('#notes').delegate('#notesPaper textarea', 'change', function() {
        let id = $(this).attr('data-id');
        if (id == undefined || id == '') {
            id = wbapp.newId();
            $(this).attr('data-id', id);
        }
        if (this.dirty !== undefined && this.dirty == false) return;
        //if ($(this).val() == '' && wbapp.storage("cms.list.notes." + id) == undefined) return;
        currentNote = id;
        try {
            params = wbapp.parseAttr($(this).attr("wb-save"));
        } catch (error) { return }
        params.item > '' ? null : params.item = id;
        wbapp.save($(this), params, function(data) {
            // wbapp.render('#notesList');
        });
        this.dirty = false;
        return false;
    })

    $('#notes').delegate('#notesList', 'wb-ajax-done', function() {
        // server mode
        setTimeout(function() {
            $('#notesList .card[data-id="' + currentNote + '"]').addClass('active');
        })
    })

    $('#notes').delegate('#notesPaper textarea', 'wb-save-done', function(ev, a, b) {
        // client mode
        setTimeout(function() {
            $('#notesList .card[data-id="' + currentNote + '"]').addClass('active');
        })
    });

    $('#notes').delegate('button.close', wbapp.evClick, function() {
        var card = $(this).closest('.card');
        let id = $(card).attr('data-id');
        if (id == undefined) return;
        if ($(card).hasClass('new')) {
            $(card).remove();
            $('#notes').find('#noteComment, #noteCommentBtn').prop('disabled', true);
            return;
        }
        var self = this;
        wbapp.ajax({ "url": "\/ajax\/rmitem\/notes\/" + id + '?_confirm' }, function(res) {
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


    $('#notes').delegate('#notesPaper textarea', 'keyup', function() {
        var self = this;
        this.dirty = true;
        if (this.timer !== undefined) {
            clearTimeout(this.timer);
        }
        this.timer = setTimeout(function() {
            $(self).trigger('change')
        }, 500)
    });

    if ($('#notes .card:not(.empty)').length == 0) {
        $('#notes #newNote').trigger('click');
    }


})