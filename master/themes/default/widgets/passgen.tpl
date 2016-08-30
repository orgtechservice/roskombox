<tr>
 <td><b>{escape(param.title)}</b></td>
 <td>
  <table class="gc_layout passgen">
   <tr>
    <td><input type="text" id="passgen_{escape(param.name)}" name="{escape(param.name)}" value="{escape(param.value)}" autocomplete="off" class="r_field" /></td>
    <td><img src="{INFO.PREFIX}static/tango/16x16/actions/view-refresh.png" alt="G" title="Генерировать новый" onclick="javascript:passgen_renew('passgen_{param.name}');" /></td>
   </tr>
  </table>
 </td>
</tr>
