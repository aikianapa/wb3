<view>
    <header>
        <div class="jumbotron mb-0">
            <h1 class="display-4">{{header}}</h1>
            {{text}}
            <wb-jq wb="$dom->find('p:first-child')->addClass('lead')" />
            <a class="btn btn-primary btn-lg" href="{{link}}" role="button" wb-if="'{{link}}'>''">{{button}}</a>
        </div>
    </header>
</view>
<edit header="{{_lang.header}}">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <wb-multilang wb-lang="{{_sett.locales}}" name="lang">
        <div class="form-group">
        <div class="col-12">
            <label class="form-control-label">{{_lang.name}}</label>
            <input type="text" class="form-control" name="header" placeholder="{{_lang.name}}">
        </div>
        </div>
        <div class="form-group">
            <div class="col-12">
                <wb-module wb="{'module':'jodit'}" name="text" />
            </div>
        </div>
        <div class="form-group row">
        <div class="col-md-6">
            <label class="form-control-label">{{_lang.button}}</label>
            <input type="text" class="form-control" name="button" placeholder="{{_lang.button}}">
        </div>
        <div class="col-md-6">
            <label class="form-control-label">{{_lang.link}}</label>
            <input type="text" class="form-control" name="link" placeholder="{{_lang.link}}">
        </div>
        </div>
        <wb-lang>
        [ru]
        header = "Шапка сайта"
        name = "Заголовок"
        button = Кнопка
        link = Ссылка
        [en]
        header = "Site header"
        name = "Header"
        button = Button
        link = Link
        </wb-lang>
    </wb-multilang>
</edit>