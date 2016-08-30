<?php

/**
* Модуль доступа к базе UTM5
*
* (c) Золотов Алексей <zolotov-alex@shamangrad.net>, 2011
*/
class mod_syslog extends mod_mysql
{
	public $trace = false;
	
	/**
	* Конструктор модуля
	*
	* Читает конфиг, ищет реальный драйвер БД, загружает и возращает его
	*
	* @param LightEngine менеджер модулей
	* @retval LightModule модуль
	*/
	public static function create(LightEngine $engine) {
		return new self($engine);
	}
	
	/**
	* Добавить запись в журнал
	* @param $module модуль
	* @param $message текстовое сообщение
	* @param $time время в которое было свершено действие (Unix time)
	* @param $operator_id ID оператора вызвавщего действие
	* @param $client_id ID клиента в отношении которого было свершено действие
	*/
	public function put($module, $message, $time = false, $operator_id = false, $client_id = false) {
		if(is_array($operator_id)) $operator_id = (int) @ $operator_id['user_id'];
		if($operator_id == 0) {
			$operator = $this->session->authorize();
			$operator_id = $operator['user_id'];
		}
		$this->db->insert('syslog', array(
			'syslog_time' => $this->db->quote(date('Y-m-d H:i:s', ($time !== false ? $time : time()))),
			'syslog_module' => $this->db->quote($module),
			'syslog_operator_id' => ($operator_id !== false ? intval($operator_id) : 'NULL'),
			'syslog_client_id' => ($client_id !== false ? intval($client_id) : 'NULL'),
			'syslog_message' => $this->db->quote($message)
		));
	}

	public function putAnonymously($module, $message, $time = false) {
		$police = $this->db->selectOne('users', '*', "user_login = 'police'");
		return $this->put($module, $message, $time, $police);
	}
	
	/**
	* Выбрать записи журнала по оператору
	* @param $operator_id ID оператора
	* @param $start время начала интервала (Unix time)
	* @param $end время конца интервала (Unix time)
	*/
	public function selectByOperator($operator_id, $start, $end)
	{
		$tm_start = $engine->db->quote(date('Y-m-d H:i:s', $start));
		$tm_end = $engine->db->quote(date('Y-m-d H:i:s', $end));
		$oid = intval($operator_id);
		$prefix = $this->db->prefix;
		$this->db->query("SELECT * FROM syslog
			LEFT JOIN clients ON syslog_client_id = client_id
			JOIN users ON syslog_operator_id = user_id
			WHERE syslog_operator_id = $oid AND syslog_time BETWEEN $tm_start and $tm_end
			ORDER BY syslog_time ASC");
		$result = array();
		while ( $f = $this->db->fetchAssoc($r) )
		{
			$result[] = $f;
		}
		$this->db->freeResult($r);
		return $result;
	}
	
	/**
	* Выбрать записи журнала по клиенту в отношении которого были выполнены действия
	* @param $operator_id ID оператора
	* @param $start время начала интервала (Unix time)
	* @param $end время конца интервала (Unix time)
	*/
	public function selectByClient($client_id, $start, $end)
	{
		$tm_start = $engine->db->quote(date('Y-m-d H:i:s', $start));
		$tm_end = $engine->db->quote(date('Y-m-d H:i:s', $end));
		$oid = intval($operator_id);
		$prefix = $this->db->prefix;
		$this->db->query("SELECT * FROM syslog
			JOIN users ON syslog_operator_id = user_id
			WHERE syslog_client_id = $oid AND syslog_time BETWEEN $tm_start and $tm_end
			ORDER BY syslog_time ASC");
		$result = array();
		while ( $f = $this->db->fetchAssoc($r) )
		{
			$result[] = $f;
		}
		$this->db->freeResult($r);
		return $result;
	}
	
	function trace($status, $host, $message)
	{
		if ( $this->trace )
		{
			if ( is_bool($status) ) $status = $status ? "ok" : "fail";
			echo "[$host] $message [ $status ]\n";
		}
	}
}
