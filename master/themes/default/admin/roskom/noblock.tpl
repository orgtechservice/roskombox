
<h1>{TITLE}</h1>

<table class="gbk" cellpadding="5" cellspacing="3">
	<tr>
		<th>Адрес</th>
		<th>IP</th>
	</tr>
	{ foreach urls as url }
		<tr>
			<td><a href="{ url.address }">{ url.address }</a></td>
			<td>{ url.ip }</td>
		</tr>
	{endeach}
</table>
