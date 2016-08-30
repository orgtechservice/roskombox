<table class="gc_table">
	<tr>
		<th>Ссылка</th>
	</tr>
	{ foreach links as link }
		<tr>
			<td><a href="{ escape(link.link_url) }" target="_blank">{ escape(link.link_url) }</a></td>
		</tr>
	{ endeach }
</table>