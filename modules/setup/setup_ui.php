<!DOCTYPE html>
<html>
<wb-snippet wb="name=wbapp" />
<link rel="stylesheet" type="text/css" href="/engine/modules/cms/tpl/assets/css/dashforge.css" />
<link rel="stylesheet" type="text/css" href="/engine/modules/cms/tpl/assets/css/cms.less" />

<div id="setup" class="container mt-5">
    <div class="card">
        <div class="card-header bg-light">
            <div class="row">
                <div class='col-auto p-0 px-2'>
                    <img data-src="/engine/modules/cms/tpl/assets/img/virus.svg" width="50" />
                </div>
                <div class='col-auto p-0'>
                    <h2 class="tx-30 p-0 wblogo">
                        <i class="text-dark">Web</i><i class="text-primary">Basic</i><br>
                        <i class="tx-13 text-dark pt-2">Pandemic edition</i>
                    </h2>
                </div>
            </div>
        </div>


        <form id="setup" method="post" class="card-body">
            <input type="hidden" name="setup" value="start" />
            <div id="wizard">
                <section>

                    <div class="form-group">
                        <label for="">{{_LANG[header]}}</label>
                        <input class="form-control" placeholder="{{_LANG[header]}}" type="text" name="header" required>
                    </div>

                    <div class="form-group">
                        <label for="">{{_LANG[login]}} ({{_LANG[email]}})</label>
                        <input class="form-control" placeholder="{{_LANG[login]}} ({{_LANG[email]}})" value=""
                            type="email" name="email" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">{{_LANG[password]}}</label>
                                <input class="form-control" placeholder="{{_LANG[password]}}" type="password"
                                    name="password" minlength='3' required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">{{_LANG[chkpass]}}</label>
                                <input class="form-control" placeholder="{{_LANG[chkpass]}}" type="password"
                                    name="password_check" minlength='3' required>
                            </div>
                        </div>
                    </div>
                </section>
                <button class="btn btn-primary btn-install float-right" disabled type="submit">{{_lang.btn_install}}</button>
            </div>
        </form>
    </div>

    <div id="errors" class="mt-5 alert alert-warning d-none">
        <h5 class='text-dark'>{{_lang.error_ins}}</h5>
        <ol></ol>
    </div>

</div>

<script type="wbapp" src="/engine/modules/setup/wbsetup.js"></script>

<wb-lang>
    [en]
    _locale = English
    _flag = /engine/tpl/img/flags-icons/uk.png
    settings = Settings
    slogan = First time setup
    step1 = Main
    step2 = Security
    step3 = Template
    step4 = Installation
    header = Site header
    email = Email
    lang = Language
    login = Admin Login
    password = Admin Password
    chkpass = Check password
    beautifyHtml = Beautify HTML
    merchant = Payment service
    variable = Variable
    value = Value
    ulogin = Social login
    description = Description
    path_tpl = Template folder
    msg_ready = Ready to installation. Click Install button to Start.
    btn_prev = Prev
    btn_next = Next
    btn_install = Install
    btn_save = Save
    tab_cache = Cache
    tab_main = Main
    tab_appends = Appends
    tab_prop = Properties
    tab_update = Update
    tab_backup = Backup
    tab_sitemap = Sitemap
    tab_fileman = Filemanager
    tab_users = Users
    tab_tree = Catalogs
    tab_shortcode = Shortcodes
    error_log = Logging errors
    error_ins = "Installation Error!"
    head_inc = "Append to HEAD"
    body_inc = "Append to BODY"
    utilites = "Utilites"
    code = "Code"
    sc_card_title = "Sample to use shortcode"
    
    [ru]
    _locale = Русский
    _flag = /engine/tpl/img/flags-icons/ru.png
    settings = Настройки
    slogan = Начальная настройка сайта
    step1 = Основное
    step2 = Безопасность
    step3 = Шаблон
    step4 = Установка
    header = Заголовок сайта
    email = Эл.почта
    lang = Язык интерфейса
    login = Логин админа
    password = Пароль админа
    chkpass = Повтор пароля
    beautifyHtml = Форматировать HTML
    merchant = Платёжная система
    variable = Переменная
    value = Значение
    ulogin = Вход через соцсети
    description = Описание
    path_tpl = Директория темплейта
    msg_ready = Всё готово к установке. Нажмите кнопку Установить для начала процесса.
    btn_prev = Назад
    btn_next = Далее
    btn_install = Установить
    btn_save = Сохранить
    tab_cache = Кэширование
    tab_main = Основные
    tab_appends = "Встаки HEAD & BODY"
    tab_prop = Характеристики
    tab_update = Обновление
    tab_backup = Бэкап
    tab_sitemap = Карта сайта
    tab_fileman = Файлменеджер
    tab_users = Пользователи
    tab_tree = Каталоги
    tab_shortcode = Короткие вставки
    error_log = Включить лог ошибок
    error_ins = "Ошибка установки!"
    head_inc = "Вставка в HEAD"
    body_inc = "Вставка в BODY"
    utilites = "Утилиты"
    code = "Код"
    sc_card_title = "Как использовать shortcode"
</wb-lang>

</html>