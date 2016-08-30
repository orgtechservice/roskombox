<h1>Доступ закрыт</h1>
<p>Вы не можете просматривать данную страницу так как у вас не достаточно прав.<br/>
Требуется право «{escape(require_access_title)}».</p>

{ if count(groups) > 0 }
	<h3>Зато это право есть у следующих групп:</h3>
	<ul>
		{ foreach groups as group  }
			<li><a href="{INFO.PREFIX}admin/users/group.php?id={ group.group_id }">{ escape(group.group_name) }</a></li>
		{ endeach }
	</ul>
{ else }
	<h3>Также не существует ни одной группы, обладающей данным правом.</h3>
{ endif }
