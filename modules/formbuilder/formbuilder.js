var $ = jQuery.noConflict();
var wbModFormBuilder = function() {
	var $fb = $("#modFormBuilder")
	var $fbm = $("#modFormBuilderModal");
	var lang = wbapp._session.lang;

	var formname = '';
	var formtype = '';
	var filename = '';
	var $focus;
	var $iframe = $('#modFormbuilderView');
	var $editor = $('#modFormbuilderEditor'); // модаль с редактором
	var focused = false;
	var hovered = false;
	var editor = false; // сам редактор
	var alt = '';

	$('aside.aside').addClass('minimize');
	$("#modFormbuilderSnipview").disableSelection();

	var contextMenu = function(ev) {
		let el = ev.currentTarget;
	}

	var focusMenu = function() {
		if (!$focus) {
			$iframe.contents().find('body[constructor]>:first-child').attr('hover',true).trigger('click');
			return;
		}
		let mh = $fb.find('.focus-menu').height();
		let pos = getOffset($focus.get(0));
		$fb.find('.focus-menu')
			.css('left',pos.left - 1)
			.css('top',pos.top - mh - 10 )
			.show();
		$fb.find('.focus-box')
		.css('left',pos.left - 1)
		.css('top',pos.top - 1 )
		.css('width',pos.width)
		.css('height',pos.height)
		.show();
	}

	var setFocus = function(ev) {
		$iframe.contents().find('[focus]').removeAttr('focus').removeAttr('contenteditable');
		$fb.find('.hover-box').hide();
		hovered = false;
		if ($(ev.currentTarget).is('body[constructor]')) {
			$fb.find('.focus-menu').hide();
			$fb.find('.focus-box').hide();
			return;
		}
		$(ev.currentTarget).attr('focus',true).attr('contenteditable',true);
		$focus = $(ev.currentTarget);
		focusMenu();
	}

	function getOffset(el) {
		const rect = el.getBoundingClientRect();
		return {
		  left: rect.left + window.scrollX,
		  top: rect.top + window.scrollY,
		  width: rect.width,
		  height: rect.height
		};
	}

	var appendSnippet = function(snippet){
		$focus.append(snippet);
	}

	var saveState = function() {
		wbapp.post('/module/formbuilder/savestate/',{
			'page': $iframe.contents().find('html').outer()
		});
	}

	var loadState = function() {
		if (this.state !== undefined) return;
		let page = wbapp.storage('cms.mod.formbuilder.page');
		console.log(page);
		if (page !== undefined) {
			let src = wbapp.postSync('/module/formbuilder/savestate/',{'page': page});
			$iframe.attr('src','/module/formbuilder/loadstate/');
		}
	}

	$fb.find('.focus-menu').disableSelection();
	$fb.find('.focus-menu i[data]').off('tap click');
	$fb.find('.focus-menu i[data]').on('tap click',function(){
		var $parent = $focus.parent();
		var partag = $parent.prop("tagName").toLowerCase();
		switch($(this).attr('data')) {
			case 'uplevel':
				if (partag == 'body') return;
				$parent.attr('hover',true).trigger('click');
				break;
			case 'trash':
				let parent = $focus.parent();
				$focus.remove();
				$parent.trigger('click');
				break;
			case 'up':
				if ($focus.prev().length) {
					$focus.prev().before($focus);
					break;
				}

				if ($parent.is(':first-child')) {
					$parent.before($focus);
				} else {
					if (partag == 'body' && $focus.is(':first-child')) return;
					$parent.prev().append($focus);
				}
				break;
			case 'down':
				if ($focus.next().length) {
					$focus.next().after($focus);
					break;
				}

				if ($parent.is(':last-child')) {
					if (partag == 'body') return;
					$parent.after($focus);
				} else {
					$parent.next().prepend($focus);
				}
				break;
			case 'sett': 
				$('#modFormbuilderPanel').toggleClass('show');
				break;
			case 'edit':
				$editor.modal('show');
				let $text = $focus.clone();
				$text.removeAttr('focus').removeAttr('contenteditable');
				editor = $('#modFormbuilderEditor textarea.codemirror').get(0).editor;
				editor.setSize("100%", $('#modFormbuilderEditor .modal-body').height());
				editor.setValue($text.outer());
				editor.focus();
				break;
		}
		focusMenu();
		saveState();
	})

	$editor.children('.modal-dialog').children('.modal-content').children('.modal-header').find('.btn-primary').off('tap click');
	$editor.children('.modal-dialog').children('.modal-content').children('.modal-header').find('.btn-primary').on('tap click',function(){
		let $text = $(editor.getValue());
		$text.attr('hover',true).attr('contenteditable',true);
		$focus.replaceWith($text.outer());
		$iframe.contents().find('[hover][contenteditable]').trigger('click');
		saveState();
	});

	$('#modFormbuilderFormType [data-toggle="popover"]').each(function () {
		let el = this;
		let url = $(el).attr('data-url');
		$(this).popover({
			//Установление направления отображения popover
			placement: 'right'
			, trigger: 'hover'
			, title: $(this).text()
			, html: true
			, sanitize: false
			//			,container: '#modFormbuilderSnipview'
			, content: '<iframe frameborder="0" width="200" src="' + url + '"></iframe>'
		}).on('shown.bs.popover',function(){
			let popover = '#'+$(this).attr('aria-describedby');
			this.snippet = $(popover).find('iframe').contents().find('body').html();
		});

	})

	$iframe.ready(function(){
		setTimeout(function(){

			
			$iframe.contents().find('body').get(0).addEventListener("keydown", (event) => {
				if (event.key == 'Alt') {
					alt = 'preview';
					$iframe.contents().find('body').attr('preview', true);
					$fb.attr('preview', true);
				}
			});
			$iframe.contents().find('body').get(0).addEventListener("keyup", (event) => {
				if (event.key == 'Alt') {
					$iframe.contents().find('body').removeAttr('preview');
					$fb.removeAttr('preview');
				}
			});

			$fb.get(0).addEventListener("keydown", (event) => {
				if (event.key == 'Alt' && $('#modFormbuilderEditor').is(':visible')) {
					alt = 'editor';
					$('#modFormbuilderEditor').modal('hide');
					$iframe.contents().find('body').attr('preview', true);
					$fb.attr('preview', true);
				} else if (event.code == "KeyS" && $('#modFormbuilderEditor').is(':visible')) {
					$editor.children('.modal-dialog').children('.modal-content').children('.modal-header').find('.btn-primary').trigger('click');
					event.preventDefault();
					return false;
				}
			});

			document.addEventListener("keyup", (event) => {
				if (event.key == 'Alt') {
					if (alt == 'editor') {
						alt = '';
						$iframe.contents().find('body').removeAttr('preview');
						$fb.removeAttr('preview');
						$('#modFormbuilderEditor').modal('show');
						editor.focus();
					}
				}
			});


			$iframe.contents().find('body').undelegate('*','mouseenter');
			$iframe.contents().find('body').delegate('*','mouseenter',function(ev){
				if (hovered == false) {
					hovered = true;
					$iframe.contents().find('[hover]').removeAttr('hover');
					$fb.find('.hover-box').hide();
					if ($(ev.currentTarget).is('[focus]')) return;
					let pos = getOffset($(ev.currentTarget).get(0));
					$(ev.currentTarget).attr('hover',true);
					$fb.find('.hover-box')
					.css('left',pos.left - 1)
					.css('top',pos.top - 1 )
					.css('width',pos.width)
					.css('height',pos.height)
					.show();
					setTimeout(()=>{hovered = false},100);
				}
			});
		
			$iframe.contents().find('body').undelegate('*','mouseleave');
			$iframe.contents().find('body').delegate('*','mouseleave',function(ev){
				$(ev.currentTarget).removeAttr('hover');
				$fb.find('.hover-box').hide();
				hovered = false;
			});
		
			setInterval(()=>{focusMenu();},30);


			$iframe.contents().find('body').undelegate('[hover]','tap click');
			$iframe.contents().find('body').delegate('[hover]','tap click',function(ev){
				setFocus(ev);
				if ($(ev.currentTarget).is('[type=submit]')) return false;
			});

			$iframe.contents().find('body').undelegate(':not([hover])','tap click');
			$iframe.contents().find('body').delegate(':not([hover])','tap click',function(ev){
				if ($(ev.currentTarget).is('[type=submit]')) return false;
			});


			$iframe.contents().find('body[constructor]').undelegate('*','contextmenu');
			$iframe.contents().find('body[constructor]').delegate('*','contextmenu',function(ev){
				contextMenu(ev);
				return false;
			});

			$('#modFormbuilderFormType .list-group-item').off('tap click');
			$('#modFormbuilderFormType .list-group-item').on('tap click',function(){
				if (!$iframe.length) return;
				if ($focus.is('.row') && $(this.snippet).is('.row')) {
					this.snippet = $(this.snippet).html();
				}

				if ($iframe.contents().find('[focus]').length) {
					$iframe.contents().find('[focus]').append($(this.snippet));
				} else {
					$(this.snippet).attr('focus',true);
					$iframe.contents().find('[constructor]').append($(this.snippet));
				}
				saveState()
			})
			$fb.find('.hover-box').hide();
			$iframe.contents().find('[focus]').trigger('click');
			focusMenu();
		},300)
	});

	$(window).resize(function(){
		$fb.find('.chat-content').height($(window).height() - $('.content-header').height() - 10);
		$iframe.height($('.chat-content').height());
		focusMenu();
	});

	$iframe.contents().off('scroll');
	$iframe.contents().on('scroll',function(){
		focusMenu();
	});

	$(window).trigger('resize');
}

$(document).one('formbuilder-js', function(){
	wbModFormBuilder();
});

