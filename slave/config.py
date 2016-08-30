# -*- coding: utf-8 -*-

# Число потоков
THREADS = 100

# Параметры для подключения к БД
DATABASE = {
	'host': 'localhost',
	'user': 'roskom',
	'passwd': 'roskom123456',
	'db': 'roskom_db',
}

# Таймаут подключения в секундах
HTTP_TIMEOUT = 2

# Журнал доступных URL
ENABLE_LOG = True
LOG = '/tmp/available-links.txt'
