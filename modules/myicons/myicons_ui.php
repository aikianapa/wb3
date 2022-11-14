<html>

<head>
    <script src="/engine/js/wbapp.js"></script>
    <link rel="stylesheet" href="/engine/modules/yonger/tpl/assets/css/dashforge.css">
    <link rel="stylesheet" href="/engine/modules/yonger/tpl/assets/css/yonger.less">
    <script src="/engine/modules/myicons/clipboard.min.js"></script>
</head>

<body>
        <input wb-module="myicons" wb-on>
    <div class="container scroll-y" id="myIcons" wb-off>
        <form class="mt-3 row" onsubmit="return false;">
            <div class="col-sm-4">

                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Поиск (минимум 3 символа)" on-keyup="find">
                    <div class="input-group-append cursor-pointer" on-click="find">
                        <span class="input-group-text py-0">
                            <svg class="mi mi-code-search" size="24" stroke="333333" wb-module="myicons" wb-on></svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <button type="button" class="btn btn-secondary" on-click="clear">Сброс</button>
            </div>
        </form>
        <div class="row list d-none">
            {{#each list}}
            <div class='col-2 text-center'>
                {{{svg}}}
                <textarea data-id="{{@key}}" class="d-none"><svg class="mi mi-{{@key}}" size="24" stroke="333333" wb-module="myicons"></svg></textarea>
                <br>
                <span>{{@key}}</span>
            </div>
            {{/each}}
        </div>
    </div>
</body>
<script>
</script>
<script type="wbapp">

var myicons = new Ractive({
    el: '#myIcons',
    template: $('#myIcons').html(),
    data: {
        list: {},
        data: {}
    },
    on: {
        init() {
            let clipboard = new ClipboardJS('.mi');
            clipboard.on('success', function(e) {
                //console.info('Action:', e.action);
                console.info(e.text);
                let mi = $(e.trigger).parent().children('span').text()
                console.log(`<img src="/module/myicons/24/333333/${mi}.svg" width="24" height="24">`)
                //console.info('Trigger:', e.trigger);
                e.clearSelection();
            });
        },
        complete() {
            $('#myIcons input[type=search]').focus()
        },
        find(ev) {
            let str = $('#myIcons input[type=search]').val() + '';
            if (str.length < 3) {
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