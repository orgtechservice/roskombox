/**
 * Флаг фильтра, который будет отрпалвен серверу
 */
var filter = "0";

/**
 * Флаг обратной сортировки
 */
var descflag = "0";

/**
 * Подсветить заголовки, по которым установлен фильтр
 */
function setClicked()
{
	var ths = document.getElementById("0");
	for(var i = 0; i < ths.children.length; i++)
	{
		ths.children[i].style = "background-color:#CCC";
		if(filter == ths.children[i].id)
		{
			if(descflag == "0") ths.children[i].style = "background-color:#999";
			if(descflag == "1") ths.children[i].style = "background-color:#AAA";
		}
	}
}

/**
 * Установить фильтр
 */
function setFilter(event)
{
	if(filter != event.target.id)
	{
		descflag = "0";
		filter = event.target.id;
	}
	else
	{
		if(descflag == "1")
		{
			descflag = "0";
			filter = "0";
		}
		else descflag = "1";
	}
}

/**
 * Прибить все устаревшие данные
 */
function dropData()
{
	var main_data = document.getElementById("main_data");
	while(main_data.rows.length != 0)
	{
		main_data.removeChild(main_data.rows[0]);
	}
}

/**
 * Обработка клика по заголовку таблицы
 */
function onClick(event, url, id)
{
	setFilter(event);
	setClicked();
	dropData();
	ajax_post_frame("main_data", url,  { filter: filter, descflag: descflag, id: id });
}