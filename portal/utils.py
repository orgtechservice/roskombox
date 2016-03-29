# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Python
import re
from os.path import expanduser

# Наш проект
from portal.models import *

# Django
from django.core.mail import send_mail
from django.conf import settings

re_ssh_key = re.compile(r'^(ssh\-rsa|ssh\-dss) ([a-zA-Z0-9+/]+={0,2}) (([a-z]+)@([a-z]+))$')

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

	print(filename)

	try:
		return [{'mode': l[0], 'name': l[2], 'data': l[1]} for l in [l.strip().split(' ') for l in open(filename, 'r') if re_ssh_key.match(l) is not None]]
	except:
		return []

def append_ssh_key(key_data):
	filename = expanduser("~/.ssh/authorized_keys")

	lines = []
	try:
		with open(filename, 'r') as file:
			lines = [l.strip() for l in file if re_ssh_key.match(l) is not None]
	except:
		pass

	try:
		with open(filename, 'w') as file:
			lines += [key_data]
			file.truncate(0)
			file.write("\n".join(lines) + "\n")
			return True
	except:
		return False

	return False

def delete_ssh_key(key_name):
	filename = expanduser("~/.ssh/authorized_keys")

	lines = []
	try:
		with open(filename, 'r') as file:
			lines = [l.strip() for l in file if re_ssh_key.match(l) is not None]
	except:
		pass

	try:
		with open(filename, 'w') as file:
			lines = [l.strip() for l in lines if key_name not in l]
			file.truncate(0)
			file.write("\n".join(lines) + "\n")
			return True
	except:
		return False

	return False
