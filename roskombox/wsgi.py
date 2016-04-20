# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# WSGI-файл для развёртывания, развёртывание другими способами не допускается!

# Системные импорты
import os, sys, subprocess, signal, threading
from datetime import datetime

# Не оставлять после себя зомби
# http://stackoverflow.com/questions/16807603/python-non-blocking-non-defunct-process
if threading.current_thread() == threading.main_thread():
	signal.signal(signal.SIGCHLD, signal.SIG_IGN)

# Компоненты Django
from django.core.management import call_command
from django.conf import settings

using_uwsgi = False

try:
	import uwsgi
	using_uwsgi = True
except:
	print("This application is meant to be run under uWSGI, note that!")

# Django
from django.core.wsgi import get_wsgi_application
from django.db import connection

# tasks
import roskombox.tasks as tasks

# Задаём путь к модулю настроек
os.environ.setdefault("DJANGO_SETTINGS_MODULE", "roskombox.settings")

# Наш WSGI callable
application = get_wsgi_application()

if using_uwsgi:
	try:
		from uwsgidecorators import *
	except:
		print("Running under uWSGI, but also need uwsgidecorators module!")
		print("If you are using a virtualenv, you can install the decorators manually:")
		print("pip install uwsgidecorators")
		exit(-1)

	def read_setting(name, default_value = None):
		cursor = connection.cursor()
		cursor.execute("SELECT value FROM settings WHERE name = %s", (name,))
		row = cursor.fetchone()
		if row is None:
			return default_value
		else:
			return row[0]

	def downloads_enabled():
		disable = read_setting('disable_downloads', '0')
		return disable == '0'

	def checks_enabled():
		disable = read_setting('disable_checks', '0')
		return disable == '0'

	@cron(-2, -1, -1, -1, -1, target = 'mule')
	def roskom_cleanup(num):
		print("Running roskom_cleanup")
		uwsgi.lock(0)
		call_command('roskom_cleanup')
		uwsgi.unlock(0)
		print("roskom_cleanup finished")

	@cron(-5, -1, -1, -1, -1)
	def roskom_stat(num):
		tasks.update_stats()

	@cron(0, -1, -1, -1, -1, target = 'mule') # ежечасно
	def check_timers(num):
		print("Checking timers")
		download_interval = int(read_setting('download_interval', settings.ROSKOM_DOWNLOAD_INTERVAL))
		check_hour = int(read_setting('check_hour', settings.ROSKOM_CHECK_HOUR))
		print("Download interval is %d, check hour is %d" % (download_interval, check_hour))

		now = datetime.now()

		# Если нужно, осуществляем выгрузку
		if (now.hour % download_interval) == 0:
			tasks.perform_load('auto')

		# Если нужно, запускаем проверку
		if now.hour == check_hour:
			tasks.perform_scan('auto')

	print("Running under uWSGI, perfect!")
