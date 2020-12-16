<html>
<nav class="nav navbar navbar-expand-md col">
  <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">Основные настройки</a>
</nav>
<wb-data wb="{'table':'_settings','item':'settings'}">
  <form class="m-3">
    <div>
    </div>
    <fieldset class="form-group">
      <label for="_setApiKey">{{_lang.apikey}}</label>
      <input type="text" class="form-control" name="api_key" id="_setApiKey" placeholder="{{_lang.apikey}}">
			<p>
				<small>
				Для получения доступа к API необходимо получить token: <i>/api/token/</i>
				</small>
			</p>
    </fieldset>

		<div class="form-group row">
      <label class="col-sm-4 form-control-label">{{_lang.allow}}</label>
      <div class="col-sm-8">
          <input class="form-control" wb="module=tagsinput" name="api_allow" />
      </div>
    </div>

		<div class="form-group row">
      <label class="col-sm-4 form-control-label">{{_lang.disallow}}</label>
      <div class="col-sm-8">
          <input class="form-control" wb="module=tagsinput" name="api_disallow" />
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-4 form-control-label">{{_lang.reqfor}} /api</label>
      <div class="col-sm-8">
          <wb-module wb="module=switch" name="api_key_query" />
      </div>
    </div>

    <div class="form-group row">
      <label class="col-sm-4 form-control-label">{{_lang.reqfor}} /api/mail</label>
      <div class="col-sm-8">
          <wb-module wb="module=switch" name="api_key_mail" />
      </div>
    </div>

    <nav class="text-right">
      <button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
        <i class="fa fa-save"></i> Сохранить
      </button>
    </nav>
  </form>
</wb-data>

<wb-lang>
[en]
apikey = "API key"
reqfor = "Required for"
allow = "Allow"
disallow = "Disallow"
[ru]
apikey = "Ключ API"
reqfor = "Требовать для"
allow = "Разрешено"
disallow = "Запрещено"
</wb-lang>

</html>
