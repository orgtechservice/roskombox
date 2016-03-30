# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# WSGI-файл для развёртывания, развёртывание другими способами не допускается!

# Системные импорты
import os, sys, subprocess, signal, threading

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

	@cron(0, -2, -1, -1, -1, target = 'mule') # Каждые 2 часа
	def roskom_load(num):
		print("Running roskom_load")
		tasks.perform_load('auto')
		print("roskom_load finished")

	@cron(-2, -1, -1, -1, -1, target = 'mule')
	def roskom_cleanup(num):
		print("Running roskom_cleanup")
		uwsgi.lock(0)
		call_command('roskom_cleanup')
		uwsgi.unlock(0)
		print("roskom_cleanup finished")

	@cron(0, 3, -1, -1, -1, target = 'mule')
	def roskom_check(num):
		print("Running roskom_check")
		tasks.perform_scan('auto')
		print("roskom_check finished")

	print("Running under uWSGI, perfect!")
