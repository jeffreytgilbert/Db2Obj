<?php
class Column{
	public $name;
	private $type;
	private $integer_size;
	private $decimal_size;
	private $attributes;
	public $default; // nice to have -- unused
	public $collation; // nice to have -- unused
	public $nullable = true;
	public $primary = false;
	public $unique = false;
	public $index = false;
	public $fulltext = false;
	public $auto_increment = false;
	public $comments = '';
	
	public function setType($type){
		$type = strtolower($type);
		$supported_types = array(
			'integer',
			'unsigned integer',
			'unsigned zerofill integer',
			'string',
			'float',
			'double',
			'boolean',
			'binary',
			'bit',
			'timestamp',
			'datetime',
			'date');
		if(in_array($type, $supported_types)){
			$this->type = $type;
		} else {
			throw(new Exception('Invalid type specified. Supported types: '.implode(', ',$supported_types).'.'));
		}
	}
	public function getType(){
		return $this->type;
	}
	
	// remove this and blend it with type
	public function setAttributes($attrs){
		$attrs = strtolower($attrs);
		$supported_attrs = array(
			'unsigned',
			'unsigned zerofill',
			'binary',
			null);
		if(in_array($attrs, $supported_attrs)){
			$this->attributes = $attrs;
		} else {
			throw(new Exception('Invalid attributes specified. Supported attributes: '.implode(', ',$supported_attrs).'.'));
		}
	}
	public function getAttributes(){
		return $this->attributes;
	}
	
	public function setSize($integer, $decimal=null){
		$this->integer_size = $integer;
		$this->decimal_size = $decimal;
	}
	public function getSize(){
		if(!isset($this->decimal_size) || empty($this->decimal_size)){
			return array('integer'=>$this->integer_size);
		} else {
			return array('integer'=>$this->integer_size, 'decimal_size'=>$this->decimal_size);
		}
	}
	
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
