<?php

// Костыль для update-скриптов
if(php_sapi_name() == 'cli') {
	$_SERVER['SERVER_NAME'] = 'localhost';
	$_SERVER['REMOTE_ADDR'] = '127.0.0.0';
}

/**
* Корень движка/сайта
*/
define('DIR_ROOT', dirname(__FILE__));

/**
* Пути поиска классов
*/
define('DIR_PATH', 'modules:/var/lib/maycloud/php-modules');

/**
* Двигатель
*/
require_once "/var/lib/maycloud/engine.php";

/**
* Каталог со скинами
*/
define('DIR_THEMES', makepath(DIR_ROOT, 'themes'));

/**
* Каталог для кеша
*/
define('DIR_CACHE', makepath(DIR_ROOT, 'cache'));

/**
* Язык по умолчанию
*/
define('LANG_DEFAULT', 'russian');

/**
* Каталог с файлами локализации
*/
define('DIR_LANGS', makepath(DIR_ROOT, 'lang'));

/**
* Каталог для описаний форм
*/
define('DIR_FORMS', DIR_ROOT);

/**
* Время жизни сессии (15 минут)
*/
define('MOD_SESSION_LIFETIME', 900);

/**
* Время действия автовхода (10 часов)
* (ровно столько, сколько нужно для одной смены)
*/
define('MOD_SESSION_AUTOLOGIN_LIFETIME', 36000);

/**
* Хост куда отправлять syslog-сообщения
*/
define('MOD_NETLOG_HOST', '127.0.0.1');

/**
* Порт на который отправлять syslog-сообщения
*/
define('MOD_NETLOG_PORT', '514');

/**
* Префикс для модуля netlog
*/
define('MOD_NETLOG_PREFIX',  uniqid()  . " $_SERVER[SERVER_NAME]: ");

/**
* Регулярное выражение для проверки корректности телефонного номера
*/
define('RE_PHONE_RUS', '#^\+?((7|8)([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2}))$#');

/**
* Загрузчик классов
*/
function __autoload($class)
{
	LightEngine::loadClass($class);
}

/**
* Общий обработчик не обработанных исключений
*
* Здесь мы ничего не знаем о типе исключения
* и как его отобразить, поэтому просто выведем
* краткое сообщение
*/
function general_exception_handler($e)
{
	header('Content-type: text/html; charset=utf-8');
	$message = get_class($e) . ": " . $e->getMessage();
	//$message = "";
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Непредвиденная ошибка</title>
		<style>
			body { font-family: Tahoma, Verdana, Arial, sans-serif; }
			body { background: white; color: black; }
			h1 { font-size: 16pt; }
			p { font-size: 12pt; }
			#alter_report_button { cursor:pointer; float:right; font-size:smaller; }
			#trace_block { text-align:left; display: inline-block; border:1px solid silver; border-radius:8px; padding:0 20px; background-color:#f5f5f5; }
		</style>
	</head>
	<body>
		<div style="text-align: center; margin: 2%;">
			<img src="/static/css/fatalerror.png" alt="" title="" width="300" height="434" />
			<h1>Непредвиденная ошибка</h1>
			<p>
				Если ошибка повторяется сообщите о ней администратору сети<br />
				тел.: 8 (8772) 55-13-19, <a href="mailto:support@mkpnet.ru">support@mkpnet.ru</a><br />
				<br />
				<?php echo htmlspecialchars($message) . "<br />";
				    if (method_exists($e,"getDriverMessage")) { echo htmlspecialchars($e->getDriverMessage()) . "<br />"; }
				    echo "Файл: " . htmlspecialchars($e->getFile());
				    echo ':' . htmlspecialchars($e->getLine()); ?>
				    <br /><br />
					<div id="trace_block">
						<p onclick="toggle(this);" style="cursor: pointer;">Техническая информация</p>
						<div style="display: none;">
							<div id="alter_report_button">Trace/Exception</div>
							<div><h3>Trace</h3><pre><?php print_r($e->getTrace());?></pre></div>
							<div style="display: none;"><h3>Exception Object</h3><pre><?php print_r($e);?></pre></div>
						</div>
					</div>
				    <script>
					function toggle(obj) {
						if ( obj.nextElementSibling.style.display == '' ) {
							obj.nextElementSibling.style.display='none';
							obj.style.borderBottom = "1px none";
							obj.style.fontWeight = "";
						}
						else {
							obj.nextElementSibling.style.display='';
							obj.style.borderBottom = "1px solid";
							obj.style.fontWeight = "bold";
						}
					}

					document.getElementById('alter_report_button').onclick = alterReport(document.getElementById('alter_report_button'));

					function alterReport(obj) {
						var trace = obj.nextElementSibling;
						var excep = trace.nextElementSibling;
						var flag = 'trace';

						return function() {
							if (flag == 'trace') {
								trace.style.display = 'none';
								excep.style.display = '';
								flag = 'excep';
							} else {
								excep.style.display = 'none';
								trace.style.display = '';
								flag = 'trace';
							}
						}
					}
				    </script>
			</p>
		</div>
	</body>
</html>
<?php
	exit;
}

/**
* Обработчик не обработанных исключений
*/
function exception_handler($e)
{
	if ( is_a($e, 'eviewable') )
	{
		global $engine;
		echo $e->render($engine);
		exit;
	}
	general_exception_handler($e);
}

set_exception_handler("exception_handler");

header('Content-type: text/html; charset=utf-8');

if(version_compare(PHP_VERSION, '5.6.0') >= 0) {
	// Подробности тут: http://php.net/manual/ru/ini.core.php#ini.default-charset
	ini_set('default_charset', 'UTF-8');
} else {
	ini_set('mbstring.internal_encoding','UTF-8');
}
