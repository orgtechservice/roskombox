<h1>{TITLE}</h1>
<p><a href="{INFO.PREFIX}admin/roskom/add_new.php">Добавить новый домен</a></p>

<table class="gbk" cellpadding="5" cellspacing="3">
		<tr>
			<th>domain</th>
			<th>except</th>
			<th>manual</th>
			<th>status</th>
		</tr>
		{foreach domains as domain}
		<tr>
			<td>{escape(domain.domain_name)}</td>
			<td>{if domain.except=0} <a href="{INFO.PREFIX}admin/roskom/add_domain.php?list=1&amp;item={domain.domain_name}" >Добавить</a>
			{else}<a href="{INFO.PREFIX}admin/roskom/del.php?list=1&amp;item={domain.domain_name}">Удалить</a>{endif}</td>
			<td>{if domain.manual=0} <a href="{INFO.PREFIX}admin/roskom/add_domain.php?list=0&amp;item={domain.domain_name}">Добавить</a>
			{else}<a href="{INFO.PREFIX}admin/roskom/del.php?list=0&amp;item={domain.domain_name}">Удалить</a>{endif}</td>
			<td> {if domain.status=0}<p>Нет проблем с блокировкой</p>{else}<p style="color:Blue">Есть проблемы с блокировкой</p>{endif}</td>
		</tr>
		{endeach}
</table>