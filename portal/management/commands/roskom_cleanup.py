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

# Python
import os
import sys
import base64
import time
from datetime import datetime
import zipfile

class Command(BaseCommand):
	args = '<"auto">'
	help = 'Maintenance script'
	db = None
	client = None
	service = None

	def process_exists(self, process):
		return os.path.exists("/proc/%d" % int(process))

	def handle(self, *args, **options):

		automatic = False
		if(len(args) > 0):
			automatic = True

		deadline = datetime.fromtimestamp(int(time.time()) - 600) # 10 минут
		open_downloads = Download.objects.filter(state = 'new', updated__lt = deadline)
		for download in open_downloads:
			if self.process_exists(download.task_pid):
				killed = 'It was killed.'
				try:
					os.kill(download.task_pid, 15)
				except:
					self.stderr.write("Failed to kill process %d, which seems to have hanged" % download.task_pid)
					killed = 'Failed to kill it.'

				download.set_failed('Process did not finish in 10 minutes. ' + killed)
			else:
				download.set_failed('Worker process crashed')

		deadline = datetime.fromtimestamp(int(time.time()) - (60 * 60 * 2)) # 2 часа
		open_scans = Scan.objects.filter(state = 'new', started__lt = deadline)
		for scan in open_scans:
			if self.process_exists(scan.task_pid):
				killed = 'It was killed.'
				try:
					os.kill(scan.task_pid, 15)
				except:
					self.stderr.write("Failed to kill process %d, which seems to have hanged" % scan.task_pid)
					killed = 'Failed to kill it.'

				scan.set_failed('Process did not finish in 10 minutes. ' + killed)
			else:
				scan.set_failed('Worker process crashed')

		self.stdout.write("Job done!")
