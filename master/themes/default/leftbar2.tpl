<td id="nav-leftbar" valign="top" width="200">
	{ifdef SESSION.user}
		<ul id="mainmenu">
			<li>
				<a href="{INFO.PREFIX}admin/roskom/search.php"{ if MODULE = 'roskom' } class="current"{ endif }>Роскомнадзор</a>
				{ if MODULE = 'roskom' }
					<ul>
						<li><a href="{INFO.PREFIX}admin/roskom/search.php"{ if CONTENT = 'admin/roskom/search' } class="current"{ endif }>Поиск по реестру</a></li>
						<li><a href="{INFO.PREFIX}admin/roskom/log.php"{ if CONTENT = 'admin/roskom/log' } class="current"{ endif }>Просмотр журнала</a></li>
						<li><a href="{INFO.PREFIX}admin/roskom/orders.php"{ if CONTENT = 'admin/roskom/orders' } class="current"{ endif }>Локальные решения суда</a></li>
						<li><a href="{INFO.PREFIX}admin/roskom/checkers.php"{ if CONTENT = 'admin/roskom/checkers' } class="current"{ endif }>Проверялки</a></li>
						<li><a href="{INFO.PREFIX}admin/roskom/links.php"{ if CONTENT = 'admin/roskom/links' } class="current"{ endif }>Статистика ссылок</a></li>
					</ul>
				{ endif }
			</li>
		</ul>
	{endif}
</td>
