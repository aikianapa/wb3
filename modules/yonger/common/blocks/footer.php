<view>
    <footer class="bd-footer text-muted">
        <div class="container-fluid p-3 p-md-5">
            <ul class="bd-footer-links">
                <li>
                    <noindex><a rel="nofollow noopener" target="_blank"
                            href="https://github.com/twbs/bootstrap">GitHub</a></noindex>
                </li>
                <li>
                    <noindex><a rel="nofollow noopener" target="_blank"
                            href="https://twitter.com/getbootstrap">Twitter</a></noindex>
                </li>
            </ul>
            <p>Разработано и построено со всей любовью к миру <a href="/docs/4.5/about/team/">командой Bootstrap</a> с
                помощью <noindex><a href="https://github.com/twbs/bootstrap/graphs/contributors" rel="nofollow noopener"
                        target="_blank">наших участников</a></noindex>.</p>
            <p>Актуальная версия vv5.0.0-beta3. Код распространяется по лицензии <noindex><a
                        href="https://github.com/twbs/bootstrap/blob/master/LICENSE" rel="nofollow noopener license"
                        target="_blank">MIT</a></noindex>, документация распространяется по лицензии <noindex><a
                        href="https://creativecommons.org/licenses/by/3.0/" rel="nofollow noopener license"
                        target="_blank">CC BY 3.0</a></noindex>.</p>
            <p>Свои предложения по развитию сайта и переводу присылайте на почту info@bootstrap-4.ru</p>
        </div>
    </footer>
</view>
<edit header="{{_lang.header}}">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <wb-multilang wb-lang="{{_sett.locales}}" name="lang">
        <div class="col-sm-6">
            <label class="form-control-label">{{_lang.name}}</label>
            <input type="text" class="form-control" name="header" placeholder="{{_lang.name}}">
        </div>
        <div class="form-group row">
            <div class="col-12">
                <wb-module wb="{'module':'jodit'}" name="text" />
            </div>
        </div>
    </wb-multilang>
    <wb-lang>
        [ru]
        header = Подвал сайта
        name = "Наименование блока"
        active = "Отображать блок"
        template = Шаблон
        [en]
        header = Site footer
        name = "Block name"
        active = "Show block"
        template = Template

    </wb-lang>
</edit>