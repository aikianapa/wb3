		<wb-var base="/engine/modules/yonger/tpl" />
		<script type="wbapp" remove>
		    wbapp.loadStyles([
				 "/engine/lib/fonts/remixicons/remixicon.css"
        		,"/engine/lib/fonts/font-awesome/css/font-awesome.min.css"
        		,"{{_var.base}}/assets/css/dashforge.css"
				,"{{_var.base}}/assets/css/dashforge.chat.css"
				,"{{_var.base}}/assets/css/skin.cool.css"
        		,"{{_var.base}}/assets/css/yonger.less"
			]);

			wbapp.loadScripts([
				 "/engine/lib/bootstrap/js/bootstrap.bundle.min.js"
				,"{{_var.base}}/assets/lib/perfect-scrollbar/perfect-scrollbar.min.js"
				,"{{_var.base}}/assets/js/dashforge.js"
				,"{{_var.base}}/assets/js/dashforge.aside.js"
				,"{{_var.base}}/assets/lib/js-cookie/js.cookie.js"
				,"{{_var.base}}/assets/js/yonger.js"
			]);
		</script>