# -*- coding: utf-8 -*-

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

		deadline_download = datetime.fromtimestamp(int(time.time()) - 600) # 10 минут
		deadline_scan = datetime.fromtimestamp(int(time.time()) - 3600) # 1 час

		open_downloads = Download.objects.filter(state = 'new')
		if(len(open_downloads) == 0):
			Setting.write('roskom:download_requested', '0')
		for download in open_downloads:
			if self.process_exists(download.task_pid):
				if (download.updated < deadline_download):
					killed = 'It was killed.'
					try:
						os.kill(download.task_pid, 15)
					except:
						self.stderr.write("Failed to kill process %d, which seems to have hanged" % download.task_pid)
						killed = 'Failed to kill it.'

					download.failed('Process did not finish in 10 minutes. ' + killed)
			else:
				download.failed('Worker process crashed')
				Setting.write('roskom:download_requested', '0')

		open_scans = Scan.objects.filter(state = 'new')
		if(len(open_scans) == 0):
			Setting.write('roskom:scan_requested', '0')
		for scan in open_scans:
			if self.process_exists(scan.task_pid):
				if (scan.started < deadline_scan):
					killed = 'It was killed.'
					try:
						os.kill(scan.task_pid, 15)
					except:
						self.stderr.write("Failed to kill process %d, which seems to have hanged" % scan.task_pid)
						killed = 'Failed to kill it.'

					scan.set_failed('Process did not finish in 2 hours. ' + killed)
			else:
				scan.set_failed('Worker process crashed')
				Setting.write('roskom:scan_requested', '0')

		self.stdout.write("Job done!")
