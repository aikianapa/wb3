<div id="commonChangePassword">
    <fieldset class="form-group">
      <label>{{_lang.password}}</label>
      <input type="password" class="form-control pwd" placeholder="{{_lang.password}}" autocomplete="off">
    </fieldset>
    <fieldset class="form-group">
      <label>{{_lang.password}} ({{_lang.repeat}})</label>
      <input type="password" class="form-control chk" placeholder="{{_lang.password}} ({{_lang.repeat}})" autocomplete="off">
    </fieldset>
    <input type="hidden" name="password" />
    <button type="button" class="btn btn-sm btn-secondary" disabled>{{_lang.change}}</button>
    <script>
      function commonChangePassword() {
        let $form = $('#commonChangePassword');
        $form.find('input[type=password]').off('keyup');
        $form.find('input[type=password]').on('keyup',function(){
            let pwd = $form.find('input[type=password].pwd').val();
            let chk = $form.find('input[type=password].chk').val();
            if (pwd == chk && pwd > "") {
                $form.find('button')
                  .removeClass('btn-secondary')
                  .addClass('btn-success')
                  .prop('disabled',false);
            } else {
              $form.find('button')
                .addClass('btn-secondary')
                .removeClass('btn-success')
                .prop('disabled',true);
            }
        });
        $form.find('button').off('tap click');
        $form.find('button').on('tap click',function(){
            $.post('/api/genpass/post',{'pass':$form.find('input[type=password].pwd').val()},function(data){
                if (data.result !== undefined) {
                    $form.find('input[name=password]').val(data.result);
                }
            })
        });
      }
      commonChangePassword();
    </script>
<wb-lang>
[en]
password = Password
repeat = repeat
change = Change

[ru]
password = Пароль
repeat = повторить
change = Изменить
</wb-lang>

</div>
