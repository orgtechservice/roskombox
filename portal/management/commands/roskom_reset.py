# -*- coding: utf-8 -*-

# Django
from django.core.management.base import BaseCommand, CommandError
from django.core.management import call_command
from django.db import models
from django.conf import settings
from django.utils import six
from django.utils.six.moves import input

from portal.models import *
from portal.utils import *

# Python
import os, sys, shutil, re

FILE_LIST = (
	'cache/dump.xml',
	'cache/dump.xml.sig',
	'cache/registry.zip',
)

MEDIA_LIST = (
	'media/auth_files',
)

class Command(BaseCommand):
	args = None
	help = 'Reset Roskombox application'
	db = None
	client = None
	service = None

	def process_exists(self, process):
		return os.path.exists("/proc/%d" % int(process))

	def handle(self, *args, **options):
		confirm = input('Do you really want to reset this Roskombox instance to default state? [y/n]: ')
		if confirm != 'y':
			self.stdout.write('Operation cancelled')
			exit(-1)

		# Очистить базу данных
		call_command('flush', '--noinput')

		# Удалить ставшие ненужными файлы из кеша
		for file in FILE_LIST:
			try:
				file = os.path.join(settings.BASE_DIR, file)
				os.remove(file)
			except:
				self.stderr.write("Failed to delete file %s. Already deleted?" % file)

		# Удалим подпапки из каталога media
		for directory in MEDIA_LIST:
			try:
				directory = os.path.join(settings.BASE_DIR, directory)
				shtool.rmtree(directory)
			except:
				self.stderr.write("Failed to delete dir %s. Already deleted?" % directory)

		# Удалим загруженные ключи SSH
		reset_ssh_keys()

		self.stdout.write('Operation completed')
