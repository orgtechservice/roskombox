<tr>
 <td><b>{escape(param.title)}</b></td>
 <td>
  <select name="{escape(param.name)}"  class="r_field">
   {foreach param.options as option}
   <option value="{escape(option.value)}"{if param.value = option.value} selected="selected"{endif}>{ option.title }</option>
   {endeach}
  </select>
 </td>
</tr>
