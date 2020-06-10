<html>
<wb-include wb="{'src':'/engine/modules/cms/tpl/wrapper.inc.php'}"/>
<meta http-equiv="refresh" content="5; url=/cms/login/" wb-disallow="admin">

<div prepend="body">
  <div class="app-chat" wb-disallow="admin">
    <div class="container">
      <div class="alert alert-outline alert-danger d-flex align-items-center t-100" role="alert">
        <i class="fa fa-info-circle"></i> &nbsp; Ошибка входа в систему!
      </div>
    </div>
  </div>

  <div wb-allow="admin">

    <aside class="aside aside-fixed">
      <div class="aside-header">
        <a href="#" class="aside-logo">
					<img src="/engine/modules/cms/tpl/assets/img/virus.svg" width="30">
					Pan<span>demic</span>
        </a>
        <a href="" class="aside-menu-link">
          <i data-feather="menu"></i>
          <i data-feather="x"></i>
        </a>
        <a href="" id="chatContentClose" class="burger-menu d-none"><i data-feather="arrow-left"></i></a>
      </div>
      <div class="aside-body">
        <div class="aside-loggedin">
          <div class="d-flex align-items-center justify-content-start">
            <a href="" class="avatar"><img src="https://via.placeholder.com/500" class="rounded-circle" alt=""></a>
            <div class="aside-alert-link">
              <a href="" class="new" data-toggle="tooltip" title="You have 2 unread messages"><i data-feather="message-square"></i></a>
              <a href="" class="new" data-toggle="tooltip" title="You have 4 new notifications"><i data-feather="bell"></i></a>
              <a href="" data-ajax="{'url':'/cms/ajax/logout'}" data-toggle="tooltip" title="Sign out"><i data-feather="log-out"></i></a>
            </div>
          </div>
          <div class="aside-loggedin-user">
            <a href="#loggedinMenu" class="d-flex align-items-center justify-content-between mg-b-2"
              data-toggle="collapse">
              <h6 class="tx-semibold mg-b-0">{{_sess.user.first_name}} {{_sess.user.last_name}}</h6>
              <i data-feather="chevron-down"></i>
            </a>
            <p class="tx-color-03 tx-12 mg-b-0">{{_sess.user.role}}</p>
          </div>
          <div class="collapse" id="loggedinMenu">
            <ul class="nav nav-aside mg-b-0">
              <li class="nav-item">
                <a href="" class="nav-link"><i data-feather="edit"></i>
                  <span>Edit Profile</span>
                </a>
              </li>
              <li class="nav-item">
                <a href="" class="nav-link"><i data-feather="user"></i>
                  <span>View Profile</span>
                </a>
              </li>
              <li class="nav-item">
                <a href="" class="nav-link"><i data-feather="settings"></i>
                  <span>Account Settings</span>
                </a>
              </li>
              <li class="nav-item">
                <a href="" class="nav-link"><i data-feather="help-circle"></i>
                  <span>Help Center</span>
                </a>
              </li>
              <li class="nav-item">
                <a href="" class="nav-link"><i data-feather="log-out"></i>
                  <span>Sign Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
        <!-- aside-loggedin -->
        <ul class="nav nav-aside">

          <li class="nav-label mg-t-25">Навигация</li>
          <li class="nav-item with-sub">
            <a href="" class="nav-link"><i data-feather="user"></i>
              <span>Формы</span>
            </a>
            <ul>
              <li>
                <a href="#" data-ajax="{'url':'/cms/ajax/form/pages/list','html':'.content-body'}">Страницы</a>
              </li>
              <li>
                <a href="#" data-ajax="{'url':'/cms/ajax/form/users/list','html':'.content-body'}">Пользователи</a>
              </li>
            </ul>
          </li>

          <li class="nav-label mg-t-25">Настройки</li>
          <li class="nav-item">
            <a href="#" data-ajax="{'url':'/cms/settings/settings_ui','html':'.content-body'}" class="nav-link"><i data-feather="layers"></i>
              <span>Сайт</span>
            </a>
          </li>

        </ul>
      </div>
    </aside>

    <div class="content ht-100v pd-0">
      <div class="content-header">
        <div class="content-search">
          <i data-feather="search"></i>
          <input type="search" class="form-control" placeholder="Search">
        </div>
        <nav class="nav">
          <a href="" class="nav-link"><i data-feather="help-circle"></i></a>
          <a href="" class="nav-link"><i data-feather="grid"></i></a>
          <a href="" class="nav-link"><i data-feather="align-left"></i></a>
        </nav>
      </div>
      <!-- content-header -->

      <div class="content-body pd-0">

      </div>
    </div>
    <!-- content -->

    <div class="modal fade effect-scale" id="modalCreateChannel" tabindex="-1" role="dialog"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-body pd-20">
            <button type="button" class="close pos-absolute t-15 r-15" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"><i data-feather="x" class="wd-20"></i></span>
            </button>

            <h6 class="tx-uppercase tx-spacing-1 tx-semibold mg-b-20">Create Channel</h6>
            <input type="text" class="form-control" placeholder="Channel name" value="">
          </div>
          <div class="modal-footer pd-x-20 pd-b-20 pd-t-0 bd-t-0">
            <button type="button" class="btn btn-secondary tx-13" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary tx-13">Create</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script type="wbapp">
    wbapp.loadScripts(["{{_var.base}}./assets/js/cms.js"]);
  </script>
</div>
</html>
