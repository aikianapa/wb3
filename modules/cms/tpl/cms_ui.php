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
					<img data-src="/engine/modules/cms/tpl/assets/img/virus.svg" width="30" wb-if='"{{_sett.logo.0.img}}" == ""'>
          <img data-src="{{_sett.logo.0.img}}" width="30" wb-if='"{{_sett.logo.0.img}}" > ""'>
          <strong wb-if='"{{_sett.logo1}}" == ""'>Pad</strong>
          <strong wb-if='"{{_sett.logo1}}" > ""'>{{_sett.logo1}}</strong>

          <span wb-if='"{{_sett.logo2}}" > ""'>{{_sett.logo2}}</span>
          <span wb-if='"{{_sett.logo2}}" == ""'>Demic</span>
        </a>
        <a href="" class="aside-menu-link">
          <i data-feather="menu"></i>
          <i data-feather="x"></i>
        </a>
        <a href="javascript:$('body').removeClass('chat-content-show');"  class="burger-menu"><i class="ri-arrow-left-line"></i></a>
      </div>
      <div class="aside-body">
        <div class="aside-loggedin">
          <div  id="userProfileMenu">
          <template>
            <div class="d-flex align-items-center justify-content-start">
              <a href="#loggedinMenu" data-toggle="collapse" class="avatar">
                <img data-src="/thumbc/48x48/src/{{avatar.0.img}}" class="rounded-circle" alt="">
              </a>
              <div class="aside-alert-link">
                <a href="#" data-ajax="{'url':'/cms/ajax/form/users/profile','html':'.content-body'}" class="nav-link"><i class="ri-user-settings-line"></i></a>
                <a href="#" data-ajax="{'url':'/cms/logout'}" data-toggle="tooltip" title="{{_lang.signout}}"><i class="ri-logout-box-r-line"></i></a>
              </div>
            </div>
            <div class="aside-loggedin-user">
              <a href="#loggedinMenu" class="d-flex align-items-center justify-content-between mg-b-2"
                data-toggle="collapse">
                <h6 class="tx-semibold mg-b-0">{{first_name}} {{last_name}}</h6>
                <i data-feather="chevron-down"></i>
              </a>
              <p class="tx-color-03 tx-12 mg-b-0">{{role}}</p>
            </div>
          </template>
          </div>
          <div class="collapse" id="loggedinMenu">
            <ul class="nav nav-aside mg-b-0">
              <li class="nav-item">
                <a href="#" data-ajax="{'url':'/cms/ajax/form/users/profile','html':'.content-body'}" class="nav-link"><i class="ri-user-settings-line"></i>
                  <span>&nbsp;{{_lang.profile}}</span>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" data-ajax="{'url':'/cms/logout'}" class="nav-link"><i class="ri-logout-box-r-line"></i>
                  <span>&nbsp;{{_lang.signout}}</span>
                </a>
              </li>
            </ul>
          </div>
        </div>

        <ul class="nav nav-aside" wb-tree="{'table':'_settings','item':'settings','field':'cmsmenu','branch':'aside','parent':'false'}">
<level>
              <li class="nav-label mg-t-25" wb-if=' "{{_lvl}}" == "1" '>
                {{data.label}}
              </li>
              <li class="nav-item" wb-if=' "{{_lvl}}" == "2" '>
                <a href="#" data-ajax="{{data.ajax}}" class="nav-link"><i class="{{data.icon}}"></i>&nbsp;&nbsp;
                  <span>{{data.label}}</span>
                </a>
              </li>
</level>
        </ul>

      </div>
    </aside>

    <div class="content ht-100v pd-0">
      <div class="content-header">
        <div class="content-search">
          <i data-feather="search"></i>
          <input type="search" class="form-control" placeholder="Поиск..." disabled>
        </div>
        <nav class="nav">
          <a href="" class="nav-link"><i data-feather="help-circle"></i></a>
          <a href="" class="nav-link"><i data-feather="grid"></i></a>
          <a href="" class="nav-link"><i data-feather="align-left"></i></a>
        </nav>
      </div>
      <!-- content-header -->

      <div class="content-body pd-0">

        <meta href="#" data-ajax="{'url':'/cms/ajax/form/places/list','html':'.content-body'}" auto >

      </div>
    </div>
    <!-- content -->

  </div>

  <script type="wbapp">
    wbapp.loadScripts(["{{_var.base}}./assets/js/cms.js"]);
  </script>
</div>
<wb-lang>
[en]
forms = Forms
profile = Profile
signout = Sign Out
[ru]
forms = Формы
profile = Профиль
signout = Выход
</wb-lang>
</html>
