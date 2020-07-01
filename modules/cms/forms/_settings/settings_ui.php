<html>
<div class="chat-wrapper chat-wrapper-two">
  <div class="chat-sidebar">
    <div class="chat-sidebar-body" style="top:0;bottom:0;">
      <div class="flex-fill pd-y-20 pd-x-10">
        <div class="d-flex align-items-center justify-content-between pd-x-10 mg-b-10">
          <span class="tx-10 tx-uppercase tx-medium tx-color-03 tx-sans tx-spacing-1"><i class="ri-settings-4-line"></i> {{_lang.settings}}</span>
        </div>
        <nav id="{{_form}}ListSettings" class="nav flex-column nav-chat mg-b-20">
          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_common/','html':'#editSettingsForm'}"
              auto>
              {{_lang.common}}
            </a>
          </span>
          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_vars/','html':'#editSettingsForm'}">
              {{_lang.variables}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_menu/','html':'#editSettingsForm'}">
              {{_lang.menu}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_prop/','html':'#editSettingsForm'}">
              {{_lang.properties}}
            </a>
          </span>


        </nav>
      </div>
    </div>
  </div>

  <div class="chat-content">
    <div id="editSettingsForm" class="m-2">

    </div>
  </div>

</div>

<wb-lang>
[en]
settings = Settings
common = Common
variables = Variables
properties = Properties
menu = Menu
[ru]
settings = Настройки
common = Основные
variables = Переменные
properties = Свойства
menu = Меню
</wb-lang>

</html>
