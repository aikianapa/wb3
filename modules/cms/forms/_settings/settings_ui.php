<html>
<div class="chat-wrapper chat-wrapper-two">
  <div class="chat-sidebar">
    <div class="chat-sidebar-body" style="top:0;bottom:0;">
      <div class="flex-fill pd-y-20 pd-x-10">
        <div class="d-flex align-items-center justify-content-between pd-x-10 mg-b-10">
          <span class="tx-10 tx-uppercase tx-medium tx-color-03 tx-sans tx-spacing-1"><i class="fa fa-gears"></i> Настройки</span>
        </div>
        <nav id="{{_form}}ListSettings" class="nav flex-column nav-chat mg-b-20">
            <span class="nav-link">
            <a href="#" data-ajax="{'url':'/cms/settings/ui_common/','html':'#editSettingsForm'}" auto>
              Основные
            </a>
          </span>
          <span class="nav-link">
          <a href="#" data-ajax="{'url':'/cms/settings/ui_vars/','html':'#editSettingsForm'}">
            Переменные
          </a>
        </span>

        <span class="nav-link">
        <a href="#" data-ajax="{'url':'/cms/settings/ui_prop/','html':'#editSettingsForm'}">
          Свойства
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
</html>
