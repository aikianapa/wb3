<wb-var base="/engine/modules/yonger/tpl" />

<wb-snippet name="wbapp" />

<wb-scripts src="main.js">
	[
	 "/engine/lib/bootstrap/js/bootstrap.bundle.min.js"
	,"{{_var.base}}/assets/js/dashforge.js"
	,"{{_var.base}}/assets/js/dashforge.aside.js"
	,"{{_var.base}}/assets/lib/js-cookie/js.cookie.js"
    ]
</wb-scripts>

<wb-styles src="main.css">
[
	"/engine/lib/fonts/remixicons/remixicon.css"
	,"/engine/lib/fonts/font-awesome/css/font-awesome.min.css"
	,"{{_var.base}}/assets/css/dashforge.css"
	,"{{_var.base}}/assets/css/dashforge.chat.css"
	,"{{_var.base}}/assets/css/skin.cool.css"
	,"{{_var.base}}/assets/css/yonger.less"
]
</wb-styles>

<script type="wbapp" remove>
	wbapp.loadScripts([
		"{{_var.base}}/assets/js/yonger.js"
	]);
</script>