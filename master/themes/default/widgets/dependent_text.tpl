<tr>
	<td>
		<label for="{escape(param.name)}_helper"><input type="checkbox" id="{escape(param.name)}_helper" name="{escape(param.name)}_helper"{if param.value}checked{endif} onchange="turn_disabled('{escape(param.name)}_value',this.checked); if (this.checked==false)document.getElementById('{escape(param.name)}_value').value='';" /> {escape(param.title)}</label>
	</td>
	<td>
		<input type="text" name="{escape(param.name)}" id="{escape(param.name)}_value" value="{escape(param.value)}" class="r_field" {if param.value=false}disabled{endif} />
	</td>
</tr>
