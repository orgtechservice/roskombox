<tr>
 <td><b>{escape(param.title)}</b></td>
 <td>
 <div id="select_{escape(param.name)}_1">
  <input type="hidden" id="{escape(param.name)}" name="{escape(param.name)}" value="{escape(param.value)}" />
  <span id="select_{escape(param.name)}_title">{escape(param.options.switch_address) ", порт: " escape(param.options.port_peer_port)}</span>
  <button onclick="select_switch_port('{param.name}', event, '{escape(param.options.house_limiter)}');" class="r_button">выбрать коммутатор/порт</button>
 </div>
 <div style="display: none;" id="select_{escape(param.name)}_2"></div>
 </td>
</tr>
