<?php
class Column{
	public $name;
	public $type;
	public $size;
	public $default; // nice to have -- unused
	public $collation; // nice to have -- unused
	public $attributes;
	public $nullable = 'null';
	public $primary = false;
	public $unique = false;
	public $index = false;
	public $fulltext = false;
	public $auto_increment = false;
	public $comments = '';
	
	public function __construct($column=array()){
		foreach($column as $property => $value){ $this->$property = $value; }
	}
	
	public function toString(){
		return print_r($this->toArray(),true);
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
}
