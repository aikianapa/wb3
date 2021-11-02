<html lang="en">
<wb-var wb-if="'{{_sett.modules.yonger.allow}}' == '' " role="user" else="{{_sett.modules.yonger.allow}}" />

<meta http-equiv="refresh" content="2; url=/signin/" wb-disallow="{{_var.role}}">
<wb-include wb="{'src':'head.inc.php'}" />
<body class="app-chat">
    <div class="app-chat" wb-if="'{{_sess.user.role}}' !== {{_var.role}}">
        <div class="container">
            <div class="alert alert-outline alert-danger d-flex align-items-center t-100" role="alert">
                <i class="fa fa-info-circle"></i> &nbsp; Ошибка входа в систему!
            </div>
        </div>
    </div>
    <div wb-allow="{{_var.role}}">
        <aside class="aside aside-fixed">
            <div class="aside-header">
                <wb-include wb="{'src':'aside_header.inc.php'}" />
            </div>
            <div class="aside-body ps">
                <wb-include wb="{'src':'aside_body.inc.php'}" />
            </div>
        </aside>

        <div class="content ht-100v pd-0">
            <div class="content-header">
                <wb-include wb="{'src':'ws_menu.inc.php'}" />
            </div>
            <!-- content-header -->
            <div class="content-toasts pos-absolute t-10 r-10" style="z-index:5000;"></div>
            <div class="content-body scroll-y pd-0 pl-2">
            </div>
        </div>
    </div>
    <wb-include wb="{'src':'foot.inc.php'}" />
</body>

</html>