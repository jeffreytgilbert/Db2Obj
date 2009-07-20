<?php
final class messages_topic_references {
	
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
	// FOREIGN KEY
	public $message_id;

	public $topic_id;

	public $created;


}
