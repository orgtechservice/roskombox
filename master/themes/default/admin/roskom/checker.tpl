<table class="gc_table">
	<tr>
		<td>IP-адрес</td>
		<td>{ escape(checker.checker_ip) }</td>
	</tr>
	<tr>
		<td>Краткое имя</td>
		<td>{ escape(checker.checker_shortname) }</td>
	</tr>
	<tr>
		<td>Описание</td>
		<td>{ escape(checker.checker_description) }</td>
	</tr>
	<tr>
		<td>Предельное число доступных ссылок</td>
		<td>{ escape(checker.checker_max_available) }</td>
	</tr>
</table>

<h2>Последняя проверка</h2>

<table class="gc_table">
	<tr>
		<td>Дата/время</td>
		<td>{ escape(checker.checker_last_scan_time_formatted) }</td>
	</tr>
	<tr>
		<td>Всего проверено ссылок</td>
		<td>{ escape(checker.scan_urls_total) }</td>
	</tr>
	<tr>
		<td>Из них доступных</td>
		<td>{ escape(checker.scan_urls_available) }</td>
	</tr>
</table>

<p><a href="checker_del.php?id={ checker.checker_id }">Удалить проверялку</a><br/>{ if checker.checker_state = 'scanning' }Проверка в процессе, ожидайте результат{ else }{ if checker.checker_force_scan }Проверка запланирована, ожидайте{ else }<a href="checker_run.php?id={ checker.checker_id }">Форсировать проверку</a>{ endif }{ endif }</p>
