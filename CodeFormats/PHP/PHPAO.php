<?php

require_once( dirname(__FILE__).'/../../Db2Obj/AO.php' );
require_once( dirname(__FILE__).'/../../Db2Obj/Table.php' );

class PHPAO extends AO{
	private $escaped_primary_fields;
	private $escaped_fields;
	private $column_names;
	
	public function build(){
		
		$columns = array();
		foreach($this->Table->columns as $Column){
			$columns[] = $Column->name.'=@'.$Column->name;
			$this->column_names[] = $Column->name;
		}
		$this->escaped_fields = implode(', ',$columns);
		
		$columns = array();
		if(count($this->Table->primary_keys)>0){
			foreach($this->Table->primary_keys as $column_name){
				// woefully inefficient but for what it's doing, doesn't matter
				foreach($this->Table->columns as $Column){
					if($column_name == $Column->name){
						$columns[] = $Column->name.'=@'.$Column->name;
					}
				}
			}
			$this->escaped_primary_fields = implode(' AND ',$columns);
		}else{
			$this->escaped_primary_fields = 1;
		}
		
		$buffer = '<?php
class '.$this->Table->name.'{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new '.$this->Table->name.'(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	'.$this->insert().'
	
	'.$this->update().'
	
	'.$this->delete().'
	
	'.$this->select().'
	
	'.$this->search().'
	
}';
		return $buffer;
	}
	
	protected function insert(){
		return '
	public function insert( '.$this->Table->name.' $row ) {
		$sql = \'INSERT INTO '.$this->Table->name.'('.implode(', ',$this->column_names).') VALUES (@'.implode(', @',$this->column_names).')\';
	}';
	}
	
	protected function update(){
		return '
	public function update( '.$this->Table->name.' $row ) {
		$sql = \'UPDATE '.$this->Table->name.' SET '.$this->escaped_fields.' WHERE '.$this->escaped_primary_fields.'\';
	}';
	}
	
	protected function delete(){
		return '
	public function delete( '.$this->Table->name.' $row ) {
		$sql = \'DELETE FROM '.$this->Table->name.' WHERE '.$this->escaped_primary_fields.'\';
	}';
	}
	
	protected function select(){
		return '
	public function select( '.$this->Table->name.' $row ) {
		$sql = \'SELECT * FROM '.$this->Table->name.';\';
	}';
	}
	
	protected function search(){
		return ;
	}
	
}