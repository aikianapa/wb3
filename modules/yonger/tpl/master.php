<html lang="en">
<meta http-equiv="refresh" content="2; url=/signin/" wb-disallow="user">
<wb-include wb="{'src':'/modules/yonger/tpl/head.inc.php'}" />

<body wb-allow="user">
    <div id="startMaster">
        <div class="row h-100">
            <div class="col-4 bg-dark px-5 py-2" id="steps">
                <div class="d-flex flex-wrap h-100">
                <div class="w-100">
                    <img src="/tpl/assets/img/logo.svg" class="my-3" style="width: 150px;">
                </div>
                    <ul class="steps steps-vertical text-white-50" role="tablist">
                        <li class="step-item active">
                            <a href="#step-1" class="step-link">
                                <span class="step-number">1</span>
                                <span class="step-title">Адрес профиля</span>
                            </a>
                        </li>
                        <li class="step-item">
                            <a href="#step-2" class="step-link">
                                <span class="step-number">2</span>
                                <span class="step-title">Сфера деятельности</span>
                            </a>
                        </li>
                        <li class="step-item">
                            <a href="#step-3" class="step-link">
                                <span class="step-number">3</span>
                                <span class="step-title">Какова ваша главная цель</span>
                            </a>
                        </li>
                        <li class="step-item">
                            <a href="#step-4" class="step-link">
                                <span class="step-number">4</span>
                                <span class="step-title">Личные данные</span>
                            </a>
                        </li>
                        <li class="step-item">
                            <a href="#step-5" class="step-link">
                                <span class="step-number">5</span>
                                <span class="step-title">Данные компании</span>
                            </a>
                        </li>
                    </ul>

                </div>
            </div>
            <div class="col-8 pt-5" id="form">
                <div class="d-flex align-items-start pt-5 h-100">
                    <div class="container">
                        <form class="text-black-50 tab-content" autocomplete="off">
                            <div class="tab-pane active" id="step-1">
                                <h4>Адрес вашего профиля</h4>
                                <p class="my-4">
                                    На этом этапе, вы можете создать уникальный адрес для своего профиля. Просто введите
                                    его
                                    в форму ниже. Ваш личный кабинет будет находиться по адресу yourprofile.yonger.ru
                                </p>

                                <div class="input-group">
                                    <input class="form-control" type="text" name="login" placeholder="Ваш профиль" wb-module="smartid">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="my-addon">.{{_route.domain}}</span>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-6">
                                        <a href="#" onclick="wbapp.yongerMaster.nextstep(this);"
                                            class="btn btn-primary w-100">Продолжить</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" onclick="wbapp.yongerMaster.nextstep(this,false);"
                                            class="btn btn-secondary w-100">Пропустить</a>
                                    </div>
                                </div>
                                <p class="my-4 text-muted">
                                    * Если вы пропустите этот шаг, личный кабинет будет создан со случайным
                                    сгенерированным адресом
                                </p>
                            </div>

                            <div class="tab-pane" id="step-2">
                                <h4>Сфера деятельности</h4>
                                <p class="my-4">
                                    Выберите один из наиболее подходящих вариантов характеризующих вашу профессиональную
                                    деятельность
                                </p>
                                <div class="input-group">
                                    <select class="form-control" name="activity" required placeholder="Сфера деятельности">
                                        <option value="" disabled selected>Сфера деятельности</option>
                                        <option value="1">Маркетинг и дизайн</option>
                                        <option value="2">Продажи</option>
                                        <option value="3">Разработка ПО</option>
                                        <option value="4">Бухгалтерия</option>
                                        <option value="5">Консалтинг</option>
                                    </select>

                                </div>
                                <div class="row mt-4">
                                    <div class="col-6">
                                        <a href="#" onclick="wbapp.yongerMaster.nextstep(this);"
                                            class="btn btn-primary w-100">Продолжить</a>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="step-3">
                                <h4>Какова ваша главная цель?</h4>
                                <p class="my-4">
                                    Что вас больше всего интересует в использовании сервиса Yonger?
                                </p>

                                <div class="col-12 pb-2">
                                    <input type="radio" class="form-control hidden" id="activityType1" checked
                                        value="1" name="activity">
                                    <label class="checkholder px-4" for="activityType1">
                                        <div class="icon">
                                            <svg class="mi-hotel-building size-24" stroke="323232"
                                                wb-module="myicons"></svg>
                                        </div>
                                        <div class="d-block p-4">
                                            <h5><span class="px-0">Управление проектами</span></h5>
                                            <p>Контролируйте сроки выполнения задач и соблюдения графика проектов.</p>
                                            <div class="row text-secondary">
                                                <div class="col-12 pb-2 tx-14">
                                                    Включает:
                                                </div>
                                                <div class="col nobr"><svg class="mi-checkmark-sqaure.1 size-20"
                                                        stroke="7987a1" wb-module="myicons"></svg> Проекты</div>
                                                <div class="col nobr"><svg class="mi-document-content.16 size-20"
                                                        stroke="7987a1" wb-module="myicons"></svg> Документы</div>
                                                <div class="col nobr"><svg class="mi-user-square size-20" stroke="7987a1"
                                                        wb-module="myicons"></svg> Контакты</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-12 pb-2">
                                    <input type="radio" class="form-control hidden" id="activityType2" 
                                        value="2" name="activity">
                                    <label class="checkholder px-4" for="activityType2">
                                        <div class="icon">
                                            <svg class="mi-hotel-building size-24" stroke="323232"
                                                wb-module="myicons"></svg>
                                        </div>
                                        <div class="d-block p-4">
                                            <h5><span class="px-0">Управление проектами</span></h5>
                                            <p>Контролируйте сроки выполнения задач и соблюдения графика проектов.</p>
                                            <div class="row text-secondary">
                                                <div class="col-12 pb-2 tx-14">
                                                    Включает:
                                                </div>
                                                <div class="col nobr"><svg class="mi-checkmark-sqaure.1 size-20"
                                                        stroke="7987a1" wb-module="myicons"></svg> Проекты</div>
                                                <div class="col nobr"><svg class="mi-document-content.16 size-20"
                                                        stroke="7987a1" wb-module="myicons"></svg> Документы</div>
                                                <div class="col nobr"><svg class="mi-user-square size-20" stroke="7987a1"
                                                        wb-module="myicons"></svg> Контакты</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-12 pb-2">
                                    <input type="radio" class="form-control hidden" id="activityType3" 
                                        value="3" name="activity">
                                    <label class="checkholder px-4" for="activityType3">
                                        <div class="icon">
                                            <svg class="mi-hotel-building size-24" stroke="323232"
                                                wb-module="myicons"></svg>
                                        </div>
                                        <div class="d-block p-4">
                                            <h5><span class="px-0">Управление проектами</span></h5>
                                            <p>Контролируйте сроки выполнения задач и соблюдения графика проектов.</p>
                                            <div class="row text-secondary">
                                                <div class="col-12 pb-2 tx-14">
                                                    Включает:
                                                </div>
                                                <div class="col nobr"><svg class="mi-checkmark-sqaure.1 size-20"
                                                        stroke="7987a1" wb-module="myicons"></svg> Проекты</div>
                                                <div class="col nobr"><svg class="mi-document-content.16 size-20"
                                                        stroke="7987a1" wb-module="myicons"></svg> Документы</div>
                                                <div class="col nobr"><svg class="mi-user-square size-20" stroke="7987a1"
                                                        wb-module="myicons"></svg> Контакты</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-6">
                                        <a href="#" onclick="wbapp.yongerMaster.nextstep(this);"
                                            class="btn btn-primary w-100">Продолжить</a>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="step-4">
                                <h4>Расскажите немного о себе</h4>
                                <p class="my-4">
                                    Мы просим указать лишь самый минимум информации
                                </p>
                                <div class="row mt-4">
                                    <div class="col-12 col-md-6 pb-2">
                                        <label class="form-control-label">Имя</label>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                    <div class="col-12 col-md-6 pb-2">
                                        <label class="form-control-label">Фамилия</label>
                                        <input type="text" class="form-control" name="last_name" required>
                                    </div>
                                    <div class="col-12 col-md-6 pb-2">
                                        <label class="form-control-label">Эл.почта</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="col-12 col-md-6 pb-2">
                                        <label class="form-control-label invisible">Проверочный код</label>
                                        <button
                                            class="form-control btn btn-secondary btn-send-code text-light bg-secondary"
                                            type="button">Получить код</button>
                                        <input type="text" placeholder="Проверочный код" wb-mask='999-999'
                                            class="form-control after-send-code d-none" data-name="code">
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-12 col-md-6 pb-2" id="avatar">
                                        <template data-params="bind=cms.profile.user&render=client">
                                            <div class="tx-16 d-flex align-items-center">
                                                <div class="avatar mr-3">
                                                    {{#if avatar !== "/" }}
                                                    <img data-src="/thumbc/64x64/src{{avatar}}"
                                                        class="rounded-circle" alt="" width="36" height="36">
                                                    {{else}}
                                                    <img src="/module/myicons/photo-image-square.1.svg?size=36&stroke=323232" width="36" height="36" />
                                                    {{/if}}
                                                </div>
                                                {{#if avatar !== "/"}}
                                                <a href="#" class="mr-3" id="avatarChange">
                                                    <img src="/module/myicons/arrows-refresh-reload-loading.svg?stroke=323232" width="24" height="24" />
                                                    <span class="pl-1">Заменить</span>
                                                </a>
                                                <a href="#" class="mr-3" id="avatarRemove">
                                                    <img src="/module/myicons/trash-delete-bin.2.svg?stroke=323232" width="24" height="24" />
                                                    <span class="pl-1">Удалить</span>
                                                </a>
                                                {{else}}
                                                <a href="#" class="mr-3" id="avatarAdd">
                                                    <span>Добавить аватар</span>
                                                </a>
                                                {{/if}}
                                            </div>
                                        </template>
                                    </div>
                                    <input wb="module=filepicker&mode=single" class="d-none" wb-path="/uploads/avatars" name="avatar">
                                </div>
                                <div class="row mt-4">
                                    <div class="col-6">
                                        <a href="#" onclick="wbapp.yongerMaster.nextstep(this);"
                                            class="btn btn-primary w-100">Продолжить</a>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="step-5">
                                <p class="my-4">
                                    Выберите тип компании
                                </p>
                                <div class="row mt-4">
                                    <div class="col-12 col-lg-4 pb-2">
                                        <input type="radio" class="form-control hidden" id="conpanyType1" checked
                                            value="jur" name="company_type" _required>
                                        <label class="checkholder" for="conpanyType1">
                                            <div class="icon">
                                                <svg class="mi-hotel-building size-24" stroke="323232"
                                                    wb-module="myicons"></svg>
                                            </div>
                                            <span>Юридическое лицо</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-lg-4 pb-2">
                                        <input type="radio" class="form-control hidden" id="conpanyType2" value="ip"
                                            name="company_type" _required>
                                        <label class="checkholder" for="conpanyType2">
                                            <div class="icon">
                                                <svg class="mi-business-chart.4 size-24" stroke="323232"
                                                    wb-module="myicons"></svg>
                                            </div>
                                            <span>Индивидуальный предприниматель</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-lg-4 pb-2">
                                        <input type="radio" class="form-control hidden" id="conpanyType3" value="fiz"
                                            name="company_type" _required>
                                        <label class="checkholder" for="conpanyType3">
                                            <div class="icon">
                                                <svg class="mi-user-profile.1 size-24" stroke="323232"
                                                    wb-module="myicons"></svg>
                                            </div>
                                            <span>Физ.лицо (Самозанятый)</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-lg-7">
                                        <p>
                                            Мы настроим сервис под тип вашей организации.<br>Реквизиты будут
                                            использоваться при создании документов
                                        </p>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-6">
                                        <a href="#" onclick="wbapp.yongerMaster.nextstep(this);"
                                            class="btn btn-success w-100" id="finish">Финиш</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <wb-jq wb="$dom->find('input')->attr('autocomplete','off')" ></wb-jq>
</body>
<wb-include wb="{'src':'/modules/yonger/tpl/foot.inc.php'}" />
<script wb-app>
    wbapp.yongerMaster = {
        init: function() {
            wbapp.render('#avatar');
            $('#startMaster #steps .step-link').on('tap click',function(){
                if (!$(this).parent('.step-item').is('.active,.complete')) return;
                if (!wbapp.yongerMaster.checkform()) return;
                let pos = $(this).parent('.step-item').index();
                let id = $(this).attr('href');
                $('#startMaster #steps .step-item').removeClass('active').removeClass('complete');
                $('#startMaster #steps .step-item').each(function(i){
                    if (i < pos) $(this).addClass('complete');
                });
                $('#startMaster #form .tab-pane.active').removeClass('active');
                $('#startMaster #form .tab-pane'+ id).addClass('active');
                
                $(this).parent('.step-item').addClass('active').removeClass('complete');
            });

            $('#startMaster #form').delegate('#avatarAdd, #avatarChange','tap click',function(){
                $('#startMaster #form .filepicker .card').trigger('click');
            })
            $('#startMaster #form').delegate('#avatarRemove','tap click',function(){
                $('#startMaster #form .filepicker .card a.delete').trigger('click');
                wbapp.storage('cms.profile.user.avatar','/');
            })

            $('#startMaster #form .filepicker').on('mod-filepicker-done',function(e,list){
                wbapp.storage('cms.profile.user.avatar',list[0].img);
            });
        },
        nextstep: function(ev, verify = true) {
            if (verify && !wbapp.yongerMaster.checkform()) return;
            let $pane = $(ev).parents('.tab-pane.active');
            let id = $pane.attr('id');
            let $step = $('#startMaster #steps .step-item.active:last').next('.step-item').addClass('active');
            $pane.next().addClass('active');
            $pane.removeClass('active');
            if ($(ev).is('#finish')) wbapp.yongerMaster.finish();
        },
        finish: function() {
            let form = $('#startMaster #form form').serializeJson();
            let data = wbapp.postSync('/module/yonger/finishRegistration',form);
            if (data.error == true) {
                $('#startMaster #steps .step-link:eq(0)').trigger('click');
            } else {
                document.location.reload();
            }
        },
        checkform: function() {
            if ($('form').verify()) {
                $('#startMaster #steps .step-item.active').addClass('complete');
                return true;
            } else {
                return false;
            }
        }
    }
    setTimeout(function() {
        wbapp.yongerMaster.init();
    },0);
</script>
</body>

</html>