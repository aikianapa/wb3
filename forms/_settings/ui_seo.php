<html>
<nav class="nav navbar navbar-expand-md col">
    <a class="navbar-brand tx-bold tx-spacing--2 order-1" href="javascript:">{{_lang.header}}</a>
</nav>
<wb-data wb="{'table':'_settings','item':'seo'}">
    <form class="m-3">
        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Префикс для всех тайтлов</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="title_prepend" placeholder="Префикс для всех тайтлов">
            </div>
        </div>
        <p class="alert alert-info">{{_lang.descr}}</p>
        <wb-include wb="{'form':'common','mode':'seo'}" />

        <nav class="text-right">
            <button type="button" class="btn btn-primary tx-13 r-0" wb-save="{'table':'_settings','item':'seo'}">
                <i class="fa fa-save"></i> Сохранить
            </button>
        </nav>
    </form>
</wb-data>
<wb-lang>
    [en]
    header = "SEO settings"
    descr = "Next setting is global for all site pages, exclude pages with personal SEO settings."
    [ru]
    header = "Настройки SEO"
    descr = "Следующие настройки глобальны для всех страниц сайта, за исключением страниц имеющих персональные настройки
    SEO."
</wb-lang>

</html>