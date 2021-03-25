var modChat = function (project = 'test', room = null) {
    let current_room = null;
    let conn = null;
    let json = json_decode($('script#schema').html());
    let url = parse_url(json.server);

    if (document.modChat == undefined) {
        document.modChat = setInterval(function () {
            if ($(document).find('.chat-wrapper').length && !conn.OPEN) {
                conn = null;
                console.log('Reconnect');
                chat_connect();
            } else {
                clearInterval(document.modChat);
                delete document.modChat;
            }
        }, 4000);
    }
    

    var chat_connect = function () {
        if (conn && conn.OPEN) return conn;
        conn = new WebSocket('ws://'+url.host+':8000')
        if (conn.OPEN) {
            conn.onopen = function (e) {
                console.log("Connection established!")
                let msg = Object.assign({}, json)
                msg.command = 'getrooms'
                conn.send(json_encode(msg))
            };

            conn.onmessage = function (e) {
                let data = json_decode(e.data)
                var func = 'chat_' + data.command;

                if (data.server !== json.server && data.project !== json.project) return;

                try {
                    eval(func + '(data);');
                } catch (error) {
                    console.log(`Function ${func} not found`);
                }


            };

            conn.onclose = function (e) {
                if (conn.OPEN) {
                    wbapp.toast("Ошибка","Соединение с сервером разорвано! Подключаемся...",{'bgcolor':'danger','delay':2000});
                    console.log("Connection closed!");
                }
            }

            $(document).undelegate('#allChannels .nav-link', 'tap click');
            $(document).delegate('#allChannels .nav-link', 'tap click', function (e) {
                e.preventDefault()
                $(this).addClass('active');
                $(this).siblings().removeClass('active');

                $('#chatDirectMsg .active').removeClass('active');

                // replace channel title
                let href = $(this).attr('data-id').trim();
                $('#channelTitle').text('#' + href);

                // view channel title
                $('#channelTitle').removeClass('d-none');
                $('#directTitle').addClass('d-none');

                // view channel nav icon
                $('#channelNav').removeClass('d-none');
                $('#directNav').addClass('d-none');

                if (window.matchMedia('(max-width: 991px)').matches) {
                    showChatContent();
                }


                let msg = Object.assign({}, json); // copy json
                msg.room = msg.msg = href;
                msg.command = 'join';
                conn.send(json_encode(msg));
            });

            // direct message click
            $(document).undelegate('#chatDirectMsg .media', 'click');
            $(document).delegate('#chatDirectMsg .media','click', function (e) {
                e.preventDefault();

                $(this).addClass('active');
                $(this).siblings().removeClass('active');

                $('#allChannels .active').removeClass('active');

                var directUser = $(this).find('h6').text();
                $('#directTitle h6').text('@' + directUser);

                var avatar = $(this).find('.avatar');
                $('#directTitle .avatar').replaceWith(avatar.clone());

                // view direct title
                $('#channelTitle').addClass('d-none');
                $('#directTitle').removeClass('d-none');

                // view direct nav icon
                $('#channelNav').addClass('d-none');
                $('#directNav').removeClass('d-none');

                if (window.matchMedia('(max-width: 991px)').matches) {
                    showChatContent();
                }

                $('body').removeClass('show-sidebar-right');
                $('#showMemberList').removeClass('active');

                let uid = $(this).attr('data-id');
                let msg = Object.assign({}, json); // copy json
                msg.command = 'direct';
                msg.msg = '';
                msg.room = uid;
                msg.receiver = uid;
                conn.send(json_encode(msg));

            })

            $(document).undelegate('#chatRoomUsers .media', 'click');
            $(document).delegate('#chatRoomUsers .media', 'click',function(e){
                let receiver = $(this).attr('data-id');
                let msg = Object.assign({}, json); // copy json
                msg.room = msg.msg = '@' + receiver;
                msg.receiver = receiver;
                msg.command = 'join';
                conn.send(json_encode(msg));
            });
            

            $(document).undelegate('#channelTitle', 'tap click');
            $(document).delegate('#channelTitle', 'tap click', function () {
                $('body').toggleClass("chat-content-show chat-content-hide");
            });

            $(document).undelegate('#modChatMsg', 'submit');
            $(document).delegate('#modChatMsg', 'submit', function (e) {
                let msg = Object.assign({}, json); // copy json
                msg.msg = $(this).find('input').val();
                msg.command = 'message';
                if (msg.msg.trim() > ' ') conn.send(json_encode(msg));
                $(this)[0].reset();
                e.preventDefault();
            });
        }
        return conn
    }

    //wbapp.storage('mod.chat', {});
    if ($(document).data('moddChat')) conn = $(document).data('modChat');
    if (!conn || !conn.OPEN) conn = chat_connect()


    var chat_setrooms = function (data) {
        wbapp.storage('mod.chat.rooms', data.msg);
        wbapp.render('#allChannels');
    }

    var chat_message = function (data) {
        let hMsg = 'id' + md5(data.server + data.project + data.room + data.sender + data.receiver);
        let hRoom = 'id' + md5(data.server + data.project + data.room);
        let hUser = 'id' + md5(data.server + data.project + data.sender);
        let mid = 'msg_' + data._created + '_' + hMsg;
        let bind = 'mod.chat.room.' + hRoom;
        if (wbapp.storage(bind) !== undefined) {
            wbapp.storage(bind + '.' + data.id, {
                'msg': data.msg,
                'name': data.sender.name,
                'time': date('d.m.Y H:i:s', strtotime(data._created))
            });
            chat_to_bottom();
        }
    }

    var chat_channelusers = function(data) {
        if (data.receiver > '' ) {
            wbapp.storage('mod.chat.privusers.' + data.receiver, data.msg[data.receiver]);
            wbapp.render("#chatDirectMsg");
            $('body').removeClass('show-sidebar-right');
            $(`#chatDirectMsg [data-id='${data.receiver}']`).trigger('tap click');
        } else {
            wbapp.storage('mod.chat.roomusers', data.msg);
            wbapp.render("#chatRoomUsers");
        }
        $('#showMemberList span').text(count(data.msg));
    }

    var chat_join = function (data) {
        current_room = data.msg;
        console.log(data);
        json.room = data.room;
        let bind = 'mod.chat.room.' + current_room
        if (wbapp.storage(bind) == undefined) wbapp.storage(bind, {});
        wbapp.template["#chatRoom"].params.bind = bind;
        wbapp.render("#chatRoom");
        chat_to_bottom(true);
    }

    var chat_to_bottom = function(init = false) {
        if (init == true) {
            $("#chatRoom").css('min-height','3000px');
            $(".chat-content-body").scrollTop(0) 
        }
        setTimeout(function () {
            $(".chat-content-body").animate({ scrollTop: $('.chat-content-body').prop("scrollHeight") }, 500);
        }, 10)
    }


    setTimeout(function () {
        wbapp.render("#modChatUser", wbapp._session.user);
        wbapp.render('#allChannels');
    }, 100)


}

if (document.modChat == undefined) modChat('yonger', 'yonger_test');
