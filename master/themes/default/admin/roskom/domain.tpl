<h1>{TITLE}</h1>
<b>|<a href="{INFO.PREFIX}admin/roskom/url.php?id={escape(order_id)}">Изменить список url</a>||
<a href="{INFO.PREFIX}admin/roskom/ip.php?id={escape(order_id)}">Изменить список ip-адресов</a>||
<a href="{INFO.PREFIX}admin/roskom/edit.php?id={escape(order_id)}">Редактировать решение</a>||
<a href="{INFO.PREFIX}admin/roskom/orders.php">Вернуться к списку решений</a>|</b>
<form action="{INFO.PREFIX}admin/roskom/domain.php?id={escape(order_id)}" method="post">
	<p>
		<input type="submit" name="add_domain" value="Добавить домен" />
	</p>
</form>

{if flag_add}

	{form}
	
{endif}

<table class="gc_table">
	<tr>
		<th>domain_text</th>
		<th>domain_sys</th>
		<th>domain_view</th>
		<th> Действия</th>
	</tr>
	{foreach domains as domain}
		<tr>
			<td>{escape(domain.domain_text)}</td>
			<td>{escape(domain.domain_sys)}</td>
			<td>{escape(domain.domain_view)}</td>
			<td><a href = "{INFO.PREFIX}admin/roskom/domain_del.php?id={escape(order_id)}&name={escape(domain.domain_text)}">Удалить</a></td>
		</tr>
	{endeach}
</table>