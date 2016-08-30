# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

from django.db import models, connection
from django.utils import timezone
from django.forms import ValidationError
from django.conf import settings
from django.core.urlresolvers import reverse
from django.core.mail import send_mail
from django.db.models.signals import post_save
from django.core.validators import MaxValueValidator, MinValueValidator

# Python core
import base64, re, time, os
from datetime import datetime

# Работаем ли мы в тестовом режиме
roskom_debug = settings.ROSKOM_TEST_MODE

url_re = re.compile(r'^https?://([^\)]+)')

class AuthFile(models.Model):
	FILENAME_CHOICES = (('request.xml', 'request.xml'), ('request.xml.sign', 'request.xml.sign'))
	
	filename = models.CharField(u'Имя файла', blank = False, null = False, choices = FILENAME_CHOICES, unique = True, max_length = 64)
	data = models.FileField(u'Файл', blank = False, null = False, upload_to = 'auth_files')

	def __str__(self):
		return self.get_filename_display()

	@staticmethod
	def exists(filename):
		try:
			item = AuthFile.objects.get(filename = filename)
			return True
		except:
			return False

	@staticmethod
	def load(filename):
		try:
			item = AuthFile.objects.get(filename = filename)
			return item.data.read()
		except:
			return None

	class Meta:
		db_table = 'auth_files'
		managed = True

class Setting(models.Model):
	name = models.CharField(u'Имя параметра', blank = False, null = False, unique = True, max_length = 64)
	value = models.CharField(u'Значение', blank = True, null = False, max_length = 255)

	def __str__(self):
		return self.name

	@staticmethod
	def read(name, default = None):
		try:
			item = Setting.objects.get(name = name)
			return item.value
		except:
			return default

	def write(name, value):
		try:
			item = Setting.objects.get(name = name)
			item.value = value
			item.save()
		except:
			item = Setting(name = name, value = value)
			item.save()

	class Meta:
		db_table = 'settings'
		managed = True

class Download(models.Model):
	STATE_CHOICES = (('new', 'Выполняется'), ('got-response', 'Выполнена'), ('failed', 'Ошибка'))
	MODE_CHOICES = (('automatic', 'Автоматическая'), ('manual', 'Ручная'), ('web', 'Веб'))

	code = models.CharField(u'Код', blank = True, null = True, unique = True, max_length = 40)
	created = models.DateTimeField(u'Создана', null = False, auto_now_add = True)
	updated = models.DateTimeField(u'Обновлена', null = False, auto_now = True)
	state = models.CharField(u'Статус', max_length = 15, null = False, blank = False, default = 'new', choices = STATE_CHOICES)
	mode = models.CharField(u'Тип', max_length = 15, null = False, blank = False, default = 'automatic', choices = MODE_CHOICES)
	debug = models.BooleanField(u'Отладка', default = False)
	error = models.CharField(u'Ошибка', blank = True, null = False, default = '', max_length = 255)
	duration = models.FloatField(u'', null = False, blank = False, default = 0.0)
	filesize = models.IntegerField(u'Объём', null = False, blank = False, default = 0)
	task_pid = models.IntegerField(u'PID', null = False, blank = False, default = 0)

	start_time = 0.0

	def duration_formatted(self):
		return "%.2f секунд" % self.duration

	def get_details_url(self):
		return reverse('download-details', args = (self.id,))

	@staticmethod
	def automatic():
		item = Download(debug = roskom_debug, task_pid = os.getpid())
		item.start_time = time.time()
		item.save()
		return item

	@staticmethod
	def manual():
		item = Download(mode = 'manual', debug = roskom_debug, task_pid = os.getpid())
		item.start_time = time.time()
		item.save()
		return item

	@staticmethod
	def web():
		item = Download(mode = 'web', debug = roskom_debug, task_pid = os.getpid())
		item.start_time = time.time()
		item.save()
		return item

	def set_code(self, code):
		self.code = code
		self.save()

	def succeeded(self, filesize = 0):
		self.state = 'got-response'
		self.duration = time.time() - self.start_time
		self.filesize = filesize
		self.task_pid = 0
		self.save()

	def failed(self, error):
		self.state = 'failed'
		self.error = error
		self.task_pid = 0
		self.save()

	def __str__(self):
		if (not self.code) or (self.code is None):
			return "Выгрузка #%d" % self.id
		else:
			return self.code

	class Meta:
		db_table = 'downloads'
		managed = True
		ordering = ('-created',)

class Scan(models.Model):
	STATE_CHOICES = (('new', 'Начата'), ('failed', 'Ошибка'), ('finished', 'Завершена'))
	MODE_CHOICES = (('automatic', 'Автоматическая'), ('manual', 'Ручная'), ('web', 'Веб'))

	started = models.DateTimeField(u'Начата', null = False, auto_now_add = True)
	finished = models.DateTimeField(u'Завершена', null = True, blank = True)
	state = models.CharField(u'Статус', max_length = 15, null = False, blank = False, default = 'new', choices = STATE_CHOICES)
	mode = models.CharField(u'Тип', max_length = 15, null = False, blank = False, default = 'automatic', choices = MODE_CHOICES)

	total = models.IntegerField(u'Всего URL', null = False, blank = False, default = 0)
	unavailable = models.IntegerField(u'Недоступных URL', null = False, blank = False, default = 0)
	available = models.IntegerField(u'Доступных URL', null = False, blank = False, default = 0)
	local = models.IntegerField(u'URL с локальными IP', null = False, blank = False, default = 0)
	progress = models.PositiveIntegerField(u'Прогресс', null = False, blank = False, default = 0, validators = [MaxValueValidator(100), MinValueValidator(0)])
	task_pid = models.IntegerField(u'PID', null = False, blank = False, default = 0)
	error = models.CharField(u'Ошибка', blank = True, null = False, default = '', max_length = 255)

	@staticmethod
	def automatic():
		item = Scan(state = 'new')
		item.task_pid = os.getpid()
		item.save()
		return item

	@staticmethod
	def manual():
		item = Scan(mode = 'manual', state = 'new')
		item.task_pid = os.getpid()
		item.save()
		return item

	@staticmethod
	def web():
		item = Scan(mode = 'web', state = 'new')
		item.task_pid = os.getpid()
		item.save()
		return item

	def succeeded(self, total, available, unavailable, local):
		self.state = 'finished'
		self.finished = datetime.now()
		self.total = total
		self.available = available
		self.unavailable = unavailable
		self.local = local
		self.task_pid = 0
		self.save()

	def set_failed(self, error = ''):
		self.state = 'failed'
		self.finished = datetime.now()
		self.error = error
		self.task_pid = 0
		self.save()

	def set_progress(self, percents):
		self.progress = percents
		self.save()

	def get_duration(self):
		if self.finished is None:
			return 'не завершена'
		else:
			diff = self.finished - self.started
			hours = int(diff.seconds / 3600)
			minutes = int(diff.seconds / 60) % 60
			seconds = diff.seconds % 60
			if hours == 0:
				return "%d мин %d сек" % (minutes, seconds)
			else:
				return "%d ч %d мин %d сек" % (hours, minutes, seconds)

	def get_available_links_url(self):
		return reverse('available-links', args = (self.id,))

	def __str__(self):
		return "Проверка %d" % self.id

	def get_details_url(self):
		return reverse('scan-details', args = (self.id,))

	class Meta:
		db_table = 'scans'
		managed = True
		ordering = ('-started',)

class AvailableLink(models.Model):
	scan = models.ForeignKey(Scan, verbose_name = u'Проверка')
	url = models.TextField(u'URL')
	code = models.IntegerField(u'Код ответа', null = False, blank = False, default = 0)
	content = models.BinaryField(u'Содержимое', default = b'', blank = True, null = False)

	def __str__(self):
		m = url_re.match(self.url)
		if m is not None:
			return m.group(1)
		else:
			return self.url

	class Meta:
		db_table = 'available_links'
		managed = True

class LogEntry(models.Model):
	LEVEL_CHOICES = (('info', 'Информация'), ('warning', 'Предупреждение'), ('error', 'Ошибка'))
	MODULE_CHOICES = (('web', 'Веб-интерфейс'), ('roskom_load', 'roskom_load'), ('roskom_check', 'roskom_check'))

	level = models.CharField(u'Уровень', max_length = 16, blank = False, null = False, default = 'info')
	module = models.CharField(u'Команда', max_length = 16, blank = False, null = False, default = 'web', choices = MODULE_CHOICES)
	message = models.CharField(u'Сообщение', max_length = 1024, blank = False, null = False, default = '')
	when = models.DateTimeField(u'Дата', auto_now_add = True, blank = False, null = False)

	@staticmethod
	def add(message, module = 'web', level = 'info'):
		item = LogEntry(message = message, level = level, module = module)
		item.save()

	class Meta:
		db_table = 'log'
		managed = True
