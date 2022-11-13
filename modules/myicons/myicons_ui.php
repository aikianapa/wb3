<html>

<head>
    <script src="/engine/js/wbapp.js"></script>
    <link rel="stylesheet" href="/engine/modules/cms/tpl/assets/css/dashforge.css">
    <script src="/engine/modules/myicons/clipboard.min.js"></script>
</head>

<body>
    <div class="container" id="myIcons" wb-off>
        <form class="mt-3 row" onsubmit="return false;">
            <div class="col-sm-4">
                <input class="form-control" type="search" placeholder="Поиск (минимум 3 символа)" on-keyup="find">
            </div>
            <div class="col-sm-4">
                <button type="button" class="btn btn-secondary" on-click="clear">Сброс</button>
            </div>
        </form>
        <div class="row list d-none">
            {{#each list}}
            <div class='col-2 text-center'>
                {{{svg}}}
                <textarea id="id_{{@key}}" class="d-none"><svg class="mi mi-{{@key}}" size="24" stroke="333333" wb-module="myicons"></svg></textarea>
                <br>
                <span>{{@key}}</span>
            </div>
            {{/each}}
        </div>
    </div>
</body>
<script type="wbapp">
                var clipboard = new ClipboardJS('.mi');
                clipboard.on('success', function(e) {
                    //console.info('Action:', e.action);
                    console.info(e.text);
                    let mi = $(e.trigger).parent().children('span').text()
                    console.log(`<img src="/module/myicons/24/333333/${mi}.svg" width="24" height="24">`)
                    //console.info('Trigger:', e.trigger);
                    e.clearSelection();
                });

var myicons = new Ractive({
    el: '#myIcons',
    template: $('#myIcons').html(),
    data: {
        list: {},
        data: {}
    },
    on: {
        complete() {
            $('#myIcons input[type=search]').focus()
        },
        find(ev) {
            let str = $(ev.node).val() + '';
            if (str.length < 3) {
                myicons.set('list',[])
                return
            }
            if (ev.event.key !== "Enter") return
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