<?php
class followers{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new followers(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	
	public function insert( followers $row ) {
		$sql = 'INSERT INTO followers(user_id, is_following_user_id, created) VALUES (@user_id, @is_following_user_id, @created)';
	}
	
	
	public function update( followers $row ) {
		$sql = 'UPDATE followers SET user_id=@user_id, is_following_user_id=@is_following_user_id, created=@created WHERE user_id=@user_id';
	}
	
	
	public function delete( followers $row ) {
		$sql = 'DELETE FROM followers WHERE user_id=@user_id';
	}
	
	
	public function select( followers $row ) {
		$sql = 'SELECT * FROM followers;';
	}
	
	
	
}