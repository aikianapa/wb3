<html>
<view head>
    <meta name="Description" content="{{descr}}">
    <meta name="keywords" content="{{keywords}}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{_parent.header}}">
    <meta property="og:description" content="{{descr}}">
    <meta property="og:url" content="{{_route.host}}{{_route.uri}}">
    <meta property="og:image"
        content="{{_route.host}}/mikrorazmetka-chto-nuzhno-znat-seo-spetsialistu-o-slovaryakh-i-sintaksise.png">

    <meta property="url" content="{{_route.host}}{{_route.uri}}">
    <meta property="name" content="{{_parent.header}}">
    <meta property="description" content="{{descr}}">
    <meta property="image"
        content="{{_route.host}}/mikrorazmetka-chto-nuzhno-znat-seo-spetsialistu-o-slovaryakh-i-sintaksise.png">

    <meta itemprop="url" content="{{_route.host}}{{_route.uri}}">
    <meta itemprop="name" content="{{_parent.header}}">
    <meta itemprop="description" content="{{descr}}">
    <meta itemprop="image"
        content="{{_route.host}}/mikrorazmetka-chto-nuzhno-znat-seo-spetsialistu-o-slovaryakh-i-sintaksise.png">

    <link href="{{_route.host}}{{_route.uri}}" rel="canonical">

</view>
<edit header="{{_lang.header}}">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <wb-multilang wb-lang="{{_sett.locales}}" name="lang">
        <div class="form-group row">
            <label class="form-control-label col-md-3">{{_lang.descr}}</label>
            <div class="col-md-9">
                <textarea class="form-control" name="descr" rows="auto" placeholder="{{_lang.descr}}"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label class="form-control-label col-md-3">{{_lang.keywords}}</label>
            <div class="col-9">
                <input type="text" class="form-control" name="keywords" placeholder="{{_lang.keywords}}"
                    wb-module="tagsinput">
            </div>
        </div>
    </wb-multilang>
    <wb-lang>
        [ru]
        header = "Поисковая оптимизация"
        descr = Описание
        keywords = Ключевые слова
        [en]
        header = "Search Engine Optimization"
        descr = Description
        keywords = Keywords
    </wb-lang>
</edit>

</html>