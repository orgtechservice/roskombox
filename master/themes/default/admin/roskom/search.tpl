<h1>{TITLE}</h1>

<form method="post">
	<table class="gbk" cellpadding="5" cellspacing="3" width="600">
		<tr>
			<th colspan="3">Домен для поиска</th>
		</tr>
		<tr>
			<td width="1%" nowrap="nowrap">Домен:</td>
			<td><input type="text" name="domain" class="r_field" value="{ domain }" /></td>
			<td width="1%" nowrap="nowrap"><input class="r_button" type="submit" value="Найти" /></td>
		</tr>
	</table>
</form>

{ if domain }
	<!--
	<h2>Информация об IP-адресах { domain }</h2>
	<p>Домен резолвится в следующие IP-адреса:</p>
	<ul>
		{ foreach ips as ip }
			<li>{ ip.address } ({ ip.status })</li>
		{ endeach }
	</ul>
	-->
	<h2>Присутствие в реестре заблокированных доменов РКН</h2>
	<ul>
		{ foreach blocked as block }
			<li>{ block }</li>
		{ endeach }
	</ul>

	<h2>Присутствие в реестре заблокированных URL РКН</h2>
	<ul>
		{ foreach urls as block }
			<li>{ block }</li>
		{ endeach }
	</ul>

	<h2>Присутствие среди доменов, заблокированных по местным решениям</h2>
	<ul>
		{ foreach local_blocked as block }
			<li>{ block }</li>
		{ endeach }
	</ul>

	<h2>Присутствие среди URL, заблокированных по местным решениям</h2>
	<ul>
		{ foreach local_urls as block }
			<li>{ block }</li>
		{ endeach }
	</ul>

	<h2>Присутствие среди неблокируемых URL за последние 24 часа</h2>
	<ul>
		{ foreach unblocked as url }
			<li>{ url }</li>
		{ endeach }
	</ul>

	<h2>Ручная блокировка и принудительное исключение</h2>

	<table class="gbk" cellpadding="5" cellspacing="3" width="400">
		<tr>
			<th align="center" width="50%">Блокировка</th>
			<th width="50%">Исключение</th>
		</tr>
		<tr>
			<td align="center">
				{ if manually_blocked }
					<b>Заблокирован</b>
				{ else }
					Не заблокирован
				{ endif }
			</td>
			<td align="center">
				{ if manually_excepted }
					<b>Добавлен</b>
				{ else }
					Не добавлен
				{ endif }
			</td>
		</tr>
		<tr>
			<td align="center">
				{ if manually_blocked }
					<form method="post" action="manual.php">
						<input type="hidden" name="action" value="delete-block" />
						<input type="hidden" name="domain" value="{ domain }" />
						<input type="submit" class="r_button" value="Разблокировать" />
					</form>
				{ else }
					<form method="post" action="manual.php">
						<input type="hidden" name="action" value="add-block" />
						<input type="hidden" name="domain" value="{ domain }" />
						<input type="submit" class="r_button" value="Заблокировать" />
					</form>
				{ endif }
			</td>
			<td align="center">
				{ if manually_excepted }
					<form method="post" action="manual.php">
						<input type="hidden" name="action" value="delete-exception" />
						<input type="hidden" name="domain" value="{ domain }" />
						<input type="submit" class="r_button" value="Удалить исключение" />
					</form>
				{ else }
					<form method="post" action="manual.php">
						<input type="hidden" name="action" value="add-exception" />
						<input type="hidden" name="domain" value="{ domain }" />
						<input type="submit" class="r_button" value="Добавить исключение" />
					</form>
				{ endif }
			</td>
		</tr>
	</table>
{ endif }
