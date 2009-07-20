<?php

abstract class SQL2Obj{
	protected $_sql_file;
	protected $_file_buffer;
	protected $_db = array();
	
	public function __construct($sql_file){
		$this->_sql_file = $sql_file;
		$this->_file_buffer = file_get_contents($sql_file);
	}
	
	public function db(){ return $this->_db; }
	
	public abstract function parseToDataObj();
}
