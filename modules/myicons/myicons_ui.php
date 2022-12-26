<html>

<head>
    <script src="/engine/js/wbapp.js"></script>
    <link rel="stylesheet" href="/engine/modules/yonger/tpl/assets/css/dashforge.css">
    <link rel="stylesheet" href="/engine/modules/yonger/tpl/assets/css/yonger.less">
</head>

<body>
    <div class="container" id="myIcons" wb-off>
        <form class="mt-3 row" onsubmit="return false;">
            <div class="col-sm-4">
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Поиск..." on-keyup="find">
                    <div class="input-group-append cursor-pointer" on-click="find">
                        <span class="input-group-text py-0">
                            <svg class="mi mi-code-search" size="24" stroke="333333" wb-module="myicons" wb-on></svg>
                        </span>
                    </div>
                </div>
                <small>например: interface</small>
            </div>
            <div class="col-sm-4">
                <button type="button" class="btn btn-secondary" on-click="clear">Сброс</button>
            </div>
        </form>
        <div class="row list d-none scroll-y pb-5" style="height:calc(100vh - 70px);">
            {{#each list}}
            <div class='col-2 text-center' on-click="show">
                {{{svg}}}
                <br>
                <span>{{@key}}</span>
            </div>
            {{/each}}
        </div>
    <div id="myIconsModal" class="modal fade" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{title}}</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                        <b>Имя:</b>
                        <p>{{class}}</p>
                        <b>SVG:</b>
                        <p>{{svg}}</p>
                        <b>IMG:</b>
                        <p>{{img}}</p>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

<script src="/engine/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<script wb-app removed>

var myicons = new Ractive({
    el: '#myIcons',
    template: $('#myIcons').html(),
    data: {
        list: {},
        data: {},
        title: '',
        class: '',
        svg: '',
        img: ''
    },
    on: {
        complete() {
            $('#myIcons input[type=search]').focus()
        },
        show(ev) {
            let mi = $(ev.node).children('span').text()
            myicons.set('title',mi)
            myicons.set('class',mi)
            myicons.set('svg',`<svg class="mi mi-${mi}" size="24" stroke="333333" wb-module="myicons"></svg>`)
            myicons.set('img',`<img src="/module/myicons/24/333333/${mi}.svg" width="24" height="24">`)
            $('#myIconsModal').modal('show');
        },
        find(ev) {
            let str = $('#myIcons input[type=search]').val() + '';
            if (str.length == 0) {
                myicons.set('list',[])
                return
            }
            if (ev.event.key !== "Enter" && ev.event.type !== 'click') return
            $('#myIcons .list').addClass('d-none')
            wbapp.post('/module/myicons/getlist',{find:str},function(data){
                myicons.set('list',data)
                $('#myIcons .list').removeClass('d-none')
                $('#myIcons input[type=search]').focus()
            })
            return false
        },
        clear() {
            window.stop()
            $('#myIcons input[type=search]').val('').focus()
            myicons.set('list',[])
        }
    }
})
</script>
</html>