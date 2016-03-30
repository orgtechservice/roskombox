# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Python
import os, subprocess
from os.path import expanduser, isfile

# Django
from django.conf import settings

# Путь к интерпретатору виртуаленва
PYTHON_VENV_BIN = expanduser("~/venvs/roskombox/bin/python")

def make_command(command, mode):
	if mode == 'manual':
		arg = None
	elif mode == 'web':
		arg = 'web'
	else:
		arg = 'auto'

	if isfile(PYTHON_VENV_BIN):
		if arg is None:
			return [PYTHON_VENV_BIN, 'manage.py', command]
		else:
			return [PYTHON_VENV_BIN, 'manage.py', command, arg]
	else:
		if arg is None:
			return ['./manage.py', command]
		else:
			return ['./manage.py', command, arg]

using_uwsgi = False
try:
	import uwsgi
	using_uwsgi = True
except:
	pass

def perform_load(mode = 'manual', blocking = False):
	os.chdir(settings.BASE_DIR)

	if using_uwsgi:
		uwsgi.lock(0)

	command = make_command('roskom_load', mode)
		
	try:
		result = subprocess.Popen(command)
	except:
		pass

	if using_uwsgi:
		uwsgi.unlock(0)

def perform_scan(mode = 'manual', blocking = False):
	os.chdir(settings.BASE_DIR)
	
	if using_uwsgi:
		uwsgi.lock(0)

	command = make_command('roskom_check', mode)
		
	try:
		result = subprocess.Popen(command)
	except:
		pass

	if using_uwsgi:
		uwsgi.unlock(0)
