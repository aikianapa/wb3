<html>

<head>
    <link href="/engine/modules/api/jsonTree/jsonTree.css" rel="stylesheet" />
</head>

<body>
    <div id="wrapper">

    </div>
</body>
<script src="/engine/modules/api/jsonTree/jsonTree.js"></script>
<script>
    var wrapper = document.getElementById("wrapper");

    // Get json-data by javascript-object
    let url = document.location.href
    url = url.replace('/api/v2/listview/', '/api/v2/list/')
    fetch(url)
        .then((response) => response.json())
        .then(function(data) {
            var tree = jsonTree.create(data, wrapper);
            tree.expand(function(node) {
                return node.childNodes.length < 2 || node.label === 'phoneNumbers';
            });
        });
</script>

</html>