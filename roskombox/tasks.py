# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Python
import os, subprocess, time, random
from os.path import expanduser, isfile

# Django
from django.conf import settings

# rrdtool
import rrdtool

# Путь к интерпретатору виртуаленва
PYTHON_VENV_BIN = expanduser("~/venvs/roskombox/bin/python")

# Путь к кольцевой базе данных
RRD_FILENAME = "%s/stat.rrd" % settings.ROSKOM_CACHE_ROOT
RRD_GRAPH_FILENAME = "%s/stat.png" % settings.MEDIA_ROOT

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

	#if using_uwsgi:
	#	uwsgi.lock(0)

	command = make_command('roskom_load', mode)
		
	try:
		result = subprocess.Popen(command)
	except:
		pass

	#if using_uwsgi:
	#	uwsgi.unlock(0)

def perform_scan(mode = 'manual', blocking = False):
	os.chdir(settings.BASE_DIR)
	
	#if using_uwsgi:
	#	uwsgi.lock(0)

	command = make_command('roskom_check', mode)
		
	try:
		result = subprocess.Popen(command)
	except:
		pass

	#if using_uwsgi:
	#	uwsgi.unlock(0)

def render_graphs():
	print('Updating graph')
	end_time = int(time.time())
	start_time = end_time - (60*60*24)
	def_total_urls = 'DEF:total_urls=%s:total_urls:MAX' % RRD_FILENAME
	
	rrdtool.graph (
		RRD_GRAPH_FILENAME,
		'--border', '0',
		'-t', 'Реестр Роскомнадзора',
		'-s', str(start_time),
		'-e', str(end_time),
		def_total_urls,
		'LINE1:total_urls#AA0000:Число ссылок в реестре'
	)

def create_rrd():
	rrdtool.create(RRD_FILENAME, '--step', '300', 'DS:total_urls:GAUGE:400:U:U', 'RRA:MAX:0.5:1:600')

def update_stats():
	if not os.path.exists(RRD_FILENAME):
		create_rrd()

	print('Updating RRD')
	rrdtool.update(RRD_FILENAME, 'N:%d' % random.randint(1, 1000))

	render_graphs()
