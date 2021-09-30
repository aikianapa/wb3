<html>
<link href="/modules/yonger/tpl/assets/css/dashforge.chat.css" rel="stylesheet" />
<script src="/modules/yonger/tpl/assets/js/dashforge.chat.js"></script>
<div class="chat-wrapper chat-wrapper-two">
    <div class="chat-sidebar">

        <div class="chat-sidebar-header">
            <a href="#" data-toggle="dropdown" class="dropdown-link">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm mg-r-8"><span class="avatar-initial rounded-circle">T</span></div>
                    <span class="tx-color-01 tx-semibold">TeamName</span>
                </div>
                <span><i data-feather="chevron-down"></i></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right tx-13">
                <a href="" class="dropdown-item"><i data-feather="user-plus"></i> Invite People</a>
                <a href="" class="dropdown-item"><i data-feather="plus-square"></i> Create Channel</a>
                <a href="" class="dropdown-item"><i data-feather="server"></i> Server Settings</a>
                <a href="" class="dropdown-item"><i data-feather="bell"></i> Notification Settings</a>
                <a href="" class="dropdown-item"><i data-feather="zap"></i> Privacy Settings</a>
                <div class="dropdown-divider"></div>
                <a href="" class="dropdown-item"><i data-feather="edit-3"></i> Edit Team Details</a>
                <a href="" class="dropdown-item"><i data-feather="shield-off"></i> Hide Muted Channels</a>
            </div><!-- dropdown-menu -->
        </div><!-- chat-sidebar-header -->

        <!-- start sidebar body -->
        <div class="chat-sidebar-body">

            <div class="flex-fill pd-y-20 pd-x-10">
                <div class="d-flex align-items-center justify-content-between pd-x-10 mg-b-10">
                    <span class="tx-10 tx-uppercase tx-medium tx-color-03 tx-sans tx-spacing-1">Каналы</span>
                    <a href="#modalCreateChannel" class="chat-btn-add" data-toggle="modal">
                        <span data-toggle="tooltip" title="Создать канал">
                            <svg class="mi mi-message-chat-medical-cross" wb-module="myicons"></svg>
                        </span>
                    </a>
                </div>
                <nav id="allChannels" class="nav flex-column nav-chat mg-b-20">
                    <wb-foreach wb="from=result&bind=mod.chat.rooms&render=client">
                        <a href data-id="{{this}}" class="nav-link">#{{this}}</a>
                    </wb-foreach>
                </nav>
            </div>

            <div class="flex-fill pd-y-20 pd-x-10 bd-t">
                <p class="tx-10 tx-uppercase tx-medium tx-color-03 tx-sans tx-spacing-1 pd-l-10 mg-b-10">Приватные
                    сообщения</p>
                <div id="chatDirectMsg" class="chat-msg-list">
                <wb-foreach wb="from=result&bind=mod.chat.privusers&render=client">
                <a href="#" data-id="{{id}}" class="media">
                        <div class="avatar avatar-sm avatar-online">
                          <img src="https://via.placeholder.com/350"
                                class="rounded-circle" alt=""></div>
                        <div class="media-body mg-l-10">
                            <h6 class="mg-b-0">{{name}}</h6>
                            <small class="d-block tx-color-04">1 hour ago</small>
                        </div><!-- media-body -->
                        <span class="badge badge-danger">3</span>
                    </a><!-- media -->
                </wb-foreach>
                </div><!-- media-list -->
            </div>
        </div><!-- chat-sidebar-body -->

        <div class="chat-sidebar-footer" id="modChatUser">
            <template>
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm avatar-online mg-r-8">

                        {{#if avatar.0.img}}
                        <img data-src="/thumbc/64x64/src/{{avatar.0.img}}" class="rounded-circle" alt="">
                        {{else}}
                        <img data-src="/engine/modules/cms/tpl/assets/img/user.svg" class="rounded-circle" alt="">
                        {{/if}}
                    </div>
                    <h6 class="tx-semibold mg-b-0">{{first_name}} {{last_name}}</h6>
                </div>
                <div class="d-flex align-items-center">
                    <a href=""><i data-feather="mic"></i></a>
                    <a href=""><i data-feather="settings"></i></a>
                </div>
            </template>
        </div><!-- chat-sidebar-footer -->

    </div><!-- chat-sidebar -->

    <div class="chat-content">
        <div class="chat-content-header">
            <h6 id="channelTitle" class="mg-b-0 cursor-pointer"></h6>
            <div id="directTitle" class="d-none">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm avatar-online"><span class="avatar-initial rounded-circle">b</span>
                    </div>
                    <h6 class="mg-l-10 mg-b-0">@dfbot</h6>
                </div>
            </div>
            <div class="d-flex">
                <nav id="channelNav">
                    <a id="showMemberList" href="" data-toggle="tooltip" data-trigger="hover" title="Собеседники"
                        class="d-flex align-items-center">
                        <svg class="mi mi-legal-friction-talk-users" wb-module="myicons"></svg>
                        <span class="tx-medium mg-l-5"></span>
                    </a>
                    <a href="#channelLeave" data-toggle="modal"><span data-toggle="tooltip"
                          data-trigger="hover" title="Выйти из канала">
                          <svg class="mi mi-interface-essential-282" wb-module="myicons"></svg>
                        </span></a>
                </nav>
                <nav id="directNav" class="d-none">
                    <a href="" data-toggle="tooltip" title="Call User">1<i data-feather="phone"></i></a>
                    <a href="" data-toggle="tooltip" title="User Details">2<i data-feather="info"></i></a>
                    <a href="" data-toggle="tooltip" title="Add to Favorites">4<i data-feather="star"></i></a>
                    <a href="" data-toggle="tooltip" title="Flag User">4<i data-feather="flag"></i></a>
                </nav>
                <nav class="mg-sm-l-10">
                    <a href="" data-toggle="tooltip" title="Channel Settings" data-placement="left"><i
                            data-feather="more-vertical"></i></a>
                </nav>
            </div>
        </div><!-- chat-content-header -->

        <div class="chat-content-body">
            <div class="chat-group" id="chatRoom">
                <wb-foreach wb="from=result&bind=mod.chat.null&render=client">
                    <!--div class="chat-group-divider">February 20, 2019</div-->
                    <div class="media">
                        <div class="avatar avatar-sm avatar-online"><span class="avatar-initial rounded-circle">k</span>
                        </div>
                        <div class="media-body">
                            <h6>{{name}} <small> {{time}} </small></h6>
                            <p>{{msg}}</p>
                        </div><!-- media-body -->
                    </div>
                </wb-foreach>
            </div>
        </div><!-- chat-content-body -->

        <div class="chat-sidebar-right">
            <div class="pd-y-20 pd-x-10">
                <div class="tx-10 tx-uppercase tx-medium tx-color-03 tx-sans tx-spacing-1 pd-l-10">Собеседники</div>
                <div class="chat-member-list" id="chatRoomUsers">
                    <wb-foreach wb="from=result&bind=mod.chat.roomusers&render=client">
                        <a href="#" data-id="{{id}}" class="media">
                            <div class="avatar avatar-sm avatar-online"><span
                                    class="avatar-initial rounded-circle">b</span></div>
                            <div class="media-body mg-l-10">
                                <h6 class="mg-b-0">{{name}}</h6>
                            </div><!-- media-body -->
                        </a>
                    </wb-foreach>
                </div>
            </div>
        </div><!-- chat-sidebar-right -->

        <form method="post" id="modChatMsg" class="chat-content-footer m-0">
            <input type="text" class="form-control align-self-center bd-0" placeholder="Сообщение">
            <div class="d-flex align-items-center pd-l-10 pd-r-10 bd-l">
                <a href="javascript:$('#modChatMsg').submit();" data-toggle="tooltip" data-trigger="hover"
                    title="Отправить сообщение">
                    <svg class="mi mi-send-message-send-share.2 wd-30" wb-module="myicons"></svg></a>
            </div>
            <nav>
                <a href="javascript:null;" data-toggle="tooltip" title="Прикрепить файл">
                    <svg class="mi mi-clip-attachment-square" wb-module="myicons"></svg></a>
                <a href="javascript:null;" data-toggle="tooltip" title="Прикрепить картинку">
                    <svg class="mi mi-image-picture-add" wb-module="myicons"></svg></a>
                <a href="javascript:null;" data-toggle="tooltip" title="Видеоконференция">
                    <svg class="mi mi-video-movies-09" wb-module="myicons"></svg></a>
            </nav>
        </form><!-- chat-content-footer -->
    </div><!-- chat-content -->
</div><!-- chat-wrapper -->
<script src='{{_env.modules.chat.path}}/chat.js'></script>

</html>