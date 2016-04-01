# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Django
from django.core.management.base import BaseCommand, CommandError
from django.db import models
from django.conf import settings

from portal.models import *
from portal.utils import *

# Python
import sys, base64, signal, time, zipfile

# SUDS
from suds.client import Client
from suds.sax.text import Text

class RoskomAPI:
	def __init__(self, url, request_xml, request_xml_sign):
		self.url = url
		self.request_xml = base64.b64encode(request_xml).decode('utf-8')
		self.request_xml_sign = base64.b64encode(request_xml_sign).decode('utf-8')

		self.client = Client(url)
		self.service = self.client.service

	def getLastDumpDate(self):
		return self.service.getLastDumpDate()

	def sendRequest(self):
		response = self.service.sendRequest(self.request_xml, self.request_xml_sign, '2.2')
		return dict(((k, v.encode('utf-8')) if isinstance(v, Text) else (k, v)) for (k, v) in response)

	def getResult(self, code):
		response = self.service.getResult(code)
		return dict(((k, v.encode('utf-8')) if isinstance(v, Text) else (k, v)) for (k, v) in response)

class Command(BaseCommand):
	args = '<"auto">'
	help = 'Download registry from RSOC'
	db = None
	client = None
	service = None
	code = None
	download = None
	api = None

	def handle_signal(self, signum, frame):
		print("Exitting on user's request")
		if self.download is None:
			exit(0)
		else:
			self.download.failed('Worker received a TERM/QUIT/INT signal')
			exit(0)

	def handle_exception(e):
		Setting.write('roskom:download_requested', '0')
		self.download.failed(str(e))
		self.stderr.write(str(e))
		exit(-1)

	def handle(self, *args, **options):

		mode = 'manual'
		if(len(args) > 0):
			mode = args[0]
			if mode not in ['auto', 'web']:
				mode = 'manual'

		# Set the signal handler and a 5-second alarm
		signal.signal(signal.SIGTERM, self.handle_signal)
		signal.signal(signal.SIGQUIT, self.handle_signal)
		signal.signal(signal.SIGINT, self.handle_signal)

		if mode == 'auto':
			self.download = Download.automatic()
		elif mode == 'web':
			self.download = Download.web()
		else:
			self.download = Download.manual()
		
		# Удостоверимся, что файлы запроса и подписи загружены
		request_xml = AuthFile.load('request.xml')
		request_xml_sign = AuthFile.load('request.xml.sign')
		if (request_xml is None) or (request_xml_sign is None):
			self.stderr.write("request.xml and/or request.xml.sign not provided")
			exit(-1)

		# Подключимся к API
		if settings.ROSKOM_TEST_MODE:
			url = 'http://vigruzki.rkn.gov.ru/services/OperatorRequestTest/?wsdl'
		else:
			url = 'http://vigruzki.rkn.gov.ru/services/OperatorRequest/?wsdl'

		try:
			self.stdout.write("Connecting to the API")
			self.api = RoskomAPI(url, request_xml, request_xml_sign)
			self.stdout.write("API connection succeeded")
		except Exception as e:
			self.handle_exception(e)

		# Фактическая и записанная даты, можно сравнивать их и в зависимости от этого делать выгрузку, но мы сделаем безусловную
		try:
			dump_date = int(int(self.api.getLastDumpDate()) / 1000)
			our_last_dump = int(Setting.read('roskom:lastDumpDate', '0'))

			if dump_date > our_last_dump:
				self.stdout.write("New registry dump available, proceeding")
				Setting.write('roskom:lastDumpDate', str(dump_date))
			else:
				self.stdout.write("No changes in dump.xml, but forcing the process")
		except Exception as e:
			self.handle_exception(e)

		try:
			self.stdout.write("Sending request")
			response = self.api.sendRequest()
			self.stdout.write("Request sent")
			self.code = response['code'].decode('utf-8')
			self.download.set_code(self.code)
		except Exception as e:
			self.handle_exception(e)

		# Разблокируем кнопку, теперь её будет блокировать само наличие выгрузки
		Setting.write('roskom:download_requested', '0')
		
		while True:
			self.stdout.write("Waiting 30 seconds")
			time.sleep(30)
			self.stdout.write("Checking result")
			result = None
			try:
				result = self.api.getResult(self.code)
			except Exception as e:
				self.handle_exception(e)

			if (result is not None) and ('result' in result) and result['result']:
				try:
					self.stdout.write("Got proper result, writing zip file")
					filename = "%s/registry.zip" % settings.ROSKOM_CACHE_ROOT
					zip_archive = result['registerZipArchive']
					data = base64.b64decode(zip_archive)
					with open(filename, 'wb') as file:
						data = base64.b64decode(zip_archive)
						file.write(data)

					self.stdout.write("ZIP file saved")

					with zipfile.ZipFile(filename, 'r') as file:
						file.extractall(settings.ROSKOM_CACHE_ROOT)
					
					self.stdout.write("ZIP file extracted")
					Setting.write('roskom:update', int(time.time()))
					self.download.succeeded(len(data))
					self.stdout.write("Job done!")
					break
				except Exception as e:
					self.handle_exception(e)
			else:
				try:
					if result['resultComment'].decode('utf-8') == 'запрос обрабатывается':
						self.stdout.write("Still not ready")
						continue
					else:
						error = result['resultComment'].decode('utf-8')
						self.download.failed(error)
						self.stderr.write("getResult failed with code %d: %s" % (result['resultCode'], error))
						exit(-1)
				except Exception as e:
					self.handle_exception(e)
