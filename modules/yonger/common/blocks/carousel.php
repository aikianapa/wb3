<view>
    <section class="block-{{name}}">
        <div id="my-carousel" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <wb-foreach wb-from="carousel">
                    <wb-var active="active" wb-if='"{{_idx}}" == "0"' else="" />
                    <li class="{{_var.active}}" data-target="#my-carousel" data-slide-to="{{_idx}}"
                        aria-current="location"></li>
                </wb-foreach>
            </ol>
            <div class="carousel-inner">
                <wb-foreach wb-from="carousel">
                    <wb-var active="active" wb-if='"{{_idx}}" == "0"' else="" />
                    <div class="carousel-item {{_var.active}}">
                        <img class="d-block w-100" src="/thumbc/800x400/src/{{image.0.img}}" alt="">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>{{header}}</h5>
                            <p>{{text}}</p>
                            <a class="btn btn-primary" href="{{link}}" wb-if=" '{{button}}' > '' ">
                                {{button}}
                            </a>
                        </div>
                    </div>
                </wb-foreach>
            </div> <a class="carousel-control-prev" href="#my-carousel" data-slide="prev" role="button">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a> <a class="carousel-control-next" href="#my-carousel" data-slide="next" role="button">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </section>
</view>
<edit header="{{_lang.carousel}} BS4">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <div>
        <wb-multilang wb-lang="{{_sett.locales}}" name="lang">
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
                                <textarea class="form-control" name="text" placeholder="{{_lang.text}}"></textarea>
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
        carousel = "Карусель"
        header = Заголовок
        text = Текст
        link = Ссылка
        button = Кнопка
        [en]
        carousel = Carousel
        header = Header
        text = Text
        link = Link
        button = Button
    </wb-lang>
</edit>