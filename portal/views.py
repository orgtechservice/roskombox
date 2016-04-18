# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# компоненты Django
from django.http import HttpResponse, HttpResponseForbidden, Http404
from django.template import RequestContext
from django.shortcuts import render_to_response as render, get_object_or_404, redirect
from django.views.decorators.csrf import csrf_exempt
from django.views.decorators.http import require_http_methods, require_POST, require_GET
from django.contrib.auth.decorators import login_required
from django.contrib.auth import logout
from django.core.mail import send_mail
from django.core.paginator import Paginator
from django.core.urlresolvers import reverse
from django.db.models import Q
from django.db import connection
from django.core.paginator import Paginator

# наш проект
from portal.models import *
from portal.forms import *
from portal.utils import *

# Python
import subprocess

# Обработчик главной страницы сайта
@login_required
def home_page(request):
	title = 'Домашняя страница'
	description = 'Обзор состояния системы'
	return render('home-page.htt', {'page': 'home', 'title': title, 'description': description}, RequestContext(request))

@login_required
def downloads_page(request):
	title = 'Выгрузки'
	description = 'Запросы на получение содержимого реестра запрещённых сайтов'
	all_downloads = Download.objects.all()
	paginator = Paginator(all_downloads, 15)
	page = get_page_number(request)
	unfinished = have_unfinished_download()
	impossible = not auth_files_provided()

	try:
		downloads = paginator.page(page)
	except:
		downloads = paginator.page(1)

	return render('downloads-page.htt', {'page': 'downloads', 'title': title, 'description': description, 'downloads': downloads, 'unfinished': unfinished, 'impossible': impossible}, RequestContext(request))

@login_required
def reports_page(request):
	title = 'Проверки'
	description = 'Автоматические проверки доступности запрещённых сайтов'
	impossible = not auth_files_provided()
	unfinished = have_unfinished_scan()
	all_scans = Scan.objects.all()
	paginator = Paginator(all_scans, 15)
	page = get_page_number(request)

	try:
		scans = paginator.page(page)
	except:
		scans = paginator.page(1)

	return render('reports-page.htt', {'page': 'reports', 'title': title, 'description': description, 'scans': scans, 'impossible': impossible, 'unfinished': unfinished}, RequestContext(request))

@login_required
def settings_main_page(request):
	title = 'Настройки'
	description = 'Настройки приложения «Роскомбокс»'

	search_substring = Setting.read('search_substring', settings.ROSKOM_SEARCH_SUBSTRING)
	if search_substring == '':
		search_substring = settings.ROSKOM_SEARCH_SUBSTRING
	
	data = {
		'request_xml': AuthFile.exists('request.xml'),
		'request_xml_sign': AuthFile.exists('request.xml.sign'),
		'email': Setting.read('email', ''),
		'max_available': Setting.read('max_available', '0'),
		'search_substring': search_substring,
		'disable_downloads': (Setting.read('disable_downloads', '0') == '1'),
		'disable_checks': (Setting.read('disable_checks', '0') == '1'),
		'download_interval': int(Setting.read('download_interval', settings.ROSKOM_DOWNLOAD_INTERVAL)),
		'check_hour': int(Setting.read('check_hour', settings.ROSKOM_CHECK_HOUR)),
		'page': 'settings', 'subpage': 'main', 'title': title, 'description': description,
	}

	return render('settings/main-page.htt', data, RequestContext(request))

@login_required
def settings_auth_files_page(request):
	title = 'Настройки'
	description = 'Файлы для доступа к API Роскомнадзора'
	
	if request.method == 'POST':
		form = AuthFilesForm(request.POST, request.FILES)
		if form.is_valid():
			AuthFile.objects.all().delete()
			
			file = AuthFile(filename = 'request.xml', data = form.cleaned_data['request_xml'])
			file.save()

			file = AuthFile(filename = 'request.xml.sign', data = form.cleaned_data['request_xml_sign'])
			file.save()

			return redirect(reverse('settings'))
		else:
			return render('settings/auth-files-page.htt', {'page': 'settings', 'subpage': 'auth-files', 'title': title, 'description': description, 'form': form}, RequestContext(request))
	else:
		form = AuthFilesForm()
		return render('settings/auth-files-page.htt', {'page': 'settings', 'subpage': 'auth-files', 'title': title, 'description': description, 'form': form}, RequestContext(request))

@login_required
def settings_other_page(request):
	title = 'Настройки'
	description = 'Настройки, не включённые в другие категории'
	if request.method == 'POST':
		form = OtherSettingsForm(request.POST)
		if form.is_valid():
			Setting.write('email', form.cleaned_data['email'])
			Setting.write('max_available', form.cleaned_data['max_available'])
			Setting.write('search_substring', form.cleaned_data['search_substring'])
			return redirect(reverse('settings'))
		else:
			return render('settings/other-page.htt', {'page': 'settings', 'subpage': 'other', 'title': title, 'description': description, 'form': form}, RequestContext(request))
	else:
		data = {
			'email': Setting.read('email', ''),
			'max_available': int(Setting.read('max_available', '0')),
			'search_substring': Setting.read('search_substring', ''),
		}
		form = OtherSettingsForm(data)
		return render('settings/other-page.htt', {'page': 'settings', 'subpage': 'other', 'title': title, 'description': description, 'form': form}, RequestContext(request))

@login_required
def settings_password_page(request):
	title = 'Настройки'
	description = 'Изменение пароля учётной записи'
	if request.method == 'POST':
		form = PasswordSettingsForm(user = request.user, data = request.POST)
		if form.is_valid():
			password = form.cleaned_data['new_password_1']
			user = request.user
			user.set_password(password)
			user.save()
			return redirect(reverse('settings'))
		else:
			return render('settings/password-page.htt', {'page': 'settings', 'subpage': 'password', 'title': title, 'description': description, 'form': form}, RequestContext(request))
	else:
		form = PasswordSettingsForm(user = request.user, data = None)
		return render('settings/password-page.htt', {'page': 'settings', 'subpage': 'password', 'title': title, 'description': description, 'form': form}, RequestContext(request))

@login_required
def settings_ssh_page(request):
	title = 'Настройки'
	description = 'Редактирование настроек доступа по SSH'
	keys = fetch_ssh_keys()
	if request.method == 'POST':
		form = SshSettingsForm(data = request.POST)
		if form.is_valid():
			return redirect(reverse('settings'))
		else:
			return render('settings/ssh-page.htt', {'page': 'settings', 'subpage': 'ssh', 'title': title, 'description': description, 'form': form}, RequestContext(request))
	else:
		form = SshSettingsForm(data = None)
		return render('settings/ssh-page.htt', {'page': 'settings', 'subpage': 'ssh', 'title': title, 'description': description, 'form': form, 'keys': keys}, RequestContext(request))

@login_required
def settings_auto_page(request):
	title = 'Настройки'
	description = 'Настройки автоматики'
	if request.method == 'POST':
		form = AutoSettingsForm(data = request.POST)
		if form.is_valid():
			Setting.write('disable_downloads', int(form.cleaned_data['disable_downloads']))
			Setting.write('disable_checks', int(form.cleaned_data['disable_checks']))
			Setting.write('download_interval', form.cleaned_data['download_interval'])
			Setting.write('check_hour', form.cleaned_data['check_hour'])
			return redirect(reverse('settings'))
		else:
			return render('settings/auto-page.htt', {'page': 'settings', 'subpage': 'auto', 'title': title, 'description': description, 'form': form}, RequestContext(request))
	else:
		disable_downloads = (Setting.read('disable_downloads', '0') == '1')
		disable_checks = (Setting.read('disable_checks', '0') == '1')
		download_interval = int(Setting.read('download_interval', settings.ROSKOM_DOWNLOAD_INTERVAL))
		check_hour = int(Setting.read('check_hour', settings.ROSKOM_CHECK_HOUR))
		data = {'disable_downloads': disable_downloads, 'disable_checks': disable_checks, 'download_interval': download_interval, 'check_hour': check_hour}
		form = AutoSettingsForm(data)
		return render('settings/auto-page.htt', {'page': 'settings', 'subpage': 'auto', 'title': title, 'description': description, 'form': form}, RequestContext(request))

@login_required
def log_page(request):
	title = 'Журнал событий'
	description = 'Журнал, в котором сохраняются все действия автоматики и пользователей'
	return render('log-page.htt', {'page': 'log', 'title': title, 'description': description}, RequestContext(request))

@login_required
def download_details_page(request, download_id):
	download = get_object_or_404(Download, id = int(download_id))
	return render('iframes/download-details.htt', {'download': download}, RequestContext(request))

@login_required
def scan_details_page(request, scan_id):
	scan = get_object_or_404(Scan, id = int(scan_id))
	return render('iframes/scan-details.htt', {'scan': scan}, RequestContext(request))

@login_required
def available_links_page(request, scan_id):
	scan = get_object_or_404(Scan, id = int(scan_id))
	links = AvailableLink.objects.filter(scan = scan)
	return render('iframes/available-links.htt', {'scan': scan, 'links': links}, RequestContext(request))

@login_required
def hide_intro_page(request):
	Setting.write('hide_intro', '1')
	return redir_home_page(request)

############################################

def redir_home_page(request):
	return redirect(reverse('home'))


@login_required
def logout_page(request):
	logout(request)
	return render('logout-page.htt', {}, RequestContext(request))
