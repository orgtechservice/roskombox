# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Python
from os.path import expanduser

# Наш проект
from portal.models import *

# Django
from django.core.mail import send_mail
from django.conf import settings

# Запросить SEO-данные для страницы
def seo_data(page_slug):
	try:
		data = SEOData.objects.get(position = position)
		return data
	except:
		return {}

# Проанализировать запрос и извлечь из него номер запрошенной страницы (для постраничного вывода)
def get_page_number(request):
	try:
		page = int(request.GET.get('page', '0'))
		if page <= 0:
			return 1
	except:
		return 1

	return page

def have_unfinished_download():
	try:
		requested = int(Setting.read('roskom:download_requested', '0'))
		if requested == 1:
			return True

		item = Download.objects.filter(state = 'new').first()
		return item is not None
	except:
		return False

def have_unfinished_scan():
	try:
		requested = int(Setting.read('roskom:scan_requested', '0'))
		if requested == 1:
			return True

		item = Scan.objects.filter(state = 'new').first()
		return item is not None
	except:
		return False

def auth_files_provided():
	# Получим содержимое файлов запроса и подписи
	request_xml = AuthFile.exists('request.xml')
	request_xml_sign = AuthFile.exists('request.xml.sign')
	return request_xml and request_xml_sign

def send_mail_notification(event, arg):
	email = Setting.read('email', '')
	from_email = settings.ROSKOM_FROM_EMAIL
	if email == '':
		return None

	if(event == 'max_available_exceeded'):
		message = "Превышено предельно допустимое число доступных URL. Доступно %d ссылок." % int(arg)
		send_mail('Превышение квоты', message, from_email, [email], fail_silently = False)
	else:
		return None

def fetch_ssh_keys():
	filename = expanduser("~/.ssh/authorized_keys")
	try:
		return [{'mode': l[0], 'name': l[2], 'data': l[1]} for l in [l.strip().split(' ') for l in open(filename, 'r') if l != '']]
	except:
		return []

def append_ssh_key(key_data):
	filename = expanduser("~/.ssh/authorized_keys")

	try:
		with open(filename, 'a+') as file:
			lines = [l.strip() for l in file if l != '']
			lines += [key_data]
			file.truncate(0)
			file.write("\n".join(lines))
			return True
	except:
		return False

	return False

def delete_ssh_key(key_name):
	filename = expanduser("~/.ssh/authorized_keys")

	try:
		with open(filename, 'a+') as file:
			lines = [l.strip() for l in file if (l != '') and (key_name not in l)]
			file.truncate(0)
			file.write("\n".join(lines))
			return True
	except:
		return False

	return False
