<h1>{TITLE}</h1>
<b>|<a href="{INFO.PREFIX}admin/roskom/url.php?id={escape(order_id)}">Изменить список url</a>||
<a href="{INFO.PREFIX}admin/roskom/domain.php?id={escape(order_id)}">Изменить список доменов</a>||
<a href="{INFO.PREFIX}admin/roskom/edit.php?id={escape(order_id)}">Редактировать решение</a>||
<a href="{INFO.PREFIX}admin/roskom/orders.php">Вернуться к списку решений</a>|</b>
<form action="{INFO.PREFIX}admin/roskom/ip.php?id={escape(order_id)}" method="post">
	<p>
		<input type="submit" name="add_ip" value="Добавить ip-адрес" />
	</p>
</form>

{if flag_add}

	{form}
	
{endif}

<table class="gc_table">
	<tr>
		<th>ip_text</th>
		<th> Действия</th>
	</tr>
	{foreach ips as ip}
		<tr>
			<td>{escape(ip)}</td>
			<td><a href = "{INFO.PREFIX}admin/roskom/ip_del.php?id={escape(order_id)}&name={escape(ip)}">Удалить</a></td>
		</tr>
	{endeach}
</table>