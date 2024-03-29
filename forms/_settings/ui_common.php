<html>
<nav class="nav navbar navbar-expand-md col">
  <a class="order-1 navbar-brand tx-bold tx-spacing--2" href="javascript:">{{_lang.header}}</a>
</nav>

  <form class="m-3">
  <wb-data wb="{'table':'_settings','item':'settings'}">

    <div class="form-group row">
      <label class="col-sm-3 form-control-label">{{_lang.sitename}}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control" name="header" id="_setSiteHeader" placeholder="{{_lang.sitename}}">
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-3 form-control-label">{{_lang.email}}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control" name="email" id="_setSiteEmail" placeholder="{{_lang.email}}">
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-3 form-control-label">{{_lang.locales}}<br><small>(en, ru...)</small></label>
      <div class="col-sm-9">
        <input type="text" class="form-control" name="locales" placeholder="{{_lang.locales}}" wb-module="tagsinput">
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-3 form-control-label">{{_lang.pagesize}}</label>
      <div class="col-sm-9">
        <input type="number" class="form-control" name="page_size" min="1" max="500" step="1" placeholder="{{_lang.pagesize}}">
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-3 form-control-label">{{_lang.cachelt}}</label>
      <div class="col-sm-9">
        <input type="number" class="form-control" name="cache" min="0" step="1" placeholder="{{_lang.cachelt}}">
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-3 form-control-label">{{_lang.devmode}}</label>
      <div class="col-sm-9">
          <wb-module wb="module=switch" name="devmode" />
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-3 form-control-label">{{_lang.showstats}}</label>
      <div class="col-sm-9">
        <input wb="module=switch" name="showstats" />
      </div>
    </div>
    
    <div class="row">
      <div class="mt-5 col-sm-7">
        <div class="form-group row">
          <label class="col-sm-5 form-control-label">{{_lang.brand1}}</label>
          <div class="col-sm-7">
            <input type="text" class="form-control" name="logo1" placeholder="{{_lang.brand1}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-5 form-control-label">{{_lang.brand2}}</label>
          <div class="col-sm-7">
            <input type="text" class="form-control" name="logo2" placeholder="{{_lang.brand2}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-5 form-control-label">{{_lang.fontsize}} (px)</label>
          <div class="col-sm-7">
            <input type="number" class="form-control" name="logofontsize" min="12" max="50" step="1" placeholder="{{_lang.fontsize}} (px)">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-5 form-control-label">{{_lang.slogan}}</label>
          <div class="col-sm-7">
            <input type="text" class="form-control" name="slogan" placeholder="{{_lang.slogan}}">
          </div>
        </div>

      </div>
      <div class="col-sm-5">
        <fieldset class="form-group">
          <label for="_setSiteLogo">{{_lang.logo}}</label>
          <input wb="module=filepicker&mode=single" name="logo">
        </fieldset>
      </div>
    </div>

    <nav class="text-right">
      <button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
        <i class="fa fa-save"></i> {{_lang.save}}
      </button>
    </nav>
</wb-data>
  </form>


<wb-lang>
[en]
header = "Common settings"
save = "Save"
fontsize = "Font size"
logo = "Logo"
brand1 = "Brand (part I)"
brand2 = "Brand (part II)"
slogan = Slogan
sitename = "Site name"
email = "Email"
pagesize = "Page size (default)"
cachelt = "Cache lifetime (sec.)"
showstats = "Statistics at bottom"
locales = Localizations
devmode = Developer mode
[ru]
header = "Общие настройки"
save = "Сохранить"
fontsize = "Размер шрифта"
logo = "Логотип"
brand1 = "Брэнд (I часть)"
brand2 = "Брэнд (part II)"
slogan = Слоган
sitename = "Название сайта"
email = "Эл.почта"
pagesize = "Размер страниц (по-умолчанию)"
cachelt = "Время жизни кэша (сек.)"
showstats = "Показывать статистику"
locales = Локализации
devmode = Режим разработчика
</wb-lang>
</html>
