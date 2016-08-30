<?php

/**
* Модуль для работы вкладками на HTML
*
* (с) Золотов Алексей <zolotov-alex@shamangrad.net>, 2011
*/
class mod_tabs extends LightModule
{
	private $tabs = array ();
	
	/**
	* Конструктор модуля
	* @param LightEngine менеджер модулей
	* @retval LightModule модуль
	*/
	public static function create(LightEngine $engine)
	{
		return new mod_tabs($engine);
	}
	
	/**
	* Конструктор менеджера конфигурации
	* @param LightEngine менеджер модулей
	*/
	public function __construct(LightEngine $engine)
	{
		parent::__construct($engine);
		$this->tabs = array ();
	}
	
	public function addTab($link, $title, $enable = true)
	{
		$args = func_get_args();
		$this->tabs[$link] = array(
			'type' => 'tab',
			'link' => trim("$link"),
			'title' => trim("$title"),
			'enable' => $enable
		);
	}
	
	public function render($template, $link = "")
	{
		$this->tpl->save_tags('CONTENT', 'TABS', 'TABCURRENT', 'TABCONTENT');
		$content = $this->tpl->get_tag('CONTENT');
		$this->tpl->set_tag('CONTENT', 'std/tabs');
		$this->tpl->set_tag('TABCONTENT', $content);
		$this->tpl->set_tag('TABCURRENT', trim("$link"));
		$this->tpl->set_tag('TABS', $this->tabs);
		$result = $this->tpl->render($template);
		$this->tpl->restore_tags();
		return $result;
	}
}

?>