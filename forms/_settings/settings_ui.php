<html>
<div class="chat-wrapper chat-wrapper-two">
  <div class="chat-sidebar scroll-y">
    <div class="chat-sidebar-body bg-light hv-100p" style="top:0;bottom:0;">
      <div class="flex-fill pd-y-20 pd-x-10">
        <div class="d-flex align-items-center justify-content-between pd-x-10 mg-b-10">
          <span class="tx-30 tx-medium tx-gray-900 tx-sans tx-spacing-1"> {{_lang.settings}}</span>
        </div>
        <nav id="{{_form}}ListSettings" class="nav flex-column nav-chat mg-b-20 tx-14 lh-14">
          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_common/','html':'#editSettingsForm'}"
              auto>
              <svg class="mi mi-filter-settings-sort mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.common}}
            </a>
          </span>
          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_vars/','html':'#editSettingsForm'}">
              <svg class="mi mi-interface-essential-138 mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.variables}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_menu/','html':'#editSettingsForm'}">
              <svg class="mi mi-edit-pen-menu-square mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.menu}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_mods/','html':'#editSettingsForm'}">
              <svg class="mi mi-toys-cubes mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.modules}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_api/','html':'#editSettingsForm'}">
              <svg class="mi mi--onversion-exchange mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.api}}
            </a>
          </span>

					<span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_seo/','html':'#editSettingsForm'}">
              <svg class="mi mi-browser-search-loap mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.seo}}
            </a>
          </span>

					<span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/ajax/form/users/list_users/','html':'#editSettingsForm'}">
              <svg class="mi mi-users mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.users}}
            </a>
          </span>

					<span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/module/filemanager','html':'.content-body'}">
              <svg class="mi mi-programing-code-data-terminal mg-r-10" size="24" stroke="979797" wb-module="myicons"></svg>
              {{_lang.fileman}}
            </a>
          </span>

        </nav>
      </div>
    </div>
  </div>

  <div class="chat-content scroll-y">
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
api = API settings
modules = Modules
seo = SEO settings
users = Users
fileman = Filemanager
[ru]
settings = Настройки
common = Основные
variables = Переменные
properties = Свойства
menu = Меню
api = Настройки API
modules = Модули
seo = Настройки SEO
users = Пользователи
fileman = Файлменеджер
</wb-lang>

</html>
