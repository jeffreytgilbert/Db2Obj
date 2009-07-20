<?php
class topics{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new topics(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	
	public function insert( topics $row ) {
		$sql = 'INSERT INTO topics(id, topic, mentions, created, modified) VALUES (@id, @topic, @mentions, @created, @modified)';
	}
	
	
	public function update( topics $row ) {
		$sql = 'UPDATE topics SET id=@id, topic=@topic, mentions=@mentions, created=@created, modified=@modified WHERE id=@id';
	}
	
	
	public function delete( topics $row ) {
		$sql = 'DELETE FROM topics WHERE id=@id';
	}
	
	
	public function select( topics $row ) {
		$sql = 'SELECT * FROM topics;';
	}
	
	
	
}