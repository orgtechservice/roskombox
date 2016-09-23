#!/usr/bin/python3
# -*- coding: utf-8 -*-
# Рекомендуется Python 3.x

# Импортируем важные пакеты
import time, sys, requests

# Отключим ругань на невалидный сертификат
from requests.packages.urllib3.exceptions import InsecureRequestWarning
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

# Прикинемся браузером
request_headers = {
	'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/49.0.2623.108 Chrome/49.0.2623.108 Safari/537.36',
}

