<tr>
	<td valign="top">
		<b>{escape(param.title)}</b>
	</td>
	<td valign="top">
		{ if SESSION.user.user_avatar }
			<div style="padding: 4px;">
				<img src="/static/avatars/{ SESSION.user.user_avatar }.jpg" alt="Аватар" />
			</div>
		{ endif }
		<div>
			<input type="file" name="{escape(param.name)}" class="r_field" />
		</div>
	</td>
</tr>
