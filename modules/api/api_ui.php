<html>

<head>
    <link href="/engine/modules/api/jsonTree/jsonTree.css" rel="stylesheet" />
</head>

<body>
    <div id="Api">
        <div id="wrapper">

        </div>

        <div id="pagin">

        </div>
        <template>

        </template>
    </div>
</body>
<script src="/engine/js/jquery.min.js"></script>
<script src="/engine/modules/api/jsonTree/jsonTree.js"></script>
<script>
    var wrapper = document.getElementById("wrapper");

    // Get json-data by javascript-object
    let url = document.location.href
    url = url.replace('/api/v2/listview/', '/api/v2/list/')
    getdata(url)

    function getdata(url) {
        fetch(url)
            .then((response) => response.json())
            .then(function(data) {
                if (data.result !== undefined && data.pagination !== undefined && data.page !== undefined && data.pages !== undefined) {
                    pagination(data);
                    data = data.result;
                }
                $('#wrapper').html('')
                var tree = jsonTree.create(data, wrapper);
                tree.expand(function(node) {
                    return node.childNodes.length < 2 || node.label === 'phoneNumbers';
                });
            });
    }


    function pagination(data) {
        page = data.page
        pages = data.pages
        pagin = data.pagination
        $('#pagin').html('')
        $(pagin).each(function(i, pag) {
            $('#pagin').append(`<a href="" data-page="${pag.page}" class='btn'>${pag.label}</a>`)
        });
    }

    $("#pagin").delegate("a", "click tap", function(ev) {
        ev.preventDefault()
        let regex = /@page=([0-9]{1,999999})&*/gm;
        let url = document.location.href
        let page = $(this).attr("data-page");
        url = url.replace('/api/v2/listview/', '/api/v2/list/')
        url = url.replace(regex, '@page=' + page);
        if (url.indexOf('@page=') == -1) {
            url += "&@page="+page
        }
        console.log(url);
        getdata(url);

    })
</script>

</html>