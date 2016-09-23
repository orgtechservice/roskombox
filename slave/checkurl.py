#!/usr/bin/python3
# -*- coding: utf-8 -*-
# Рекомендуется Python 3.x

# Импортируем важные пакеты
import sys, requests

# Отключим ругань на невалидный сертификат
from requests.packages.urllib3.exceptions import InsecureRequestWarning
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

# Прикинемся браузером
request_headers = {
	'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/49.0.2623.108 Chrome/49.0.2623.108 Safari/537.36',
}

# http://stackoverflow.com/questions/22492484/how-do-i-get-the-ip-address-from-a-http-request-using-the-requests-library
try:
	from requests.packages.urllib3.connectionpool import HTTPConnectionPool
except:
	print("Sadly, your version of Requests is too old.\nTry using the version from OTS repo: http://doc.mkpnet.ru/admin/deb/index.html")
	sys.exit(-1)

# Новый метод, который мы обезьянним методом воткнём вместо старого
def _make_request(self, conn, method, url, **kwargs):
	response = self._old_make_request(conn, method, url, **kwargs)
	sock = getattr(conn, 'sock', False)
	if sock:
		setattr(response, 'peer', sock.getpeername())
	else:
		setattr(response, 'peer', None)
	return response

# Осуществляем подмену
HTTPConnectionPool._old_make_request = HTTPConnectionPool._make_request
HTTPConnectionPool._make_request = _make_request

try:
	url = sys.argv[1]
except:
	print('Usage: checkurl <URL to check>')
	sys.exit(-1)

try:
	response = requests.get(url, timeout = 3, stream = True, headers = request_headers)
	content = response.raw.read(100000, decode_content = True)
	
	if b'eais.rkn.gov.ru' in content:
		print('Blocked')
	else:
		peer = response.raw._original_response.peer
		if peer is not None:
			address = peer[0]
			if address.startswith('127') or address.startswith('192.168') or address.startswith('10.10') or address == '::1' or address is None:
				print('Local IP address')
			else:
				print('Available')
		else:
			print('Available')
except:
	print('Connection failed (probably blocked)')
