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
from django.db import connection

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

def last_scan_stats():
	cursor = connection.cursor()
	cursor.execute("SELECT total, available, unavailable, local FROM scans ORDER BY started DESC LIMIT 1")
	row = cursor.fetchone()

	if row is None:
		return {'total': 0, 'available': 0, 'unavailable': 0, 'local': 0}
	else:
		return {'total': row[0], 'available': row[1], 'unavailable': row[2], 'local': row[3]}

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
	rrdtool.create (
		RRD_FILENAME,
		'--step', '300', # интервал 5 минут
		'DS:total_urls:GAUGE:400:U:U',
		'DS:available_urls:GAUGE:400:U:U',
		'DS:unavailable_urls:GAUGE:400:U:U',
		'DS:local_urls:GAUGE:400:U:U',
		'DS:registry_filesize:GAUGE:400:U:U',
		'DS:download_time:GAUGE:400:U:U',
		'DS:scan_time:GAUGE:400:U:U',
		'RRA:MAX:0.5:1:105408' # 366 дней в 5-минутных интервалах
	)

def update_rrd():
	stats = last_scan_stats()

	rrdtool.update (
		RRD_FILENAME,
		'N:%s:%s:%s:%s:%s:%s:%s' % (
				str(stats['total']),
				str(stats['available']),
				str(stats['unavailable']),
				str(stats['local']),
				'U', 'U', 'U'
			)
	)

def update_stats():
	if not os.path.exists(RRD_FILENAME):
		create_rrd()

	print('Updating RRD')
	try:
		update_rrd()
	except:
		create_rrd()

	render_graphs()
