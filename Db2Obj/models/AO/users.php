<?php
class users{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new users(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	
	public function insert( users $row ) {
		$sql = 'INSERT INTO users(id, login, email, pass, reset_code, total_followers, total_followings, created, modified) VALUES (@id, @login, @email, @pass, @reset_code, @total_followers, @total_followings, @created, @modified)';
	}
	
	
	public function update( users $row ) {
		$sql = 'UPDATE users SET id=@id, login=@login, email=@email, pass=@pass, reset_code=@reset_code, total_followers=@total_followers, total_followings=@total_followings, created=@created, modified=@modified WHERE id=@id';
	}
	
	
	public function delete( users $row ) {
		$sql = 'DELETE FROM users WHERE id=@id';
	}
	
	
	public function select( users $row ) {
		$sql = 'SELECT * FROM users;';
	}
	
	
	
}