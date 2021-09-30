<html>
<div class="chat-wrapper chat-wrapper-two">
  <div class="chat-sidebar">
    <div class="chat-sidebar-body bg-light" style="top:0;bottom:0;">
      <div class="flex-fill pd-y-20 pd-x-10">
        <div class="d-flex align-items-center justify-content-between pd-x-10 mg-b-10">
          <span class="tx-30 tx-medium tx-gray-900 tx-sans tx-spacing-1"> {{_lang.settings}}</span>
        </div>
        <nav id="{{_form}}ListSettings" class="nav flex-column nav-chat mg-b-20 tx-14 lh-14">
          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_common/','html':'#editSettingsForm'}"
              auto>
              <img data-src="/module/myicons/filter-settings-sort.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.common}}
            </a>
          </span>
          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_vars/','html':'#editSettingsForm'}">
              <img data-src="/module/myicons/interface-essential-138.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.variables}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_menu/','html':'#editSettingsForm'}">
              <img data-src="/module/myicons/edit-pen-menu-square.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.menu}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_mods/','html':'#editSettingsForm'}">
              <img data-src="/module/myicons/toys-cubes.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.modules}}
            </a>
          </span>

          <span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_api/','html':'#editSettingsForm'}">
              <img data-src="/module/myicons/-onversion-exchange.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.api}}
            </a>
          </span>

					<span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/settings/ui_seo/','html':'#editSettingsForm'}">
              <img data-src="/module/myicons/browser-search-loap.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.seo}}
            </a>
          </span>

					<span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/cms/ajax/form/users/list_users/','html':'#editSettingsForm'}">
              <img data-src="/module/myicons/users.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.users}}
            </a>
          </span>

					<span class="nav-item">
            <a class="nav-link" href="#" data-ajax="{'url':'/module/filemanager','html':'.content-body'}">
              <img data-src="/module/myicons/programing-code-data-terminal.svg?size=24&stroke=979797" class="mg-r-10">
              {{_lang.fileman}}
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
