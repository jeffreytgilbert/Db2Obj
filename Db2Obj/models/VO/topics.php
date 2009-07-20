<?php
final class topics {
	
	public function __construct($table=array()){
		foreach($table as $property => $value){ $this->$property = $value; }
	}
	
	public function toString(){
		return serialize($this->toArray());
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
	// PRIMARY KEY
	public $id;

	// UNIQUE
	public $topic;

	public $mentions;

	public $created;

	public $modified;


}
