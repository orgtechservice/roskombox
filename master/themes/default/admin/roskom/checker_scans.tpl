<h2>Последние 50 проверок</h2>

<table class="gc_table">
	<tr>
		<th>Дата/время</th>
		<th>Всего ссылок</th>
		<th>Из них доступны</th>
		<th>Процент доступных</th>
		<th>Продолжительность</th>
	</tr>
	{ foreach scans as scan }
		<tr>
			<td>{ escape(scan.scan_when_formatted) }</td>
			<td>{ escape(scan.scan_urls_total) }</td>
			<td>{ escape(scan.scan_urls_available) }</td>
			<td>{ escape(scan.scan_percent_available) }</td>
			<td>{ escape(scan.scan_time_formatted) }</td>
		</tr>
	{ endeach }
</table>
