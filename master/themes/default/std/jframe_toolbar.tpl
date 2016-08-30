{if count(TOOLBAR) > 0}
<div class="jframe_toolbar">
<table cellspacing="0" cellpadding="0">
	<tr>
		{foreach TOOLBAR as button}
		<td class="{if button.active}active{else}inactive{endif}"><a href="{escape(button.link)}">{escape(button.title)}</a></td>
		{endeach}
		<td width="*"></td>
	</tr>
</table>
</div>
{endif}
