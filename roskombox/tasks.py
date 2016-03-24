# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Python
import os, subprocess
from os.path import expanduser

# Django
from django.conf import settings

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

	if mode == 'manual':
		command = ['./manage.py', 'roskom_load']
	elif mode == 'web':
		command = ['./manage.py', 'roskom_load', 'web']
	else:
		command = ['./manage.py', 'roskom_load', 'auto']
		
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

	if mode == 'manual':
		command = ['./manage.py', 'roskom_check']
	elif mode == 'web':
		command = ['./manage.py', 'roskom_check', 'web']
	else:
		command = ['./manage.py', 'roskom_check', 'auto']
		
	try:
		result = subprocess.Popen(command)
	except:
		pass

	if using_uwsgi:
		uwsgi.unlock(0)
