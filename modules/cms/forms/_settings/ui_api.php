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
    </fieldset>

    <nav class="text-right">
      <button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'settings'}">
        <i class="fa fa-save"></i> Сохранить
      </button>
    </nav>
  </form>
  
  
  
  <form method="POST" id="mail">
      <input name="_message" value="qrwerqwerqwer">
      <a href="#" data-ajax="{'url':'/api/mail/','form':'#mail'}">send</a>
      
  </form>
  
</wb-data>

<wb-lang>
[en]    
apikey = "API key"
[ru]
apikey = "Ключ API"
</wb-lang>

</html>
