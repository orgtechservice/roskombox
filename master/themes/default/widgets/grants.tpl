<tr><th colspan="2">{escape(param.title)}</th></tr>
{foreach param.value as key => item}
<tr>
 <td colspan="2">
  <label for="{escape(param.name)}_{escape(key)}"><input type="checkbox" id="{escape(param.name)}_{escape(key)}" name="{escape(param.name)}[{escape(key)}]" {if item.value}checked="checked"{endif} /> {escape(item.title)}</label>
 </td>
</tr>
{endeach}
