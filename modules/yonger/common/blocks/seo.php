<html>
<view head>
    <title>{{title}}</title>
    <meta name="description" content="{{descr}}">
    <meta name="keywords" content="{{keywords}}">
    <link href="{{_route.host}}{{_route.uri}}" rel="canonical">
</view>
<edit header="{{_lang.header}}" head>
    <div id="yongerBlockSeoAltUrl">
        <div>
            <wb-module wb="module=yonger&mode=edit&block=common.inc" />
        </div>

        <div class="form-group row">
            <label class="form-control-label col-md-3">{{_lang.alturl}}</label>
            <div class="col-md-9">
                <input type="text" class="form-control" name="alturl" placeholder="{{_lang.alturl}}" on-change="alturl">
            </div>
        </div>
    </div>
            <script>
            $('#yongerBlockSeoAltUrl .yonger-block-common input[name=active]').attr('on-change','alturl')
            new Ractive({
                el: '#yongerBlockSeoAltUrl',
                template: $('#yongerBlockSeoAltUrl').html(),
                on: {
                    alturl() {
                        yonger.pageEditor.changePath()
                    }
                }
            })
            </script>

    <wb-multilang wb-lang="{{_sett.locales}}" name="lang">
        <div class="form-group row">
            <label class="form-control-label col-md-3">{{_lang.title}}</label>
            <div class="col-md-9">
                <input type="text" class="form-control" name="title" placeholder="{{_lang.title}}">
            </div>
        </div>

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
        title = Заголовок
        alturl = Альтернативный URL
        [en]
        header = "Search Engine Optimization"
        descr = Description
        keywords = Keywords
        title = Title
        alturl = Alternative URL
    </wb-lang>
</edit>

</html>