

$('.download_details').click(function(){
	var frametarget = $(this).attr('href');
	var targetmodal = $(this).attr('target');
	if(targetmodal == undefined) {
		targetmodal = '#popupModal';
	} else {
		targetmodal = '#' + targetmodal;
	}
	if ($(this).attr('title') != undefined) {
		$(targetmodal + ' .modal-header h3').html($(this).attr('title'));
		$(targetmodal + ' .modal-header').show();
	} else {
		$(targetmodal+' .modal-header h3').html('');
		$(targetmodal+' .modal-header').hide();
	}
	$(targetmodal).on('show', function () {
		$('iframe').attr("src", frametarget );   
	});
	$(targetmodal).modal({show:true});
	return false
});

$('.scan_details').click(function(){
	var frametarget = $(this).attr('href');
	var targetmodal = $(this).attr('target');
	if(targetmodal == undefined) {
		targetmodal = '#popupModal';
	} else {
		targetmodal = '#' + targetmodal;
	}
	if ($(this).attr('title') != undefined) {
		$(targetmodal + ' .modal-header h3').html($(this).attr('title'));
		$(targetmodal + ' .modal-header').show();
	} else {
		$(targetmodal+' .modal-header h3').html('');
		$(targetmodal+' .modal-header').hide();
	}
	$(targetmodal).on('show', function () {
		$('iframe').attr("src", frametarget );   
	});
	$(targetmodal).modal({show:true});
	return false
});

var xml_http_request;
var ajax_error = 'Ошибка AJAX. Вероятно, вы используете устаревший обозреватель.';

// TODO: сделать поддержку множественных уведомлений
function showMessage(text) {
	$.msgGrowl({type: 'info', title: 'Информация', text: text});
}

var last_scan = null;

// Наши обновления пришли, посмотрим, что там
function handleUpdates(updates) {
	progress_bar = $('#progress_bar');
	progress_bar_value = $('#progress_bar_value');
	perform_scan_button = $('#perform_scan_button');
	if(updates.scan_id) {
		last_scan = $('#scan_state_' + updates.scan_id);
		scan_progress = parseInt(updates.scan_progress);
		last_scan.text(scan_progress + '% выполнено');
		progress_bar.show();
		progress_bar_value.css('width', scan_progress + '%');
	} else {
		if(last_scan) {
			last_scan.addClass('label-success');
			last_scan.text('Завершена');
			last_scan = null;
		}
		progress_bar.hide();
		perform_scan_button.show();
	}
}

// Сервер корректно ответил, обработаем ответ
function handleAPIResponse(result) {
	switch(result.method) {
		case 'add_ssh_key':
			$('#ssh_key').modal('hide');
			location.reload();
		break;
		case 'del_ssh_key':
			location.reload();
		break;
		case 'check_updates':
			handleUpdates(result);
		break;
		default:
			showMessage('Неадекватный ответ сервера');
			//unlockAll();
		break;
	}
}

// Сервер корректно ответил, но вызванный метод рапортовал об ошибке
function handleAPIFailure(result) {
	switch(result.method) {
		case 'add_ssh_key':
			$('#ssh_key_warning').html(result.message);
			key = $('#id_key');
			button = $('#add_key_button');
			key.prop('disabled', false);
			button.prop('disabled', false);
			return true; // Мы обработали эту ошибку, не будем сообщать о ней
		break;
		case 'del_ssh_key':
			return false;
		break;
		default:
			return false;
		break;
	}
}

// Сервер ответил, но мы пока не знаем, может, там какая-то плесень
function handleServerResponse() {
	if(xml_http_request.readyState == 4 || xml_http_request.readyState == 'complete') {
		if(xml_http_request.status == 200) {
			result = $.parseJSON(xml_http_request.responseText);
			if(result.result != 'ok') {
				if(!handleAPIFailure(result)) {
					showMessage('Ошибка AJAX: ' + result.message);
				}
				return;
			}

			handleAPIResponse(result);
		} else {
			result = $.parseJSON(xml_http_request.responseText);
			showMessage('Ошибка AJAX <' + xml_http_request.status + '> ' + result.message);
			unlockAll();
		}
	}
}

// Получить объект для осуществления запросов
function getXmlHttpObject() {
	var instance = null;

	if(window.XMLHttpRequest) {
		instance = new XMLHttpRequest();
	} else if(window.ActiveXObject) {
		instance = new ActiveXObject('Microsoft.XMLHTTP');
	} else {
		return false;
	}

	instance.onreadystatechange = handleServerResponse;
	return instance;
}

function add_ssh_key() {
	if(!(xml_http_request = getXmlHttpObject())) {
		showMessage(ajax_error);
		return false;
	}

	key = $('#id_key');
	button = $('#add_key_button');

	key_data = key.val();
	key.prop('disabled', true);
	button.prop('disabled', true);

	data = new FormData();
	data.append('key_data', $.trim(key_data));

	xml_http_request.open("POST", '/api/add_ssh_key', true);
	xml_http_request.setRequestHeader("X-CSRFToken", $('#csrf_token').val());
	xml_http_request.send(data);

	return false;
}

function del_ssh_key(key_name) {
	if(!(xml_http_request = getXmlHttpObject())) {
		showMessage(ajax_error);
		return false;
	}

	data = new FormData();
	data.append('key_name', key_name);

	xml_http_request.open("POST", '/api/del_ssh_key', true);
	xml_http_request.setRequestHeader("X-CSRFToken", $('#csrf_token').val());
	xml_http_request.send(data);

	return false;
}

function check_updates(first) {
	if(!(xml_http_request = getXmlHttpObject())) {
		showMessage(ajax_error);
		return false;
	}

	xml_http_request.open("GET", '/api/check_updates', true);
	xml_http_request.send(null);

	if(!first) {
		setTimeout(check_updates, 5000);
	}

	return false;
}

$('.ssh-key-del-confirm').live ('click', function (e) {
	var key_name = $(this).attr('data-target');
	$.msgbox("Действительно удалить выбранный ключ ssh (" + key_name + ")?", {
		type: "confirm", buttons: [{type: "submit", value: "Yes", class: "btn btn-danger"}, {type: "submit", value: "No", class: "btn btn-secondary"}, {type: "cancel", value: "Cancel", class: "btn btn-secondary"}]
	}, function(result) {
		if(result == 'Yes') {
			del_ssh_key(key_name);
		}
	});
});

// Обновимся и поставим обновлять каждые 5 секунд
check_updates(true);
setTimeout(check_updates, 5000);
