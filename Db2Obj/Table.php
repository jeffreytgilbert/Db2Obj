<?php

class Table{
	public $name;
	public $primary_keys=array();
	public $foreign_keys=array();
	public $possible_foreign_keys=array();
	public $index_keys=array();
	public $unique_keys=array();
	public $columns=array();
	
	public function __construct($table=array()){
		foreach($table as $property => $value){ $this->$property = $value; }
	}
	
	public function toString(){
		return print_r($this->toArray(),true);
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
}
