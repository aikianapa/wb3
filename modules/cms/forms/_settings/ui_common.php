<html>
<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">Основные настройки</a>
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
      <label class="col-sm-3 form-control-label">{{_lang.showstats}}</label>
      <div class="col-sm-9">
        <input wb="module=switch" name="showstats" />
      </div>
    </div>
    
    <div class="row">
      <div class="col-sm-7 mt-5">
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
            <input type="number" class="form-control" name="logofontsize" min="12" max="30" step="1" placeholder="{{_lang.fontsize}} (px)">
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
save = "Save"
fontsize = "Font size"
logo = "Logo"
brand1 = "Brand (part I)"
brand2 = "Brand (part II)"
sitename = "Site name"
email = "Email"
pagesize = "Page size (default)"
cachelt = "Cache lifetime (sec.)"
showstats = "Statistics at bottom"
[ru]
save = "Сохранить"
fontsize = "Размер шрифта"
logo = "Логотип"
brand1 = "Брэнд (I часть)"
brand2 = "Брэнд (part II)"
sitename = "Название сайта"
email = "Эл.почта"
pagesize = "Размер страниц (по-умолчанию)"
cachelt = "Время жизни кэша (сек.)"
showstats = "Показывать статистику"
</wb-lang>
</html>
