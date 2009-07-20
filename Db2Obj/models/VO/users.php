<?php
final class users {
	
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
	public $login;

	// UNIQUE
	public $email;

	public $pass;

	public $reset_code;

	public $total_followers;

	public $total_followings;

	public $created;

	public $modified;


}
