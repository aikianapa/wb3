<html>
<div class="chat-wrapper chat-wrapper-two" id="modFormBuilder">
    <div class="chat-sidebar">

        <div class="chat-sidebar-header d-flex align-items-center">
		<a data-toggle="tooltip" data-trigger="hover" title="Собеседники"
							onclick="$('.chat-sidebar-right').toggle();">
							<svg class="mi-legal-friction-talk-users size-20" wb-module="myicons"></svg>
							<span class="tx-medium mg-l-5"></span>
						</a>
						<a href="#channelLeave" data-toggle="modal"><span data-toggle="tooltip"
							data-trigger="hover" title="Выйти из канала">
							<svg class="mi-interface-essential-282 size-20" wb-module="myicons"></svg>
							</span></a>
        </div><!-- chat-sidebar-header -->

        <!-- start sidebar body -->
        <div class="chat-sidebar-body">


 <!-- required bootstrap js and fontawesome -->
 <!-- add 'accordion' snippet in css -->
 <div id="modFormbuilderFormType" class="accordion w-100">
   <div class="card mb-0">
	 <div class="card-header collapsed" data-toggle="collapse" href="#collapseOne">
	  <a class="cursor-pointer card-title">Заголовки</a>
	 </div>
	  <div id="collapseOne" class="card-body collapse" data-parent="#modFormbuilderFormType" >
		<p></p>
	  </div>
	  <div class="card-header collapsed" data-toggle="collapse" data-parent="#modFormbuilderFormType" href="#collapseTwo">
		<a class="cursor-pointer card-title">Подвалы</a>
	  </div>
	  <div id="collapseTwo" class="card-body collapse" data-parent="#modFormbuilderFormType" >
		<p></p>
	  </div>
	  <div class="card-header collapsed" data-toggle="collapse" data-parent="#modFormbuilderFormType" href="#collapseThree">
		<a class="cursor-pointer card-title">Формы</a>
	  </div>
	  <div id="collapseThree" class="collapse" data-parent="#modFormbuilderFormType" >
		<div class="card-body"></div>
	  </div>
   </div>
</div>

        </div>



    </div><!-- chat-sidebar -->

    <div class="chat-content">
        <div class="chat-content-body">
qerqwertqw
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
    </div><!-- chat-content -->
</div><!-- chat-wrapper -->
<script type="wbapp">
    wbapp.loadScripts(["/engine/modules/formbuilder/formbuilder.js?{{_env.new_id}}"],"formbuilder-js");
    wbapp.loadStyles(["/engine/modules/formbuilder/formbuilder.less?{{_env.new_id}}"]);
</script>


</html>