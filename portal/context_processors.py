# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

from portal.models import *

from datetime import datetime

def preprocess_context(request):
	result = {}

	hide_intro = (Setting.read('hide_intro', '0') == '1')
	result.update({'hide_intro': hide_intro})

	last_update = Setting.read('roskom:update', None)
	if last_update is not None:
		result.update({'last_update': datetime.fromtimestamp(int(last_update))})

	last_scan = Scan.objects.filter(state = 'finished').first()
	result.update({'last_scan': last_scan})

	return result
