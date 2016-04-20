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

# Пути к графикам
RRD_GRAPH_SCANS = "%s/scans.png" % settings.MEDIA_ROOT
RRD_GRAPH_TOTAL_URLS = "%s/total_urls.png" % settings.MEDIA_ROOT
RRD_GRAPH_REGISTRY_FILESIZE = "%s/registry_filesize.png" % settings.MEDIA_ROOT
RRD_GRAPH_TIMES = "%s/times.png" % settings.MEDIA_ROOT

# Временные интервалы
ONE_DAY = 86400
ONE_MONTH = 2592000
ONE_YEAR = 31536000

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

def last_download_stats():
	cursor = connection.cursor()
	cursor.execute("SELECT duration, filesize FROM downloads ORDER BY updated DESC LIMIT 1")
	row = cursor.fetchone()

	if row is None:
		return {'duration': 0, 'filesize': 0}
	else:
		return {'duration': row[0], 'filesize': row[1]}

def render_graphs():
	print('Updating graph')
	end_time = int(time.time())
	start_time = end_time - ONE_MONTH

	def_total_urls = 'DEF:total_urls=%s:total_urls:MAX' % RRD_FILENAME
	def_available_urls = 'DEF:available_urls=%s:available_urls:MAX' % RRD_FILENAME
	def_unavailable_urls = 'DEF:unavailable_urls=%s:unavailable_urls:MAX' % RRD_FILENAME
	def_local_urls = 'DEF:local_urls=%s:local_urls:MAX' % RRD_FILENAME
	def_registry_filesize = 'DEF:registry_filesize=%s:registry_filesize:MAX' % RRD_FILENAME
	def_download_time = 'DEF:download_time=%s:download_time:MAX' % RRD_FILENAME
	def_scan_time = 'DEF:scan_time=%s:scan_time:MAX' % RRD_FILENAME
	
	rrdtool.graph (
		RRD_GRAPH_SCANS,
		'--border', '0',
		'-t', 'Результаты проверок доступности',
		'-s', str(start_time),
		'-e', str(end_time),
		'-w', '300',
		'-h', '100',
		'-v', 'ссылок',
		def_available_urls,
		def_local_urls,
		'LINE1:available_urls#AA0000:Число доступных URL',
		'LINE1:local_urls#AAAA00:Число резолвящихся в локальные IP'
	)

	rrdtool.graph (
		RRD_GRAPH_TOTAL_URLS,
		'--border', '0',
		'-t', 'Общее число ссылок в реестре',
		'-s', str(start_time),
		'-e', str(end_time),
		'-w', '300',
		'-h', '100',
		'-v', 'ссылок',
		def_total_urls,
		'AREA:total_urls#00AA00:Ссылок в реестре',
	)

	rrdtool.graph (
		RRD_GRAPH_REGISTRY_FILESIZE,
		'--border', '0',
		'-t', 'Объём выгрузки в байтах',
		'-s', str(start_time),
		'-e', str(end_time),
		'-w', '300',
		'-h', '100',
		'-v', 'байт',
		def_registry_filesize,
		'AREA:registry_filesize#00AA00:Объём выгрузки',
	)

	rrdtool.graph (
		RRD_GRAPH_TIMES,
		'--border', '0',
		'-t', 'Временные затраты',
		'-s', str(start_time),
		'-e', str(end_time),
		'-w', '300',
		'-h', '100',
		'-v', 'секунд',
		def_download_time,
		def_scan_time,
		'LINE1:download_time#AA0000:Длительность выгрузки',
		'LINE1:scan_time#0000AA:Длительность проверки',
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
	scan = last_scan_stats()
	download = last_download_stats()

	rrdtool.update (
		RRD_FILENAME,
		'N:%s:%s:%s:%s:%s:%s:%s' % (
				str(scan['total']),
				str(scan['available']),
				str(scan['unavailable']),
				str(scan['local']),
				str(download['filesize']),
				'U',
				str(int(download['duration'])),
			)
	)

def update_stats():
	if not os.path.exists(RRD_FILENAME):
		create_rrd()

	print('Updating RRD')
	try:
		update_rrd()
	except:
		print('Creating RRD')
		create_rrd()

	render_graphs()
