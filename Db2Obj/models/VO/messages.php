<?php
final class messages {
	
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

	public $user_id;

	public $message;

	public $created;


}
