<form action="{escape(form.url)}" method="post" enctype="multipart/form-data">
 <table class="gc_table" width="100%">
  {if form.title}
  <tr>
   <th colspan="2"><h3>{form.title}</h3></th>
  </tr>
  {endif}
  {if count(form.errors) > 0}
  <tr>
   <td colspan="2">
    <h3>Ошибки</h3>
    <ul>
    {foreach form.errors as error}
     <li>{error}</li>
    {endeach}
    </ul>
   </td>
  </tr>
  {endif}
  {foreach form.params as param}
   {include param.widget}
  {endeach}
  <tr>
   <td colspan="2">
    <input type="hidden" name="action" value="{form.action}" />
    {foreach form.params as param}
     {if param.widget = "widgets/hidden"}
     <input type="hidden" name="{param.name}" value="{param.value}" />
     {endif}
    {endeach}
    <center>
     {foreach form.submits as submit}
      <input type="submit" name="{submit.name}" value="{submit.title}" class="r_button" />
     {endeach}
    </center>
   </td>
  </tr>
 </table>
</form>