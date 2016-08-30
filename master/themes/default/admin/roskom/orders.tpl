<h1>{TITLE}</h1>
<p><a href="{INFO.PREFIX}admin/roskom/add.php">Добавить решение суда</a></p>
<table class="gbk">
		<tr>
			<th>ID</th>
			<th>date</th>
			<th>url</th>
			<th>domain</th>
			<th>IP</th>
			<th>info</th>
			<th></th>
		</tr>
		{foreach orders as order}
		<tr>
			<td>{escape(order.order_id)}</td>
			<td>{escape(date("d.m.Y",order.order_date))}</td>
			<td>{foreach order.url as url}<a href="{escape(url)}">{escape(url)}</a>; {endeach}</td>
			<td>{foreach order.domain as domain escape(domain) "; " endeach}</td>
			<td>{foreach order.ip as ip escape(ip) "; " endeach}</td>
			<td>{escape(order.order_info)}</td>
			<td><a href="{INFO.PREFIX}admin/roskom/edit.php?id={order.order_id}">Изменить</a></td>
		</tr>
		{endeach}
</table>