<html>

<head>
    <script src="/engine/js/wbapp.js"></script>
    <link rel="stylesheet" href="/engine/modules/cms/tpl/assets/css/dashforge.css">
</head>

<body>
    <div class="container" id="myIcons" wb-off>
        <form class="mt-3 row">
            <div class="col-sm-4">
                <input class="form-control" type="search" placeholder="Поиск (минимум 3 символа)" on-keyup="find">
            </div>
        </form>
        <div class="row list d-none">
            {{#each list}}
            <div class='col-2 text-center'>
                <img loading="lazy" src='/module/myicons/{{.}}?size=50&stroke={{~/stroke}}'>
                <br>{{.}}
            </div>
            {{/each}}
        </div>
    </div>
</body>
<script type="wbapp">
var myicons = new Ractive({
    el: '#myIcons',
    template: $('#myIcons').html(),
    data: {
        list: {},
        data: {},
        stroke: '000000'
    },
    on: {
        init() {
            let that = this
            wbapp.post('/module/myicons/getlist',function(data){
                that.set('data', data);
                $('#myIcons .list').removeClass('d-none')
            })
        },
        find(ev) {
            let str = $(ev.node).val() + '';
            if (str.length < 3) {
                myicons.set('list',[])
                return
            }
            let data = myicons.get('data')
            let list = data.filter(element => {
            if (element.includes(str)) {
                return true;
            }
            });
            myicons.set('list',list)
        },
        stroke(ev) {
            let str = $(ev.node).val()
            str = str.replace('#','')
            myicons.set('stroke',str)
        }
    }
})
</script>
</html>