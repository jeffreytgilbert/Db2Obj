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
	
	public function convertToStandardSizeArray($size){
		if(isset($size) && !empty($size)){
			$size_array = explode(',',$size);
			if(count($size_array) == 2) return $size_array;
			else if(count($size_array) == 1) { $size_array[1]=null; return $size_array; }
		}
		return array(null,null);
	}
	
	public abstract function parseToDataObj();
}
