<html>
<div class="p-4 bg-white h-100">
    <form id="createSite" method="post">
        <nav class="navbar navbar-expand-lg px-0 mb-4">
            <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="#">Создание сайта</a>
            <button class="navbar-toggler order-2" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <svg class="mi mi-menu-dots wd-20 ht-20" wb-module="myicons"></svg>
            </button>

            <div class="collapse navbar-collapse order-2" id="navbarSupportedContent">
                <div class="form-inline mg-t-10 mg-lg-0  ml-auto">
                    <input class="form-control wd-200-f mg-r-10 rounded-10" type="text" name="site" required placeholder="Наименование" aria-label="Наименование">
                    <button class="btn btn-primary rounded-10 mg-r-10" type="button" onclick="createSite()">Создать</button>
                </div>
            </div>
        </nav>
        <div class="row">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <input type="radio" class="d-none" name="method" id="createSiteBuilder" value="builder" checked>
                <label class="card p-3 rounded-20" for="createSiteBuilder">
                    <img src="{{_env.tpl_path}}/assets/img/create_site_builder.svg" class="card-img-top" alt="Из конструктора">
                    <div class="card-body">
                        <h6 class="card-title">
                            <a class="arrow-after nobr">Из конструктора</a>
                        </h6>
                        <p class="card-text">Соберите свой сайт из готовых блоков</p>
                    </div>
                </label>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <input type="radio" class="d-none" name="method" id="createSiteTpl" value="template">
                <label class="card p-3 rounded-20" for="createSiteTpl">
                    <img src="{{_env.tpl_path}}/assets/img/create_site_tpl.svg" class="card-img-top" alt="Из шаблона">
                    <div class="card-body">
                        <h6 class="card-title">
                            <a class="arrow-after nobr">Из шаблона</a>
                        </h6>
                        <p class="card-text">Выберите свой сайт из готовых шаблонов</p>
                    </div>
                </label>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <input type="radio" class="d-none" name="method" id="createSiteUpload" value="upload">
                <label class="card p-3 rounded-20" for="createSiteUpload">
                    <img src="{{_env.tpl_path}}/assets/img/create_site_upload.svg" class="card-img-top" alt="Загрузить шаблон">
                    <div class="card-body">
                        <h6 class="card-title">
                            <a class="arrow-after nobr">Загрузить свой</a>
                        </h6>
                        <p class="card-text">Загрузите собственный шаблон формата Yonger </p>
                    </div>
                </label>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <input type="radio" class="d-none" name="method" id="createSiteImport" value="import">
                <label class="card p-3 rounded-20" for="createSiteImport">
                    <img src="{{_env.tpl_path}}/assets/img/create_site_import.svg" class="card-img-top" alt="Импорт сайта">
                    <div class="card-body">
                        <h6 class="card-title">
                            <a class="arrow-after nobr">Перенести сайт</a>
                        </h6>
                        <p class="card-text">Закажите перенос вашего сайта в Yonger</p>
                    </div>
                </label>
            </div>
        </div>
    </form>
</div>
</html>