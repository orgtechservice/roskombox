<h1>{TITLE}</h1>

<p><a href="checker_add.php">Добавить проверялку</a></p>

<table class="gbk" cellpadding="5" cellspacing="3">
	<tr>
		<th>Короткое имя</th>
		<th>Описание</th>
		<th>Последняя проверка</th>
		<th>Проверено ссылок</th>
		<th>Оказались доступны</th>
	</tr>
	{ foreach checkers as checker }
		<tr>
			<td><a href="checker.php?id={ checker.checker_id }">{ escape(checker.checker_shortname) }</a></td>
			<td>{ escape(checker.checker_description) }</td>
			<td>{ escape(checker.checker_last_scan_time_formatted) }</td>
			<td>{ escape(checker.scan_urls_total) }</td>
			<td><a href="checker_avail.php?id={ checker.checker_id }">{ escape(checker.scan_urls_available) }</a></td>
		</tr>
	{endeach}
</table>