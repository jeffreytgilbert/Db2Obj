<?php
class messages{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new messages(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	
	public function insert( messages $row ) {
		$sql = 'INSERT INTO messages(id, user_id, message, created) VALUES (@id, @user_id, @message, @created)';
	}
	
	
	public function update( messages $row ) {
		$sql = 'UPDATE messages SET id=@id, user_id=@user_id, message=@message, created=@created WHERE id=@id';
	}
	
	
	public function delete( messages $row ) {
		$sql = 'DELETE FROM messages WHERE id=@id';
	}
	
	
	public function select( messages $row ) {
		$sql = 'SELECT * FROM messages;';
	}
	
	
	
}