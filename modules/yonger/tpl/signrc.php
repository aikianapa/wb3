<!DOCTYPE html>
<html lang="en">
<wb-var assets="/modules/yonger/tpl/assets" />
<wb-include src="head.inc.php" />

<body class="bg-light">


    <wb-include wb-src="signhead.inc.php" />

    <div class="content content-fixed content-auth">
        <div class="container">
            <div class="ht-100p">

                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h1 class="display-4">Забыли пароль?</h1>
                        <p class="lead">Восстановите доступ или
                            <a href="/signin">выполните вход</a>.</p>
                    </div>
                </div>
                <div class="row  justify-content-center mt-5">
                    <div class="col-md-6">
                        <form class="tx-14" id="recover">
                            <fieldset class="form-fieldset recover-form">
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label>Укажите Email, который вы указывали при регистрации.</label>
                                        <input type="email" class="form-control" placeholder="Email" name="login" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control" placeholder="Новый пароль" name="password" required>
                                    </div>
                                    <div class="col-sm-6 mt-3 mt-sm-0">
                                        <input type="password" class="form-control" placeholder="Пароль повторно" name="password-confirm" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <a class="btn btn-primary w-100" href="javascript:wbapp.auth('#recover', 'recover');">Восстановить</a>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="recover-success alert alert-info text-center d-none">
                                Запрос на изменение пароля успешно отправлен.<br>Пожалуйста, проверьте почтовый ящик и перейдите по ссылке восстановления доступа.
                            </div>

                            <div class="recover-error alert alert-danger text-center mt-3 d-none">
                                Произошла ошибка отправки данных.<br>Пожалуйста, попробуйте ещё раз чуть позже.
                            </div>


                            <code class="d-none recover-text">
                                <h2>Восстановление пароля</h2>
                                <p align="justify">
                                Вы получили это письмо, потому что кто-то инициировал процедуру восстановления пароля на вашем аккаунте.
                                Если это были не Вы, то просто игнорируйте это письмо.<br>
                                Новый пароль для входа: <b>{{password}}</b><br>
                                Чтобы установить новый пароль, пожалуйста, перейдите по ссылке:<br><a href="{{recover}}">ВОССТАНОВИТЬ ДОСТУП</a>
                                </p>
                            </code>
                        </form>


                        <p class="text-center">
                            <a href="/signup">У вас еще нет учетной записи? Зарегистрироваться</a>
                        </p>
                    </div>
                </div>
            </div>
            <!-- media -->
        </div>
        <!-- container -->
    </div>
    <!-- content -->
    <wb-include wb-tpl="signfoot.inc.php" />
    <wb-include wb-snippet="wbapp" />
    <wb-include wb-snippet="lineawesome" />
    <wb-include wb-tpl="scripts.inc.php" />
</body>

</html>