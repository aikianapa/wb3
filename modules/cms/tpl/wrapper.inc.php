<html lang="en">
	<wb-var base="/engine/modules/cms/tpl/" />
  <head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Twitter -->
    <meta name="twitter:site" content="@webbasic">
    <meta name="twitter:creator" content="@webbasic">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="WebBasic">
    <meta name="twitter:description" content="Web Basic - Pandorum varsion">
    <meta name="twitter:image" content="http://themepixels.me/dashforge/img/dashforge-social.png">

    <!-- Facebook -->
    <meta property="og:url" content="http://digiport.ru">
    <meta property="og:title" content="WebBasic">
    <meta property="og:description" content="Web Basic - Pandorum varsion">

    <meta property="og:image" content="http://themepixels.me/dashforge/img/dashforge-social.png">
    <meta property="og:image:secure_url" content="http://themepixels.me/dashforge/img/dashforge-social.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="600">

    <!-- Meta -->
    <meta name="description" content="Web Basic - Pandorum varsion">
    <meta name="author" content="Oleg Frolov">

		<wb-snippet name="wbapp" />
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{_var.base}}/assets/img/favicon.png">
    <title>Web Basic - Pandemic version</title>

  </head>
  <body class="app-chat">

		<script type="wbapp">
			wbapp.loadStyles([
        "{{_var.base}}/assets/css/dashforge.css"
				,"{{_var.base}}/assets/css/dashforge.chat.css"
				,"{{_var.base}}/assets/css/skin.cool.css"
				,"{{_var.base}}/assets/css/cms.less"
				,"/engine/lib/fonts/remixicons/remixicon.css"
				,"/engine/lib/fonts/font-awesome/css/font-awesome.min.css"
			]);

			wbapp.loadScripts([
				 "/engine/lib/bootstrap/js/bootstrap.bundle.min.js"
				,"{{_var.base}}/assets/lib/perfect-scrollbar/perfect-scrollbar.min.js"
				,"{{_var.base}}/assets/js/dashforge.js"
				,"{{_var.base}}/assets/js/dashforge.aside.js"
				,"{{_var.base}}/assets/lib/js-cookie/js.cookie.js"
			]);

		</script>

  </body>
</html>
