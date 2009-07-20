<?php
class messages_user_references{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new messages_user_references(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	
	public function insert( messages_user_references $row ) {
		$sql = 'INSERT INTO messages_user_references(message_id, user_id, created) VALUES (@message_id, @user_id, @created)';
	}
	
	
	public function update( messages_user_references $row ) {
		$sql = 'UPDATE messages_user_references SET message_id=@message_id, user_id=@user_id, created=@created WHERE message_id=@message_id';
	}
	
	
	public function delete( messages_user_references $row ) {
		$sql = 'DELETE FROM messages_user_references WHERE message_id=@message_id';
	}
	
	
	public function select( messages_user_references $row ) {
		$sql = 'SELECT * FROM messages_user_references;';
	}
	
	
	
}