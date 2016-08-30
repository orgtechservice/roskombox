{if TABSTITLE}<h1>{TABSTITLE}</h1>{endif}
<table class="gc_tabsheet" cellspacing="0" style="min-width: 70%;">
<tr>
	{foreach TABS as tab}
	{if tab.link = TABCURRENT}
		<th class="gc_selected">{escape(tab.title)}</th>
	{else if tab.enable}
		<th><a href="{INFO.PREFIX}{escape(tab.link)}">{escape(tab.title)}</a></th>
	{else}
		<th style="color: grey;">{escape(tab.title)}</th>
	{endif endif}
	{endeach}
</tr>
<tr>
	<td colspan="{count(TABS)}">
		{INCLUDE TABCONTENT}
	</td>
</tr>
</table>
