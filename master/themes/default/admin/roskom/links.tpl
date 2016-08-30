<h1>{ escape(TITLE) }</h1>

<table class="gbk gc_table" width="100%">
	<tr>
		<th>последняя доступность</th>
		<th>ctime</th>
		<th>utime</th>
		<th>Всего доступностей</th>
		<th>Проверялка</th>
		<th>URL</th>
		<th>Домен</th>
	</tr>
	{ foreach links as link }
		<tr>
			<td>{ escape(link.us_ptime_formatted) }</td>
			<td>{ escape(link.us_ctime_formatted) }</td>
			<td>{ escape(link.us_utime_formatted) }</td>
			<td>{ link.us_pcount }</td>
			<td><a href="checker.php?id={ link.us_checker_id }">{ escape(link.checker_shortname) }</a></td>
			<td><a href="{ escape(link.us_url) }">{ escape(link.us_url) }</a></td>
			<td>{ if link.us_from_domain = 'yes' }да{ else }нет{ endif }</td>
		</tr>
	{ endeach }
</table>
