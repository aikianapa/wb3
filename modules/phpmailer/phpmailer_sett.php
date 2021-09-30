<html>
  <div class="row form-group">
    <label class="col-sm-4 control-label" title="Set system mail function">Mail function</label>
    <div class="col-sm-8">
      <select class="col-sm-8 form-control" name="func">
        <option value="mail">mail</option>
        <option value="sendmail">sendmail</option>
      </select>
    </div>
  </div>

  <div class="row form-group">
    <label class="col-sm-4 control-label" title="Set mailer to use SMTP">Use SMTP</label>
    <div class="col-sm-8">
      <label class="switch switch-sm switch-success">
        <input type="checkbox" name="smtp" wb-module="switch">
        <span></span>
    </div>
  </div>

  <div class="row form-group">
    <label class="col-sm-4 control-label" title="Specify SMTP server">SMTP host</label>
    <div class="col-sm-8">
      <input type="text" name="host" class="form-control" placeholder="SMTP host">
    </div>
  </div>

  <div class="row form-group">
    <label class="col-sm-4 control-label" title="Specify SMTP server">SMTP port</label>
    <div class="col-sm-8">
      <input type="number" name="port" class="form-control" placeholder="SMTP port">
    </div>
  </div>

  <div class="row form-group">
    <label class="col-sm-4 control-label" title="Set system mail function">SMTP secure</label>
    <div class="col-sm-8">
      <select class="col-sm-8 form-control" name="secure" value="{{phmail[secure]}}">
        <option value="tls">TLS</option>
        <option value="ssl">SSL</option>
      </select>
    </div>
  </div>

  <hr>
  <div class="row form-group">
    <label class="col-sm-4 control-label" title="Enable SMTP authentication">SMTP Auth</label>
    <div class="col-sm-2">
      <label class="switch switch-success">
        <input type="checkbox" name="auth" wb-module="switch">
        <span></span>
      </label>
    </div>
  </div>

  <div class="row form-group">
    <label class="col-sm-4 control-label">SMTP username</label>
    <div class="col-sm-8">
      <input type="text" name="username" class="form-control" placeholder="SMTP username">
    </div>
  </div>

  <div class="row form-group">
    <label class="col-sm-4 control-label">SMTP password</label>
    <div class="col-sm-8">
      <input type="password" name="password" class="form-control" placeholder="SMTP password">
    </div>
  </div>

  <wb-lang>
    [en]
    settings = "Settings"
    ready = "Ready"
    [ru]
    settings = "Настройки"
    ready = "Готово"
  </wb-lang>
  </html>