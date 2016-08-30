<tr>
 <td><b>{escape(param.title)}</b></td>
 <td>
 <div id="select_{escape(param.name)}_1">
  <input type="hidden" id="{escape(param.name)}" name="{escape(param.name)}" value="{escape(param.value)}" />
  <span id="select_{escape(param.name)}_title">{escape(param.options.street_name) " " escape(param.options.house_number)}</span>
  <button onclick="return select_house('{param.name}', event);" class="r_button">выбрать дом</button>
 </div>
 <div style="display: none;" id="select_{escape(param.name)}_2"></div>
 </td>
</tr>
