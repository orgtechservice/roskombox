<input type="hidden" id="{escape(param.name)}" name="{escape(param.name)}" value="{escape(param.value)}" />
<tr>
  <td><b>{escape(param.title)}</b></td>
  <td>
    <div id="select_{escape(param.name)}_1">
      <span id="select_{escape(param.name)}_title">{escape(param.options.street_name) " " escape(param.options.house_number) ", кв. " escape(param.options.client_flat) ", " escape(param.options.client_name)}</span>
      <button onclick="select_client2('{param.name}'); return false;" class="r_button">выбрать клиента</button>
    </div>
    <div style="display: none;" id="select_{escape(param.name)}_2"></div>
  </td>
</tr>
