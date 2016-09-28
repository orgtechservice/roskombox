#!/usr/bin/python3
# -*- coding: utf-8 -*-
# Рекомендуется Python 3.x

"""
Я старался как следует прокомментировать основные моменты, чтобы в случае необходимости
в моём коде мог разобраться сотрудник, незнакомый с Python
© Илья <WST>
"""

# Импортируем важные пакеты
import time, sys, threading, requests

# Время начала работы скрипта
execution_start = time.time()

# Расставим затычки-мьютексы
in_mutex = threading.Lock()
out_mutex = threading.Lock()

# Прикинемся браузером
request_headers = {
	'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/49.0.2623.108 Chrome/49.0.2623.108 Safari/537.36',
}

# Счётчик обработанных ссылок (для отображения прогресса)
counter = 0

# http://stackoverflow.com/questions/22492484/how-do-i-get-the-ip-address-from-a-http-request-using-the-requests-library
try:
	from requests.packages.urllib3.connectionpool import HTTPConnectionPool

	# Отключим ругань на невалидный сертификат
	from requests.packages.urllib3.exceptions import InsecureRequestWarning
	requests.packages.urllib3.disable_warnings(InsecureRequestWarning)
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

# Наш воркер
class Worker(threading.Thread):
	def __init__(self, thread_id, in_data, out_data, trace):
		threading.Thread.__init__(self),
		self.thread_id = thread_id
		self.in_data = in_data
		self.out_data = out_data
		self.timeout = 3
		self.total_count = len(in_data)
		self.trace = trace

	def select_unprocessed(self):
		with in_mutex:
			try:
				result = self.in_data.pop()
			except:
				result = None
			return result

	def report_progress(self, item):
		global counter
		counter += 1
		print(u"(%d of %d) [%s] %s" % (counter, self.total_count, item[2], item[1]))

	def process_item(self, item):
		global request_headers
		item[4] = int(time.time())

		try:
			response = requests.get(item[1], timeout = self.timeout, stream = True, headers = request_headers)
			content = response.raw.read(100000, decode_content = True)

			if b'eais.rkn.gov.ru' in content:
				item[2] = 'blocked'
			else:
				peer = response.raw._original_response.peer
				if peer is not None:
					address = peer[0]
					if address.startswith('127') or address.startswith('192.168') or address.startswith('10.10') or address == '::1' or address is None:
						item[2] = 'local-ip'
						item[3] = ''
					else:
						item[2] = 'available'
						#item[3] = content
						item[3] = ''
				else:
					item[2] = 'available'
					#item[3] = content
					item[3] = ''
		except Exception as e:
			item[2] = 'failure'

		with out_mutex:
			if self.trace:
				self.report_progress(item)
			self.out_data.append(item)

	def set_timeout(self, new_timeout):
		self.timeout = new_timeout

	def run(self):
		while True:
			item = self.select_unprocessed()
			if item is None:
				break
			else:
				self.process_item(item)

try:
	import mysql.connector as MySQLdb
except:
	print("mysql.connector is required, other drivers are not supported")
	sys.exit(-1)

# Импортируем конфигурацию
import config

# Профилирование
import resource

# Получение ID проверялки из базы данных
def get_instance_id(api = False):
	timestamp = int(time.time())
	instance_id = 0
	cursor.execute("SELECT checker_id, checker_force_scan, checker_state, checker_last_scan_time, checker_enabled FROM roskom_checkers WHERE REPLACE(checker_ip, '127.0.0.1', 'localhost') = (SELECT SUBSTRING_INDEX(host, ':', 1) FROM information_schema.processlist WHERE ID = connection_id()) FOR UPDATE")
	rows = cursor.fetchall()
	if len(rows) != 1:
		print("This checker instance is not registered within lanbill database. Register this checker first.")
		sys.exit(-1)
	else:
		force_scan = int(rows[0][1])
		checker_state = rows[0][2]
		checker_last_scan_time = int(rows[0][3])
		checker_enabled = rows[0][4]

		if checker_enabled != 'yes':
			print("Checker is turned off in the settings section")
			sys.exit(0)

		if api:
			if force_scan == 0:
				sys.exit(0)
		else:
			if force_scan == 1:
				print("There is an unfinished scheduled scan")
				sys.exit(0)

		if checker_state == 'scanning':
			# В этом месте существует вероятность, что по факту проверка не выполняется, т.е скрипт упал или что-то такое, возможно, стоит ругаться об этом в лог, если последняя проверка была слишком давно
			# Форсируем проверку, если есть подозрение на такую ситуацию
			if checker_last_scan_time > (timestamp - 3600):
				print("Already scanning")
				sys.exit(0)

		instance_id = int(rows[0][0])
		cursor.execute("UPDATE roskom_checkers SET checker_state = 'scanning', checker_force_scan = 0, checker_last_scan_time = %s WHERE checker_id = %s", (timestamp, instance_id))

	return instance_id

# Соединение с БД
def connect_db():
	try:
		db = MySQLdb.connect(charset = 'utf8', use_unicode = True, **config.DATABASE)
		return db
	except Exception as e:
		print("MySQL connection failure\n%s" % str(e))
		print("Edit /opt/roskomcheck/config.py to configure access to lanbill database")
		sys.exit(-1)

def log_message(message, level = 'info'):
	timestamp = int(time.time())
	cursor.execute("INSERT INTO roskom_log (log_time, log_message, log_type, log_script_name) VALUES (%s, %s, %s, 'roskomcheck.py')", (timestamp, message, level))
	db.commit()

trace = True
api = False
if len(sys.argv) >= 2:
	if sys.argv[1] == 'cron':
		trace = False

	if sys.argv[1] == 'api':
		api = True

# Установим соединение с БД
db = connect_db()
#db.begin()
cursor = db.cursor(buffered = True)
# Проверим, зарегистрирован ли наш экземпляр в админке. Если нет, нам незачем работать
instance_id = get_instance_id(api)
db.commit()

log_message("instance #%d started using %d threads" % (instance_id, config.THREADS))

try:
	# Получим список URL-ок, доступность которых нам требуется проверить
	cursor.execute("SELECT url_text FROM roskom_url")
	in_data = [[0, i[0], 'unknown', '', 0] for i in cursor.fetchall()]
	out_data = []
except KeyboardInterrupt:
	cursor.execute("UPDATE roskom_checkers SET checker_state = 'idle', checker_force_scan = 0 WHERE checker_id = %s", (instance_id,))
	log_message("instance #%d aborted by signal" % (instance_id,))
	print('Exitting requested')
	exit(0)
finally:
	# Можно отсоединиться от БД на время анализа
	cursor.close()
	db.close()

try:
	# Инициализируем наши рабочие потоки
	threads = {}
	for i in range(config.THREADS):
		threads[i] = Worker(i, in_data, out_data, trace)
		threads[i].set_timeout(config.HTTP_TIMEOUT)
		threads[i].setDaemon(True)

	# Разветвляемся
	for index, thread in threads.items():
		thread.start()

	# Соединяемся
	for index, thread in threads.items():
		thread.join()
except KeyboardInterrupt:
	# Нас прервали нажатием клавиш ctrl-c, желательно как следует подтереться
	db = connect_db()
	cursor = db.cursor()
	cursor.execute("UPDATE roskom_checkers SET checker_state = 'idle', checker_force_scan = 0 WHERE checker_id = %s", (instance_id,))
	log_message("instance #%d aborted by signal" % (instance_id,))
	db.commit()
	cursor.close()
	db.close()

	print('Exitting requested')
	exit(0)

# На этом этапе у нас сформирована статистика в массиве out_data, получим данные для внесения в БД
timestamp = int(time.time())
total = len(out_data)
available = [i for i in out_data if i[2] == 'available']
unavailable = [i for i in out_data if i[2] in ['blocked', 'failure', 'local-ip']]

# Предварительная оценка ресурсов для записи в лог
stat = resource.getrusage(resource.RUSAGE_SELF)

# Время окончания работы скрипта, не считая финальную транзакцию
execution_end = time.time()
execution_time = execution_end - execution_start
execution_minutes = int(execution_time / 60)
execution_seconds = (execution_time - (execution_minutes * 60))

# Установим соединение с БД для сохранения результатов
db = connect_db()
cursor = db.cursor()
cursor.execute("INSERT INTO roskom_checker_scans (scan_checker_id, scan_when, scan_urls_total, scan_urls_available, scan_urls_unavailable, scan_time) VALUES (%s, %s, %s, %s, %s, %s)", (instance_id, timestamp, total, len(available), len(unavailable), int(execution_time)))
cursor.execute("UPDATE roskom_checkers SET checker_last_scan_time = %s, checker_last_scan_id = %s, checker_state = 'idle', checker_force_scan = 0 WHERE checker_id = %s", (timestamp, cursor.lastrowid, instance_id))
cursor.execute("DELETE FROM roskom_available_links WHERE link_checker_id = %s", (instance_id,))

for link in available:
	# Внесём ссылку в список доступных при последней проверке
	cursor.execute("INSERT INTO roskom_available_links (link_url, link_body, link_checker_id) VALUES (%s, %s, %s)", (link[1], link[3], instance_id))

	# Обновим статистику в roskom_url_stat
	cursor.execute("UPDATE roskom_url_stat SET us_ptime = %s, us_checker_id = %s, us_pcount = us_pcount + 1 WHERE us_hash = MD5(%s)", (link[4], instance_id, link[1]))

log_message("instance #%d finished, taking %d kb RES and %dm %.2fs" % (instance_id, stat.ru_maxrss, execution_minutes, execution_seconds))
db.commit()
cursor.close()
db.close()

if trace:
	# Выведем статистику проверки
	print("\n Scan report")
	print(" Total: %d\n Unavailable: %d\n Available: %d\n" % (total, len(unavailable), len(available)))

if config.ENABLE_LOG:
	with open(config.LOG, "w") as handle:
		handle.write("\n".join([i[1] for i in available]))
		handle.write("\n")
