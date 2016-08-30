
<h1>{TITLE}</h1>

<p>Даты указываются в любом из форматов: дд.мм.гггг или гггг-мм-дд.</p>

<form method="get">
	<table class="gbk" cellpadding="5" cellspacing="3">
		<tr>
			<th colspan="7">Фильтр</th>
		</tr>
		<tr>
			<td>Дата от:</td>
			<td><input type="date" name="s_day" value="{start}" class="gc_field"></td>
			<td>Дата до:</td>
			<td><input type="date" name="e_day" value="{end}" class="gc_field"></td>
			<td>Уровень</td>
			<td>
				<select name="type" class="r_field">
					<option value="any"{if type = "any"} selected{endif}>Любой</option>
					<option value="info"{if type = "info"} selected{endif}>Информация</option>
					<option value="warning"{if type = "warning"} selected{endif}>Предупреждение</option>
					<option value="error"{if type = "error"} selected{endif}>Ошибка</option>
				</select>
			</td>
			<td>
				<input type="submit" value="Отобразить" class="r_button" />
			</td>
		</tr>
	</table>
</form>

<table class="gbk" cellpadding="5" cellspacing="3" width="100%">
	<tr>
		<th width="1%" nowrap="nowrap">Дата</th>
		<th width="1%" nowrap="nowrap">Статус</th>
		<th width="1%" nowrap="nowrap">Скрипт</th>
		<th>Сообщение</th>
	</tr>
	{ foreach entries as entry }
		<tr{ if entry.type = "error" } class="roskom-log-error"{ endif }{ if entry.type = "warning" } class="roskom-log-warning"{ endif }>
			<td width="1%" nowrap="nowrap">{ entry.time }</td>
			<td width="1%" nowrap="nowrap">{ entry.type }</td>
			<td width="1%" nowrap="nowrap">{ escape(entry.script) }</td>
			<td>{ escape(entry.message) }</td>
		</tr>
	{endeach}
</table>
