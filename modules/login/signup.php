<!DOCTYPE html>
<html lang="en">

<wb-include wb-src="/engine/modules/login/head.inc.php" />

<body class="bg-light">
    <div class="content content-fixed">
        <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-6 text-center">
                        <h1>Регистрация</h1>
                        <p class="lead">Давайте сначала зарегистрируемся на сайте.</p>
                    </div>
                </div>
                <div class="row justify-content-center mt-5">
                    <div class="col-md-6">
                        <form class="tx-14" id="signup">
                            <fieldset class="form-fieldset card p-3">
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" placeholder="Имя" name="first_name" required>
                                    </div>

                                    <div class="col-sm-6 mt-3 mt-sm-0">
                                        <input type="text" class="form-control" placeholder="Фамилия" name="last_name" required>
                                    </div>
                                </div>
<!--
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" placeholder="Номер телефона" name="phone" wb="module=mask" wb-mask="+7 (999) 999-99-99">
                                    </div>

                                    <div class="col-sm-6 mt-3 mt-sm-0">
                                        <input type="text" class="form-control" placeholder="Проверочный код" data-name="phone-confirm">
                                    </div>
                                </div>
-->
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <input type="email" class="form-control" placeholder="Электронная почта" name="email" required>
                                    </div>
                                </div>
<!--
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Придумайте логин" name="login" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-white">.{{_route.domain}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
-->
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control" placeholder="Пароль" name="password" required>
                                    </div>
                                    <div class="col-sm-6 mt-3 mt-sm-0">
                                        <input type="password" class="form-control" placeholder="Пароль повторно" name="password-confirm" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-4">
                                    <a class="btn btn-secondary w-100" href="/signin">Войти</a>
                                    </div>
                                    <div class="col-sm-8">
                                    <a class="btn btn-primary w-100" href="javascript:wbapp.auth('#signup','signup');">Зарегистрироваться</a>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="signup-error alert alert-danger text-center mt-3 d-none">
                                Не удалось выполнить регистрацию пользователя.<br>Возможно, такой пользователь уже существует.
                            </div>
                            <div class="signup-success alert alert-success text-center mt-3 d-none">
                                <p>Вы успешно зарегистрировались и можете войти в систему.</p>
                                <a class="btn btn-primary w-50" href="/signin">Войти</a>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        <!-- container -->
    </div>
    <!-- content -->
    <wb-include wb-snippet="bootstrap" />
    <wb-include wb-snippet="wbapp" />
    <wb-include wb-snippet="fontawesome4" />
    <wb-include wb-src="/engine/modules/login/scripts.inc.php" />
</body>

</html>