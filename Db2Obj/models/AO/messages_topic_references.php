<?php
class messages_topic_references{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new messages_topic_references(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	
	public function insert( messages_topic_references $row ) {
		$sql = 'INSERT INTO messages_topic_references(message_id, topic_id, created) VALUES (@message_id, @topic_id, @created)';
	}
	
	
	public function update( messages_topic_references $row ) {
		$sql = 'UPDATE messages_topic_references SET message_id=@message_id, topic_id=@topic_id, created=@created WHERE message_id=@message_id';
	}
	
	
	public function delete( messages_topic_references $row ) {
		$sql = 'DELETE FROM messages_topic_references WHERE message_id=@message_id';
	}
	
	
	public function select( messages_topic_references $row ) {
		$sql = 'SELECT * FROM messages_topic_references;';
	}
	
	
	
}