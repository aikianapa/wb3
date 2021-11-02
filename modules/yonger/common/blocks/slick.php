<html>
<view>
    <section class="block-{{name}}">
        <wb-var slick="slick-{{wbNewId()}}"></wb-var>

            <div class="border-bottom">
                <a class="float-right" href="{{link}}">
                    {{button}}
                    <div class="arrow-mask ml-10"></div>
                </a>
                <h3>{{title}}</h3>
            </div>

            <div class="{{_var.slick}} ">
                <wb-foreach wb-from="carousel">
                    <div class="card">
                        <a href="{{link}}">
                            <div class="image__container">
                                <img src="/thumbc/500x320/src{{image.0.img}}" class="card-img-top">
                            </div>
                            <div class="card-body mt-1r">
                                <h4 class="card-title">{{header}}</h4>
                                <p class="card-text">{{text}}</p>
                                <button type="button" class="btn btn-primary"
                                    wb-if="'{{button}}' > ''">{{button}}</button>
                            </div>
                        </a>
                    </div>
                </wb-foreach>
            </div>

        <script wb-app remove>
        wbapp.loadStyles(["/engine/modules/yonger/common/blocks/assets/slick/slick.min.css",
            "/engine/modules/yonger/common/blocks/assets/slick/slick-theme.css"
        ]);
        wbapp.loadScripts(["/engine/modules/yonger/common/blocks/assets/slick/slick.min.js"], "slick-js", function() {
            let options = {
                infinite: true,
                lazyLoad: 'ondemand',
                responsive: [{
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        infinite: true,
                        dots: true
                    }
                }, {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }, {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }],
                dots: true,
                slidesToShow: 4,
                slidesToScroll: 4
            };
            $('.{{_var.slick}}').slick(options);
        });
        </script>
    </section>
</view>
<edit header="{{_lang.slickslider}}">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <div>
        <wb-multilang wb-lang="{{_sett.locales}}" name="lang">
            <div class="divider-text">{{_lang.header}}</div>
            <div class="form-group">
                <input class="form-control" type="text" name="title" placeholder="{{_lang.header}}">
            </div>
            <div class="row">
                <div class="col-lg-6 mb-2">
                    <input class="form-control" type="text" name="button" placeholder="{{_lang.button}}">
                </div>
                <div class="col-lg-6 mb-2">
                    <input class="form-control" type="text" name="link" placeholder="{{_lang.link}}">
                </div>
            </div>

            <div class="divider-text">{{_lang.slides}}</div>

            <wb-multiinput name="carousel">
                <div class="row mx-3">
                    <div class="col-lg-4">
                        <wb-module wb="module=filepicker&mode=single&width=150&height=100" name="image" />
                    </div>
                    <div class="col-lg-8">
                        <div class="row pl-3">
                            <div class="col-12 mb-2">
                                <input class="form-control" type="text" name="header" placeholder="{{_lang.header}}">
                            </div>
                            <div class="col-12 mb-2">
                                <textarea class="form-control" rows="auto" name="text"
                                    placeholder="{{_lang.text}}"></textarea>
                            </div>
                        </div>
                        <div class="row pl-3">
                            <div class="col-lg-6 mb-2">
                                <input class="form-control" type="text" name="button" placeholder="{{_lang.button}}">
                            </div>
                            <div class="col-lg-6 mb-2">
                                <input class="form-control" type="text" name="link" placeholder="{{_lang.link}}">
                            </div>
                        </div>
                    </div>
                </div>
            </wb-multiinput>
        </wb-multilang>
    </div>
    <wb-lang>
        [ru]
        slickslider = "Slick Slider"
        header = Заголовок
        text = Текст
        link = Ссылка
        button = Кнопка
        slides = Слайды
        [en]
        slickslider = "Slick Slider"
        header = Header
        text = Text
        link = Link
        button = Button
        slides = Slides
    </wb-lang>
</edit>

</html>