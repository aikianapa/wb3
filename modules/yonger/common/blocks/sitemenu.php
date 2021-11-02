<html>
<view>
    <section class="block-{{name}}">
        <nav class="navbar navbar-expand-lg navbar-light" id="{{id}}">
            <a class="navbar-brand" href="/">
                <img data-src="/thumb/100x50/src/{{_sett.logo.0.img}}" class="img-fluid"
                    wb-if="'{{_sett.logo.0.img}}'>''">
                <svg class="mi mi-home-circle size-50 img-fluid" stroke="000047" wb="module=myicons"
                    wb-if="'{{_sett.logo.0.img}}' == ''"></svg>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarScroll"
                aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarScroll">

                <ul class="navbar-nav mr-auto my-2 my-lg-0 navbar-nav-scroll" wb-tree="call=siteMenu">
                    <li class="nav-item level-{{_lvl}} {{divider}}">
                        <a class="nav-link" href="{{path}}">{{header.{{_lang}}}}</a>
                    </li>
                </ul>
                <wb-jq wb="$dom->find('#{{id}} .navbar-nav li ul')->addClass('dropdown-menu')
      ->parent('li')->addClass('dropdown')
      ->children('.nav-link')
      ->addClass('dropdown-toggle')
      ->attr('data-toggle','dropdown');
      $dom->find('#{{id}} .navbar-nav li.level-1')->addClass('dropdown-submenu')->children('.nav-link')->removeAttr('data-toggle');
      $dom->find('#{{id}} .navbar-nav li.divider-after')->after('<div />')->next()->addClass('dropdown-divider');
      $dom->find('#{{id}} .navbar-nav li:not(.dropdown) .nav-link')->removeAttr('data-toggle');
      " />
                <script type="wbapp">
                $('#{{id}} .dropdown-submenu a.dropdown-toggle').on("click", function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).next('ul').toggleClass('show');
                });
                </script>
                <style>
                .dropdown-submenu {
                    position: relative;
                }

                .dropdown-submenu .dropdown-menu {
                    top: 0;
                    left: 100%;
                    margin-top: -1px;
                }
                </style>
            </div>
        </nav>
    </section>
</view>
<edit header="{{_lang.header}}">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <wb-lang>
    [ru]
    header = Меню сайта
    [en]
    header = Site menu
</wb-lang>
</edit>

</html>