
function ws_display(id, value)
{
	document.getElementById(id).style.display = value;
}

function toggle_display(id, display_type)
{
	var obj = document.getElementById(id);
	if ( obj.style.display == display_type ) obj.style.display='none';
	else obj.style.display=display_type;
}

function toggle_display_obj(obj)
{
	if ( obj.style.display == '' ) {
		obj.style.display='none';
		return "hide";
	}
	else {
		obj.style.display='';
		return "show";
	}
}

function turn_display(arg, display_type, turn_on)
{
	if (typeof(arg) == 'object') var obj=arg;
	else var obj = document.getElementById(arg);
	if ( turn_on ) obj.style.display=display_type;
	else obj.style.display='none';
}

function turn_disabled(arg,turn_on)
{
	if (typeof(arg) == 'object') var obj=arg;
	else var obj = document.getElementById(arg);
	if ( turn_on ) obj.disabled=false;
	else obj.disabled=true;
}

function WSCFormError(elem, hint, error)
{
	if ( hint ) alert(error + '\n\nПодсказка: ' + hint);
	else alert(error);
	elem.focus();
	return false;
}

// depricated
function ShowError(elem, hint, error)
{
	return WSCFormError(elem, hint, error);
}

function on_post(button, action)
{
	button.form.target = '_self';
	button.form.act.value = action;
}

function on_preview(button, frame_name)
{
	on_preview_ex(button, frame_name, 'preview');
}

function on_preview_ex(button, frame_name, action)
{
	var list = document.getElementsByName(frame_name);
	if ( list.length != 1 )
	{
		// don't worry, just
		button.form.target = '_self';
	}
	else
	{
		// it's ok, preview in frame
		var frame = list.item(0);
   		frame.style.display = 'block';
		button.form.target = frame.name;
		button.form.act.value = action;
	}
}

/** вернуть текст выделенный на странице */
function bbcode_get_selection()
{
	if ( window.getSelection )
	{
		return window.getSelection().toString();
	}
	else if ( document.getSelection )
	{
		return document.getSelection();
	}
	else if ( document.selection )
	{
		return document.selection.createRange().text;
	}
	return '';
}

var selection = '';

function bbcode_catch_selection()
{
	selection = bbcode_get_selection();
}

function bbcode_replace_selection(textarea, text)
{
	var start = textarea.selectionStart;
	var end = textarea.selectionEnd;
	var len = textarea.value.length;
	var scrollTop = textarea.scrollTop;
	textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(end, len);
	textarea.focus();
	textarea.selectionStart = start;
	textarea.selectionEnd = end + text.length;
	textarea.scrollTop = scrollTop;
}

/** вставить текст после выделения */
function bbcode_insert_after(textarea, text)
{
	var start = textarea.selectionStart;
	var end = textarea.selectionEnd;
	var len = textarea.value.length;
	var scrollTop = textarea.scrollTop;
	textarea.value = textarea.value.substring(0, end) + text + textarea.value.substring(end, len);
	textarea.focus();
	textarea.selectionStart = end;
	textarea.selectionEnd = end + text.length;
	textarea.scrollTop = scrollTop;
}

/** вставить тег */
function bbcode_insert_tag(txt, tag_open, tag_close)
{
	var textarea = document.getElementById(txt);
	if ( document.selection )
	{ // IE
		var range = document.selection.createRange();
		range.text = tag_open + range.text + tag_close;
		textarea.focus();
		return false;
	}
	var start = textarea.selectionStart;
	var end = textarea.selectionEnd;
	var inner = textarea.value.substring(start, end);
	var tag = tag_open + inner + tag_close;
	var len = textarea.value.length;
	var scrollTop = textarea.scrollTop;
	textarea.value = textarea.value.substring(0, start) + tag + textarea.value.substring(end, len);
	textarea.focus();
	textarea.selectionStart = start;
	textarea.selectionEnd = end + tag_open.length + tag_close.length;
	textarea.scrollTop = scrollTop;
	return false;
}

/** вставить цитату пользователя */
function bbcode_quote(txt, author)
{
	if ( selection == '' )
	{
		alert('Выделите текст');
		return;
	}
	var code = '[quote="' + author + '"]' + selection + '[/quote]';
	bbcode_insert_after(document.getElementById(txt), code);
}

function bbedit_on_preview(id)
{
	var text = document.getElementById(id).value;
	if ( ajax_post_frame(id + '_preview', ajax_prefix + 'ajax/bbcode-preview.php', {text: text}) )
	{
		ws_display(id + '_preview', 'block');
		ws_display(id + '_area', 'none');
		ws_display(id + '_preview_btn', 'none');
		ws_display(id + '_edit_btn', 'inline');
		return true;
	}
	else
	{
		return false;
	}
}

function bbedit_on_edit(id)
{
	frame_clear(id + '_preview');
	ws_display(id + '_preview', 'none');
	ws_display(id + '_area', 'block');
	ws_display(id + '_preview_btn', 'inline');
	ws_display(id + '_edit_btn', 'none');
}

var ctrl = false;

function bbedit_onkeydown(editorID, event)
{
/*
	var editor = document.getElementById(editorID);
	if ( event.which == 17 ) ctrl = true;
	if ( ctrl )
	{
		switch (event.which)
		{
		case 13:
			//alert(event.which);
			editor.form.submit();
			if ( event.preventDefault ) event.preventDefault();
			return false;
//		case 80:
//			bbedit_on_preview(editorID);
//			event.preventDefault();
//			return false;
		}
	}
*/
	return true;
}

function bbedit_onkeyup(editorID, event)
{
//	if ( event.which == 17 ) ctrl = false;
}

function ajax_request()
{
	try { return new XMLHttpRequest(); } catch (e) {}
	try { return new ActiveXObject('Msxml2.XMLHTTP'); } catch (e) {}
	try { return new ActiveXObject('Microsoft.XMLHTTP'); } catch (e) {}
	return false;
}

function sync_get_xml(url)
{
	var request = ajax_request();
	if ( request === false ) return false;
	request.open('GET', url, false);
	request.send(null);
	return request.responseXML;
}

function sync_get_text(url)
{
	var request = ajax_request();
	if ( request === false ) return false;
	request.open('GET', url, false);
	request.send(null);
	return request.responseText;
}

function async_raw_get(url, onload, data)
{
	var request = ajax_request();
	if ( request === false ) return false;
	request.open('GET', url, true);
	request.onreadystatechange = function() {
		if(request.readyState == 4 || request.readyState == 'complete' ) {
			if(request.status == 200) onload(request, data);
			if(request.onreadystatechanage) request.onreadystatechanage = null;
		}
	}
	request.send(null);
	return true;
}

function async_raw_post(url, content, onload, data)
{
	var request = ajax_request();
	if ( request === false ) return false;
	request.open("POST", url, true);
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	request.onreadystatechange = function() {
		if ( request.readyState == 4 ) {
			onload(request, data);
		}
	}
	request.send(content);
	return true;
}

function on_load_frame(request, frame)
{
	frame.innerHTML = request.responseText;
}

function ajax_frame(id, url)
{
	var frame = document.getElementById(id);
	var result = async_raw_get(url, on_load_frame, frame);
	if ( result === false ) return false;
// 	frame.innerHTML = '<div class="ajax_loading"></div>';
	return true;
}

function ajax_post_frame(id, url, args)
{
	var frame = document.getElementById(id);
	var params = new Array();
	for(var arg in args)
	{
		try
		{
			var val = args[arg];
			params[params.length] = encodeURIComponent(arg) + '=' + encodeURIComponent(val);
		} catch (e) { }
	}
	var result = async_raw_post(url, params.join('&'), on_load_frame, frame);
	if ( result === false ) return false;
	frame.innerHTML = '<div class="ajax_loading">Loading</div>';
	return true;
}

function post(frameID, action, args)
{
	var frame = document.getElementById(frameID);
	var query = 'ajax=on&action=' + encodeURIComponent(action);
	for(var name in args) {
		try {
			query += '&' + encodeURIComponent(name) + '=' + encodeURIComponent(args[name]);
		} catch (e) { }
	}
	var request = ajax_request();
	if ( request === false ) return false;
	request.open("POST", ajax_prefix + 'admin/switch/checksw.php', true);
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	request.onreadystatechange = function() {
		if ( request.readyState == 4 ) {
			frame.innerHTML = request.responseText;
			request.onreadystatechanage = null;
		}
	}
	request.send(query);
	frame.innerHTML = '<div class="ajax_loading"></div>';
	return true;
}

function frame_clear(id)
{
	document.getElementById(id).innerHTML = '';
}

function goback(obj)
{
	if ( history.length > 0 )
	{
		history.back();
		return true;
	}
	return false;
}

function search_same_report(obj, prj_name)
{
	if ( obj.wsp_old_value !== obj.value )
		if ( post('same_reports', 'project.reports.search_same', {summary: obj.value, prj: prj_name}) )
			{ obj.wsp_old_value = obj.value; return true; }
	return false;
}

// вернуть размеры видимой области окна
function getScreenSize() {
	return {
	w: (window.innerWidth ? window.innerWidth : (document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.offsetWidth)),
	h: (window.innerHeight ? window.innerHeight : (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.offsetHeight))
	};
}

function getElementSize(elem) {
	if ( elem.getBoundingClientRect ) return {
		w: elem.getBoundingClientRect().right - elem.getBoundingClientRect().left - 1,
		h: elem.getBoundingClientRect().bottom - elem.getBoundingClientRect().top - 1
		}
	else return {
		w: elem.offsetWidth,
		h: elem.offsetHeight
	}
}

function ws_get_scroll() {
	var scrollX, scrollY;
	if(typeof window.pageYOffset == 'number') {
		scrollX = window.pageXOffset;
		scrollY = window.pageYOffset;
	} else if(document.body && (document.body.scrollTop || document.body.scrollLeft)) {
		scrollX = document.body.scrollLeft;
		scrollY = document.body.scrollTop;
	} else if(document.documentElement && (document.documentElement.scrollTop || document.documentElement.scrollLeft)) {
		scrollY = document.documentElement.scrollTop;
		scrollX = document.documentElement.scrollLeft;
	} else {
		scrollX = 0;
		scrollY = 0;
	}
	return {X: scrollX, Y: scrollY};
}

// создать тег
function mktag(tagName, props, styles) {
	var obj = document.createElement(tagName);
	if ( props ) {
		for (var Name in props) {
			obj.setAttribute(Name, props[Name]) || eval('obj.'+Name+'=props[Name];');
		}
	}
	if ( styles ) {
		for (var Name in styles) {
			try { obj.style[Name] = styles[Name] }
			catch (e) { if ( obj.currentStyle ) obj.currentStyle[Name] = styles[Name]; }
		}
	}
	return obj;
}

function jframe_encode_form(form)
{
	var params = new Array();
	for(var i = 0; i < form.elements.length; i++)
	{
		try
		{
			var val = form.elements[i].value;
			var arg = form.elements[i].name;
			params[params.length] = encodeURIComponent(arg) + '=' + encodeURIComponent(val);
		} catch (e) { }
	}
	return params.join('&');
}

function jframe_decode_form(form, data)
{
	var params = data.split('&');
	for(var i = 0; i < params.length; i++)
	{
		var param = params[i].split('=');
		try {
			var name = decodeURIComponent(param[0]);
			var value = decodeURIComponent(param[1]);
			form.elements[name].value = value;
		} catch (e) {}
	}
}

function jframe_encode_checks(form)
{
	var params = new Array();
	for(var i = 0; i < form.elements.length; i++)
	{
		try
		{
			var val = form.elements[i].value;
			var chk = form.elements[i].checked ? 1 : 0;
			params[params.length] = encodeURIComponent(arg) + '=' + encodeURIComponent(chk);
		} catch (e) { }
	}
	return params.join('&');
}

function jframe_decode_checks(form, data)
{
	var params = data.split('&');
	for(var i = 0; i < params.length; i++)
	{
		var param = params[i].split('=');
		try {
			var name = decodeURIComponent(param[0]);
			var chk = decodeURIComponent(param[1]) ? true : false;
			form.elements[name].checked = chk;
		} catch (e) {}
	}
}

// сериализовать HTML-тег
// (innerHTML не поля форм, а нам нужно чтобы сохранялись)
function jframe_serialize(tag)
{
	var forms = tag.getElementsByTagName('form');
	for(var i = 0; i < forms.length; i++) {
		var form = jframe_encode_form(forms[i]);
		forms[i].setAttribute('jframe-values', form);
	}
	return tag.innerHTML;
}

// десериализовать HTML-тег
// (innerHTML не поля форм, а нам нужно чтобы сохранялись)
function jframe_unserialize(tag, data)
{
	tag.innerHTML = data;
	var forms = tag.getElementsByTagName('form');
	for(var i = 0; i < forms.length; i++) {
		jframe_decode_form(forms[i], forms[i].getAttribute('jframe-values'));
	}
}

/**
* Регулярное выражения для парсинга URL-ов
*/
var jframe_url_regexp = /^(https?):\/\/([^\/]+)(:(\d+))?(\/[^\?]*)?(\?[^#]*)?(#.*)?$/;

// создать js-фрейм
function jframe_create()
{
	var frame = {left: 0, top: 0, onclose: false};
	frame.root = mktag('table', {className: 'jframe'}, {position: 'absolute', left: '0px', top: '0px'});
	frame.ws = frame;
	frame.root.ws = frame;
	frame.history = new Array ();
	frame.forward = new Array ();
	frame.isPosted = false;
	frame.markLoading = function () {
		var div = mktag('div', {className: 'ajax_loading'});
		div.style.width = '20em';
		div.style.height = '10em';
		frame.body.innerHTML = '';
		frame.body.appendChild(div);
	};
	frame.prepare = function () {
		var links = frame.body.getElementsByTagName('a');
		for(var i = 0; i < links.length; i++) {
			var m = jframe_url_regexp.exec(links[i].href);
			if ( m && m[2] === document.domain ) switch (links[i].getAttribute('jframe-target')) {
			case '_blank':
				links[i].onclick = function (e) { return jframe_open(this, jframe_setmode(this.href), e); };
				break;
			default:
				links[i].onclick = prevented(frame.navigate, links[i]);
			}
			else { links[i].target = '_blank'; }
		}
		
		var forms = frame.body.getElementsByTagName('form');
		for(var i = 0; i < forms.length; i++) {
			var m = jframe_url_regexp.exec(forms[i].action);
			if ( ! forms[i].action.match(/^https?:\/\//) || m && m[2] === document.domain ) switch (forms[i].getAttribute('jframe-target')) {
			case '_blank': break;
			default:
				forms[i].onsubmit = prevented(frame.post, forms[i]);
			}
		}
		
	};
	frame.serialize = function () {
		var forms = frame.body.getElementsByTagName('form');
		for(var i = 0; i < forms.length; i++) {
			var form = forms[i];
			form.setAttribute('jframe-values', jframe_encode_form(form));
			form.setAttribute('jframe-checks', jframe_encode_checks(form));
		}
		return {
		link: frame.altLink,
		title: frame.title.innerHTML,
		isPosted: frame.isPosted,
		body: frame.body.innerHTML
		};
	};
	frame.unserialize = function (page) {
		frame.altLink = page.link;
		frame.title.innerHTML = page.title;
		frame.isPosted = page.isPosted;
		frame.body.innerHTML = page.body;
		var forms = frame.body.getElementsByTagName('form');
		for(var i = 0; i < forms.length; i++) {
			var form = forms[i];
			jframe_decode_form(form, form.getAttribute('jframe-values'));
			jframe_decode_checks(form, form.getAttribute('jframe-checks'));
		}
		frame.prepare();
		frame.refresh.link.href = page.link;
		if ( frame.history.length == 0 )
		{
			frame.prev.link.href = '#';
			frame.prev.img.src = url('img/jframe/prev-grey.png');
		}
		if ( frame.forward.length == 0 )
		{
			frame.next.link.href = '#';
			frame.next.img.src = url('img/jframe/next-grey.png');
		}
	};
	frame.onload = function (request) {
		frame.body.innerHTML = request.responseText;
		var h = frame.body.getElementsByTagName('h1');
		if ( h.length > 0 ) {
			frame.title.innerHTML = h[0].innerHTML;
			h[0].style.display = 'none';
		}
		frame.prepare();
	};
	frame.onFirstLoad = function (request) {
		frame.onload(request);
		jframe_centerize(frame);
	};
	frame.navigate = function (tag) {
		frame.history.push(frame.serialize());
		frame.forward = new Array ();
		frame.next.img.src = url('img/jframe/next-grey.png');
		frame.next.link.href = '#';
		frame.prev.img.src = url('img/jframe/prev.png');
		frame.prev.link.href = frame.altLink;
		frame.title.innerHTML = tag.innerHTML;
		frame.altLink = tag.href;
		frame.refresh.link.href = tag.href;
		frame.isPosted = false;
		frame.markLoading();
		if ( ! async_raw_get(jframe_setmode(tag.href), frame.onload, frame) )
		{
			window.location.href = tag.href;
			return false;
		}
		return true;
	};
	frame.goBack = function () {
		if ( frame.history.length == 0 ) return true;
		frame.forward.push(frame.serialize());
		frame.next.img.src = url('img/jframe/next.png');
		frame.next.link.href = frame.altLink;
		frame.unserialize( frame.history.pop() );
		return true;
	};
	frame.goNext = function () {
		if ( frame.forward.length == 0 ) return true;
		frame.history.push(frame.serialize());
		frame.prev.img.src = url('img/jframe/prev.png');
		frame.prev.link.href = frame.altLink;
		frame.unserialize( frame.forward.pop() );
		return true;
	};
	frame.refresh = function () {
		if ( frame.isPosted ) {
			if ( ! confirm('Отправить форму повторно?') ) return true;
		}
		frame.markLoading();
		if ( frame.isPosted ) {
			if ( ! async_raw_post(frame.postAction, frame.postData, frame.onload, frame) )
			{
				// TODO something
				return false;
			}
			return true;
		}
		if ( ! async_raw_get(jframe_setmode(frame.altLink), frame.onload, frame) )
		{
			window.location.href = tag.href;
			return false;
		}
		return true;
	};
	frame.post = function (form) {
		frame.history.push(frame.serialize());
		frame.forward = new Array ();
		frame.next.img.src = url('img/jframe/next-grey.png');
		frame.next.link.href = '#';
		frame.prev.img.src = url('img/jframe/prev.png');
		frame.prev.link.href = frame.altLink;
		frame.altLink = form.action;
		frame.isPosted = true;
		frame.refresh.link.href = form.href;
		frame.postData = jframe_encode_form(form);
		frame.postAction = jframe_setmode(form.action)
		frame.markLoading();
		if ( ! async_raw_post(frame.postAction, frame.postData, frame.onload, frame) )
		{
			// TODO something
			return false;
		}
		return true;
	};
	frame.close = function () {
		if ( frame.onclose ) frame.onclose(frame);
		document.body.removeChild(frame.root);
		return true;
	};
	
	var tbody = mktag('tbody');
	var tr = mktag('tr');
	var td = mktag('td', {}, {border: '0px none'});
	var border = mktag('div', {className: 'jframe_border'});
	
	var caption = mktag('div', {className: 'jframe_caption'}, {cursor: 'default'});
	caption.onmousedown = function (e) { return prevent(ws_start_drag(frame.root, e), e); };
	caption.appendChild(mktag('img', {src: url('img/jframe/close.png'), alt: 'X', title: 'Закрыть', align: 'right', width: 16, height: 16, onclick: prevented(frame.close)}, {marginLeft: '8px'}));
	
	frame.prev = {
		img: mktag('img', {src: url('img/jframe/prev-grey.png'), alt: '<', title: 'Назад', align: 'right', width: 16, height: 16}, {marginLeft: '5px'}),
		link: mktag('a', {href: '#', onclick: prevented(frame.goBack)})
	};
	frame.prev.link.appendChild(frame.prev.img);
	
	frame.next = {
		img: mktag('img', {src: url('img/jframe/next-grey.png'), alt: '>', title: 'Вперёд', align: 'right', width: 16, height: 16}, {marginLeft: '5px'}),
		link: mktag('a', {href: '#', onclick: prevented(frame.goNext)})
	};
	frame.next.link.appendChild(frame.next.img);
	
	frame.refresh = {
		img: mktag('img', {src: url('img/jframe/refresh.png'), alt: 'R', title: 'Обновить', align: 'right', width: 16, height: 16}, {marginLeft: '8px'}),
		link: mktag('a', {href: '#', onclick: prevented(frame.refresh)})
	};
	frame.refresh.link.appendChild(frame.refresh.img);
	
	caption.appendChild(frame.refresh.link);
	caption.appendChild(frame.next.link);
	caption.appendChild(frame.prev.link);
	
	frame.title = mktag('span');
	frame.title.appendChild(document.createTextNode('jframe'));
	caption.appendChild(frame.title);
	
	border.appendChild(caption);
	var body = frame.body = mktag('div', {className: 'jframe_container'});
	
	body.appendChild(document.createTextNode('Hello world'));
	border.appendChild(body);
	td.appendChild(border);
	tr.appendChild(td);
	tbody.appendChild(tr);
	frame.root.appendChild(tbody);
	
	return frame;
}

function jframe_open(tag, link, e)
{
	var frame = jframe_create('Loading...');
	frame.title.innerHTML = tag.innerHTML;
	frame.altLink = tag.href;
	frame.markLoading();
	frame.refresh.link.href = tag.href;
	//frame.refresh.link.alt = tag.alt;
	//frame.refresh.link.title = tag.title;
	document.body.appendChild(frame.root);
	jframe_centerize(frame);
	if ( ! async_raw_get(link, frame.onFirstLoad) )
	{
		window.location.href = tag.href;
		return false;
	}
	return prevent(true, e);
}

/**
* Разбить строку на две
*/
function split2(str, sep)
{
	var offset = str.indexOf(sep);
	return offset < 0 ? [str, ""] : [str.substr(0, offset), str.substr(offset)];
}

function jframe_setmode(link)
{
	var a = split2(link, '#');
	var b = split2(a[0], '?');
	var args = b[1] != '' ? b[1] + '&mode=ajax' : '?mode=ajax';
	return b[0] + args + a[1];
}

// центрировать фрейм в видимой области окна
function jframe_centerize(frame)
{
	var screenSize = getScreenSize();
	var frameSize = getElementSize(frame.root);
	var scroll = ws_get_scroll();
	frame.left = Math.floor( (screenSize.w - frameSize.w) / 2 ) + scroll.X;
	frame.top = Math.floor( (screenSize.h - frameSize.h) / 2 ) + scroll.Y;
	frame.root.style.left = Math.max(frame.left, scroll.X, 0) + 'px';
	frame.root.style.top = Math.max(frame.top, scroll.Y, 0) + 'px';
}

// предотватить действие по умолчанию
function prevent(Status, e)
{
	if ( Status )
	{
		if ( !e ) e = window.event;
		else {
			if ( e.stopPropagation ) e.stopPropagation(); else e.cancelBubble = true;
			if ( e.preventDefault ) e.preventDefault(); else e.returnValue = false;
		}
		return false;
	}
	return true;
}

// создать обработчик события, который предотващает действие по-умолчанию
function prevented(Action) {
	var args = new Array ();
	for(var i = 1; i < arguments.length; i++) args[i-1] = arguments[i];
	return function (e) { return prevent(Action.apply(Action, args), e); };
}

// Вернуть объект по ID
function obj(id) { return document.getElementById(id); }

// вернуть полный URL
function url(s) { return ajax_prefix + s; }

var ws_drag = false;

// начать перетаскивание фрейма
function ws_start_drag(frame, evnt) {
	var e = ! window.event ? evnt : window.event;
	ws_drag = frame;
	frame.ws.startX = e.screenX;
	frame.ws.startY = e.screenY;
	return true;
}

// завершить перетаскивание фрейма
function ws_stop_drag() {
	if ( ! ws_drag ) return false;
	ws_drag = false;
	return true;
}

// обработчик перетаскивания фреймов
/*
document.onmousemove = function (evnt) {
	if ( ! ws_drag ) return false;
	var e = ! window.event ? evnt : window.event;
	ws_drag.ws.left += e.screenX - ws_drag.ws.startX;
	ws_drag.ws.top += e.screenY - ws_drag.ws.startY;
	ws_drag.ws.startX = e.screenX;
	ws_drag.ws.startY = e.screenY;
	ws_drag.style.left = ws_drag.ws.left + 'px';
	ws_drag.style.top = ws_drag.ws.top + 'px';
	return prevent(true, evnt);
};

// завершить перетаскивание объекта
document.onmouseup = prevented(ws_stop_drag);*/

function select_ip(param, e) {
	document.getElementById('select_' + param + '_1').style.display = 'none';
	document.getElementById('select_' + param + '_2').style.display = 'inline-block';
	var area_id = 'select_' + param + '_2';
	var ip = document.getElementById(param).value;

	//ajax_frame('select_' + param + '_2', '/ajax/select_ip.php?param=' + encodeURIComponent(param) + '&ip=' + encodeURIComponent(ip));
	ajax_put(area_id, '/ajax/select_ip.php?param=' + encodeURIComponent(param) + '&ip=' + encodeURIComponent(ip), select_ip_on_load, param);
	return prevent(true, e);
}

function select_ip_commit(param, e) {
	var networks = document.getElementById('select_' + param + '_networks');
	var ips = document.getElementById('select_' + param + '_ips');
	document.getElementById(param).value = ips.value;
	document.getElementById('select_' + param + '_title').innerHTML = selected_text(networks) + ': ' + selected_text(ips);
	document.getElementById('select_' + param + '_1').style.display = 'inline-block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function select_ip_cancel(param, e) {
	document.getElementById('select_' + param + '_1').style.display = 'inline-block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function network_changed(param) {
	var network_list_id = 'select_' + param + '_networks';
	var ip_list_id = 'select_' + param + '_ips';
	var network_list = document.getElementById(network_list_id);
	var ip = document.getElementById(param).value;
	
	ajax_put(ip_list_id, '/ajax/ip_list_options.php?network=' + network_list.value + '&ip=' + ip, network_changed_on_load, param);
}

function select_ip_on_load(target_id, url, param) {
	return network_changed(param);
}

function network_changed_on_load(target_id, url, param) {
	
}

function select_street(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'none';
	document.getElementById('select_' + param + '_2').style.display = 'inline-block';
	var street_id = document.getElementById(param).value;
	ajax_frame('select_' + param + '_2', '/ajax/select_street.php?param=' + encodeURIComponent(param) + '&street_id=' + encodeURIComponent(street_id));
	return prevent(true, e);
}

function select_house(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'none';
	document.getElementById('select_' + param + '_2').style.display = 'inline-block';
	var house_id = document.getElementById(param).value;
	ajax_frame('select_' + param + '_2', '/ajax/select_house.php?param=' + encodeURIComponent(param) + '&house_id=' + encodeURIComponent(house_id));
	return prevent(true, e);
}

function selected_text(obj)
{
	if ( obj && obj.selectedIndex >= 0 && obj.selectedIndex < obj.length ) return obj.options[obj.selectedIndex].text;
	return '';
}

function select_house_commit(param, e)
{
	var cities = document.getElementById('select_' + param + '_cities');
	var streets = document.getElementById('select_' + param + '_streets');
	var houses = document.getElementById('select_' + param + '_houses');
	document.getElementById(param).value = houses.value;
	document.getElementById('select_' + param + '_title').innerHTML = selected_text(cities) + ', ' + selected_text(streets) + ' ' + selected_text(houses);
	document.getElementById('select_' + param + '_1').style.display = 'inline-block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function select_house_cancel(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'inline-block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function street_changed(street_list_id, house_list_id)
{
	var street_list = document.getElementById(street_list_id);
	
	ajax_frame(house_list_id, '/ajax/house_list_options.php?street='+street_list.value);
}

function utm_pickup_last_user(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'none';
	document.getElementById('select_' + param + '_2').style.display = 'block';
	var user_id = document.getElementById(param).value;
	ajax_frame('select_' + param + '_2', '/ajax/utm_pickup_last_user.php?param=' + encodeURIComponent(param) + '&user_id=' + encodeURIComponent(user_id));
	return prevent(true, e);
}

function utm_user_changed(user_list_id, user_card_id)
{
	var user_list = document.getElementById(user_list_id);
	
	ajax_frame(user_card_id, '/ajax/utm_user_card.php?uid='+user_list.value);
}

function utm_pickup_last_user_commit(param, e)
{
	var users = document.getElementById('select_' + param + '_utm_user');
	document.getElementById(param).value = users.value;
	ajax_frame('select_' + param + '_2', '/ajax/utm_user_card.php?uid='+users.value);
	document.getElementById('select_' + param + '_1').style.display = 'block';
	document.getElementById('select_' + param + '_2').style.display = 'block';
	return prevent(true, e);
}

function utm_pickup_last_user_cancel(param, e)
{
	var uid = document.getElementById(param).value;
	ajax_frame('select_' + param + '_2', '/ajax/utm_user_card.php?uid='+uid);
	document.getElementById('select_' + param + '_1').style.display = 'block';
	document.getElementById('select_' + param + '_2').style.display = 'block';
	return prevent(true, e);
}

function select_switch_port(param, e, house_id)
{
	document.getElementById('select_' + param + '_1').style.display = 'none';
	document.getElementById('select_' + param + '_2').style.display = 'block';
	var port = document.getElementById(param).value;
	ajax_frame('select_' + param + '_2', '/ajax/select_switch_port.php?param=' + encodeURIComponent(param) + '&port=' + encodeURIComponent(port) + '&house=' + house_id);
	return prevent(true, e);
}

function switch_changed(switch_list_id, port_list_id)
{
	var switch_list = document.getElementById(switch_list_id);
	
	ajax_frame(port_list_id, '/ajax/port_list_options.php?switch_id='+switch_list.value);
}

function select_switch_port_commit(param, e)
{
	var switches = document.getElementById('select_' + param + '_switches');
	var ports = document.getElementById('select_' + param + '_ports');
	document.getElementById(param).value = switches.value + '/' + ports.value;
	document.getElementById('select_' + param + '_title').innerHTML = selected_text(switches) + ', порт ' + selected_text(ports);
	document.getElementById('select_' + param + '_1').style.display = 'block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function select_switch_port_cancel(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function ajax_put_on_load(request, args)
{
	args.frame.innerHTML = request.responseText;
	if ( args.on_load )
	{
		args.on_load(args.target_id, args.url, args.data);
	}
}

/**
* target_id - ID контейнера куда будет помещен контент
* url - URL с которого будет загружен контент
* on_load(target_id, url, data) - функция которая будет вызвана после загрузки контента
* data - пользовательские данные которые будут переданы в функцию on_load()
*/
function ajax_put(target_id, url, on_load, data)
{
	var frame = document.getElementById(target_id);
	var result = async_raw_get(url, ajax_put_on_load, {frame: frame, target_id: target_id, url: url, on_load: on_load, data: data});
	if ( result === false ) return false;
	frame.innerHTML = '<div class="ajax_loading"></div>';
	return true;
}

function street_changed_on_load(target_id, url, param)
{
	var house_list_id = 'select_' + param + '_houses';
	var client_list_id = 'select_' + param + '_clients';
	house_changed(house_list_id, client_list_id);
}

function street_changed_2(param)
{
	var street_list_id = 'select_' + param + '_streets';
	var house_list_id = 'select_' + param + '_houses';
	var street_list = document.getElementById(street_list_id);
	
	ajax_put(house_list_id, '/ajax/house_list_options.php?street=' + street_list.value, street_changed_on_load, param);
}

function city_changed(city_list_id, street_list_id)
{
	var city_list = document.getElementById(city_list_id);
	
	ajax_frame(street_list_id, '/ajax/street_list_options.php?city='+city_list.value);
}

function city_changed_on_load(target_id, url, param)
{
	var street_list_id = 'select_' + param + '_streets';
	var house_list_id = 'select_' + param + '_houses';
	street_changed(street_list_id, house_list_id);
}

function city_changed_2(param)
{
	var city_list_id = 'select_' + param + '_cities';
	var street_list_id = 'select_' + param + '_streets';
	var city_list = document.getElementById(city_list_id);
	
	ajax_put(street_list_id, '/ajax/street_list_options.php?city=' + city_list.value, city_changed_on_load, param);
}

function select_street_commit(param, e)
{
	var cities = document.getElementById('select_' + param + '_cities');
	var streets = document.getElementById('select_' + param + '_streets');
	document.getElementById(param).value = streets.value;
	document.getElementById('select_' + param + '_title').innerHTML = selected_text(cities) + ', ул. ' + selected_text(streets);
	document.getElementById('select_' + param + '_1').style.display = 'inline-block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function select_street_cancel(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'inline-block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function house_changed(house_list_id, client_list_id)
{
	var house_list = document.getElementById(house_list_id);
	
	ajax_frame(client_list_id, '/ajax/client_list_options.php?house='+house_list.value);
}

function select_client(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'none';
	document.getElementById('select_' + param + '_2').style.display = 'block';
	var client_id = document.getElementById(param).value;
	ajax_frame('select_' + param + '_2', '/ajax/select_client.php?param=' + encodeURIComponent(param) + '&client_id=' + encodeURIComponent(client_id));
	return prevent(true, e);
}

function select_client_commit(param, e)
{
	var streets = document.getElementById('select_' + param + '_streets');
	var houses = document.getElementById('select_' + param + '_houses');
	var clients = document.getElementById('select_' + param + '_clients');
	document.getElementById(param).value = clients.value;
	document.getElementById('select_' + param + '_title').innerHTML = selected_text(streets) + ' ' + selected_text(houses) + ', кв. ' + selected_text(clients);
	document.getElementById('select_' + param + '_1').style.display = 'block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function select_client_cancel(param, e)
{
	document.getElementById('select_' + param + '_1').style.display = 'block';
	document.getElementById('select_' + param + '_2').style.display = 'none';
	return prevent(true, e);
}

function gc_subtag(tag, offset)
{
	var subtag = tag.firstChild;
	var i = 0;
	while ( subtag )
	{
		if ( subtag.tagName )
		{
			if ( i == offset ) return subtag;
			i++;
		}
		subtag = subtag.nextSibling;
	}
}

function gc_table_filter_init(rows)
{
	rows.gcdata = {filter: new Array()};
	var table = rows.parentNode;
	var head = gc_subtag(gc_subtag(table, 0), 0);
	var i = 0;
	var col = head.firstChild;
	while ( col )
	{
		if ( col.tagName )
		{
			rows.gcdata.filter[i] = '';
			i++;
		}
		col = col.nextSibling;
	}
}

function gc_table_filter(subj, col, rows_id, e, counter_id)
{
	var rows = document.getElementById(rows_id);
	if ( ! rows.gcdata ) gc_table_filter_init(rows);
	var row = rows.firstChild;
	rows.gcdata.filter[col-1] = subj.toLowerCase();
	var filter = rows.gcdata.filter;
	var counter = 0;
	while ( row )
	{
		if ( row.tagName == 'TR' )
		{
			var ok = true;
			var i = 0;
			var td = row.firstChild;
			while ( td )
			{
				if ( td.tagName == 'TD' )
				{
					var pattern = filter[i];
					if ( td.textContent.toLowerCase().indexOf(pattern) < 0 )
					{
						ok = false;
					}
					i++;
				}
				td = td.nextSibling;
			}
			row.style.display = ok ? "table-row" : "none";
			counter = ok ? counter+1 : counter;
		}
		row = row.nextSibling;
	}
	if (counter_id)
	{
		document.getElementById(counter_id).innerHTML = counter;
	}
}

function filter_payments(a_filter, a_col, a_rows_id, event)
{
	gc_table_filter(a_filter, a_col, a_rows_id, event);
	var rows = document.getElementById(a_rows_id);
	if ( ! rows.gcdata ) gc_table_filter_init(rows);
	var row = rows.firstChild;
	var sum = 0;
	var count = 0;
	while ( row )
	{
		if ( row.tagName == 'TR' && row.style.display != "none" )
		{
			var i = 0;
			var td = row.firstChild;
			while ( td )
			{
				if ( td.tagName == 'TD' )
				{
					if ( i == 3 )
					{
						count ++;
						sum += parseFloat(td.innerHTML);
						break;
					}
					i++;
				}
				td = td.nextSibling;
			}
		}
		row = row.nextSibling;
	}
	document.getElementById('payments_count').innerHTML = count;
	document.getElementById('payments_sum').innerHTML = sum;
}

function passgen_renew(obj)
{
	var pg = document.getElementById(obj);
	var val1 = Math.floor(Math.random()*0xFFFF);
	var val2 = Math.floor(Math.random()*0xFFFF);
	pg.value=val1.toString(16) + val2.toString(16);
}

// вызывается в теге body
function onloadPage()
{
	global_on_timeout();
}

function set_timer()
{
	window.setTimeout(function() { global_on_timeout(); },3000);
}

// когда флаг отключен (значение stop) фрэйм сообщений не обновляется
var message_flag="go";
var sw_mon_flag="go";
function global_on_timeout()
{
	set_timer();
	if (window.sw_mon_flag != 'stop') async_raw_get('/ajax/switch_monitor.php', makeSwitchTree, 'switch_monitor_block');
	ajax_frame('eventmon_block', '/ajax/event_module_status.php');
	ajax_frame('roskom_load_block', '/ajax/roskom_monitor.php');
	if (window.message_flag != 'stop') ajax_frame('messages_block', '/ajax/messages_to_me.php');
}

function set_eventmon_timer() {
	window.setTimeout(function() { eventmon_on_timeout(); }, 5000);
}

// Звуковое уведомление
var alert_sound = new Audio('/static/voffice/sounds/warning.mp3');

// Был ли уже подан звуковой сигнал — чтобы подавать лишь один раз
var eventmon_signal_flag = false;

function ajax_check_eventmon(request, data) {
	var reply;
	eval("reply = " + request.responseText);
	if(reply.have_new_failures) {
		if(!eventmon_signal_flag) alert_sound.play();
		eventmon_signal_flag = true; // сигнал подан
	} else {
		if(eventmon_signal_flag) {
			alert_sound.pause();
			alert_sound.currentTime = 0;
		}
		eventmon_signal_flag = false;
	}
}

function eventmon_on_timeout() {
	set_eventmon_timer();
	ajax_frame('eventmon_body', '/ajax/eventmon.php');
	async_raw_get('/ajax/eventmon-updates.php', ajax_check_eventmon, null);
}

function set_pppoemon_timer() {
	window.setTimeout(function() { pppoemon_on_timeout(); }, 2000);
}

function pppoemon_on_timeout() {
	set_pppoemon_timer();
	username = document.getElementById('pppoemon_username').value;
	ajax_frame('pppoemon_body', '/ajax/pppoemon.php?username=' + username);
}

function sidebarBlockToggle(block_header, block_name)
{
	var parent = block_header.parentNode;
	var invisibility = 0;
	for (var i=0; i<parent.childNodes.length; i++) {
		var child = parent.childNodes[i];
		if (child.nodeType==1 && child != block_header) {
			if (child.className.indexOf('invisible')>-1) {
				child.classList.remove('invisible');
				invisibility=0;
			} else {
				child.classList.add('invisible');
				invisibility=1;
			}
			var request = ajax_request();
			if ( request !== false ) {
				request.open('GET', "/ajax/sidebar_block_toggle.php?block_name="+block_name+"&invisibility="+invisibility, true);
				request.send(null);
			}
		}
	}
}

function appendConnected(trouble_list_id)
{
	var trouble_list=document.getElementById(trouble_list_id);
	var house_list = makeHouseListToString(trouble_list_id);
	var xmlDoc = sync_get_xml("/ajax/mailing_trouble.php?house_list="+house_list);
	var houses = xmlDoc.getElementsByTagName('house');
	trouble_list.length=0;
	for (var i=0; i<houses.length; i++)
	{
		var new_option = new Option(houses[i].getAttribute('house_name'), houses[i].getAttribute('house_id'));
		trouble_list.appendChild(new_option);
	}
}

function makeHouseListToString(trouble_list_id)
{
	var trouble_list=document.getElementById(trouble_list_id);
	var house_list="";
	for (var i=0; i<trouble_list.length; i++)
	{
		house_list = house_list+","+trouble_list.options[i].value;
	}
	return house_list.replace(/^,/, '');
}

function checkTroubleList(trouble_list_id)
{
	var trouble_list=document.getElementById(trouble_list_id);
	if (trouble_list.length>0) return true;
	alert("Необходимо выбрать хотя бы один дом");
	return false;
}

/**
 * Пагинатор
 * Чтобы использовать необходимо создать в шаблоне table с id-шником, после чего вызвать
 * сей метод, передав id таблицы, общее количество страниц, номер текущей страницы
 * и количество выводимых ссылок на страницы, если значение по-умолчанию не устраивает.
 *
 * На данные момент используется только в
 * - themes/default/admin/task/statistic.tpl
 * - themes/default/admin/tv/all_stb.tpl
 * Написан альтернативный вариант на php: mod_common::createPager, поэтому эту функцию следует в будущем
 * удалить (и, соответственно, переделать в вышеуказанных скриптах).
 */
function paginatorInflater(paginator_table_id, num_pages, cur_page, link_limit, use_form) {
	link_limit = link_limit || 15; // значение по-умолчанию
	use_form = use_form || false; // значение по-умолчанию
	if (! num_pages) return false;
	var paginator = document.getElementById(paginator_table_id);
	var tr = document.createElement('TR');

	if (cur_page>1) num = cur_page-1;
		else num=false;
	tr.appendChild(paginatorCreateLinkTD('<', num));

	var min_i = 1;
	var max_i = num_pages;
	if (num_pages>link_limit) {
		if (cur_page <= link_limit/2) max_i = link_limit;
		else if (cur_page > num_pages-Math.floor(link_limit/2)) min_i = num_pages-link_limit+1;
		else {
			min_i = cur_page - Math.floor(link_limit/2);
			max_i = cur_page + Math.floor(link_limit/2);
		}
	}

	for (var i=min_i; i<=max_i; i++) {
		if (i==min_i && i>1) tr.appendChild(paginatorCreateLinkTD('…', false));

		if (i!=cur_page) num = i;
			else num=false;
		tr.appendChild(paginatorCreateLinkTD(i, num));

		if (i==max_i && i<num_pages) tr.appendChild(paginatorCreateLinkTD('…', false));
	}

	if (cur_page<num_pages) num = cur_page+1;
		else num=false;
	tr.appendChild(paginatorCreateLinkTD('>', num));

	paginator.appendChild(tr);


	/**
	* возвращает DOM-объект 'TD', который содержит ссылку с текстом из первого аргумента
	* и, если передан второй агрумент, href устанавливается в него.
	*/
	function paginatorCreateLinkTD(text, num) {
		num = num || false; // значение по-умолчанию
		var new_td = document.createElement('TD');
		var new_link = document.createElement('A');
		if (num) {
			if (use_form) {
				new_link.onclick = function() { onclickFormSubmit(num); };
				new_link.style.color="#c00";
				new_link.style.cursor="pointer";
			} else new_link.href = '?page='+num;
		}
		new_link.appendChild(document.createTextNode(text));
		new_td.appendChild(new_link);
		return new_td;
	}

	/**
	* Если используется POST-форма вместо GET
	*/
	function onclickFormSubmit(num) {
		document.forms[0].elements['page_num'].value=num;
		document.forms[0].submit();
		return false;
	}
}

/**
 * Проверить совпадают ли id-шники пакетов с теми, которые в портале
 */
function checkPortalPackagesOnLoad(request, table_id) {
	var packages = JSON.parse(request.responseText);
	var portal_packages = new Array;
	for (var i=0; i<packages.length; i++) {
		portal_packages[packages[i]['external_id']] = packages[i]['name'];
	}

	var table = document.getElementById(table_id);
	var table_tr = table.rows;
	var pkg_portal_id_td;
	var pkg_id;
	for (var i=1; i<table_tr.length; i++) {
		pkg_portal_id_td = table_tr[i].cells[2];
		if (pkg_portal_id_td.hasAttribute('id')) {
			pkg_id = pkg_portal_id_td.getAttribute('id').replace('portal_id_','');
			// производим сравнение и раскрашиваем ячейку
			if (portal_packages[pkg_id] !== undefined) {
				pkg_portal_id_td.style.backgroundColor = "#d0ecd4"; // дефолтный для gc_table - #e0e3e4
				pkg_portal_id_td.title = "В портале: \"" + portal_packages[pkg_id] + "\"";
			} else {
				pkg_portal_id_td.style.backgroundColor = "#ead3d4";
				pkg_portal_id_td.title = "Пакет с таким id отсутствует в портале";
			}
		}
	}
}

function selectHouseType(type, event)
{
	var none = document.getElementById('none');
	var mkd = document.getElementById('mkd');
	var priv = document.getElementById('private');
	if (type == 'mkd') 
	{
		mkd.style.background = "#d0ecd4";
		priv.style.background = "#D6D6CF";
		none.style.background = "#D6D6CF";
	}
	else
	{
		if (type == 'none') 
		{
			none.style.background = "#d0ecd4";
			mkd.style.background = "#D6D6CF";
			priv.style.background = "#D6D6CF";
		}
		else
		{
			priv.style.background = "#d0ecd4";
			mkd.style.background = "#D6D6CF";
			none.style.background = "#D6D6CF";
		}
	}
}

/**
 * Альтернативный виджет для выбора абонента
 * Позволяет выбрать абонента из любого города, а также
 * добавлена поддержка частных домов
 */
function select_client2(param) {
  var block_1 = document.getElementById('select_'+param+'_1');
  var block_2 = document.getElementById('select_'+param+'_2');
  var block_title = document.getElementById('select_'+param+'_title');

  turn_display(block_1, 'block', false);
  turn_display(block_2, 'block', true);
  var form_target_client_id = document.getElementById(param);

  var city_choose = document.createElement('select');
  var street_choose = document.createElement('select');
  var house_choose = document.createElement('select');
  var flat_choose = document.createElement('select');

  var private_flag_label = document.createElement('label');
  var private_flag = document.createElement('input');
  private_flag.type = 'checkbox';
  private_flag_label.appendChild(private_flag);
  private_flag_label.appendChild(document.createTextNode('Частный дом'));

  var ok_btn = document.createElement('button');
  ok_btn.textContent = 'Ок';
  var cncl_btn = document.createElement('button');
  cncl_btn.textContent = 'Отмена';

  block_2.className = 'select_client_container';
  city_choose.className = 'r_field_short';
  street_choose.className = 'r_field_short';
  house_choose.className = 'r_field_short';
  flat_choose.className = 'r_field_short';
  ok_btn.className = 'r_button';
  cncl_btn.className = 'r_button';

  city_choose.onchange = function() {  street_fill(); };
  street_choose.onchange = function() {  house_fill(); };
  house_choose.onchange = function() {  flat_fill(); };
  private_flag.onchange = function() {  on_private_flag(); };
  ok_btn.onclick = function() {  return on_ok(); };
  cncl_btn.onclick = function() {  return on_cncl(); };

  block_2.appendChild(private_flag_label);
  block_2.appendChild(city_choose);
  block_2.appendChild(street_choose);
  block_2.appendChild(house_choose);
  block_2.appendChild(flat_choose);
  block_2.appendChild(document.createElement('br'));
  block_2.appendChild(ok_btn);
  block_2.appendChild(cncl_btn);

  (function city_fill() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/ajax/get_cities.php', true);
    xhr.onload = function() {
      if (xhr.status == 200) {
        var cities = JSON.parse(xhr.responseText);
        for (var i=0, length=cities.length; i<length; i++) {
          city_choose.appendChild(new Option(cities[i].city_name, cities[i].city_id));
        }
        street_fill();
      } else console.warn('[XHR] ошибка запроса списка городов', xhr.status, xhr.statusText);
    };
    xhr.send();
  })();
  function street_fill() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/ajax/get_streets.php?city_id=' + city_choose.value + '&private=' + private_flag.checked, true);
    xhr.onload = function() {
      if (xhr.status == 200) {
        var streets = JSON.parse(xhr.responseText);
        street_choose.length = 0;
        for (var i=0, length=streets.length; i<length; i++) {
          street_choose.appendChild(new Option(streets[i].street_name, streets[i].street_id));
        }
        house_fill();
      } else console.warn('[XHR] ошибка запроса списка улиц', xhr.status, xhr.statusText);
    };
    xhr.send();
  }
  function house_fill() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/ajax/get_houses.php?street_id=' + street_choose.value + '&private=' + private_flag.checked, true);
    xhr.onload = function() {
      if (xhr.status == 200) {
        var houses = JSON.parse(xhr.responseText);
        house_choose.length = 0;
        if (private_flag.checked) {
          for (var i=0, length=houses.length; i<length; i++) {
            house_choose.appendChild(new Option(houses[i].client_house + ' ' + houses[i].client_name, houses[i].client_id));
          }
        } else {
          for (var i=0, length=houses.length; i<length; i++) {
            house_choose.appendChild(new Option(houses[i].house_number, houses[i].house_id));
          }
          flat_fill();
        }
      } else console.warn('[XHR] ошибка запроса списка домов', xhr.status, xhr.statusText);
    };
    xhr.send();
  }
  function flat_fill() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/ajax/get_flats.php?house_id=' + house_choose.value, true);
    xhr.onload = function() {
      if (xhr.status == 200) {
        var clients = JSON.parse(xhr.responseText);
        flat_choose.length = 0;
        for (var i=0, length=clients.length; i<length; i++) {
          flat_choose.appendChild(new Option(clients[i].client_flat + ' ' + clients[i].client_name, clients[i].client_id));
        }
      } else console.warn('[XHR] ошибка запроса списка квартир', xhr.status, xhr.statusText);
    };
    xhr.send();
  }
  function on_private_flag() {
    turn_display(flat_choose, '', !private_flag.checked);
    street_fill();
  }
  function on_ok() {
    form_target_client_id.value = private_flag.checked ? house_choose.value : flat_choose.value;
    block_title.textContent = selected_text(street_choose) + ' ' + selected_text(house_choose);
    if (!private_flag.checked) block_title.textContent += ', кв. ' + selected_text(flat_choose);
    return on_cncl();
  }
  function on_cncl() {
    turn_display(block_1, 'block', true);
    turn_display(block_2, 'block', false);
    block_2.innerHTML = '';
    return false;
  }
}

function showTab(tab_id) {
	tabcontent = document.getElementsByClassName("tabcontent");
	for(i = 0; i < tabcontent.length; i ++) {
		tabcontent[i].style.display = 'none';
	}

	tab = document.getElementById(tab_id);
	if(tab) {
		tab.style.display = 'block';
	}
}
