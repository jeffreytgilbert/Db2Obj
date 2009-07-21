<?php

require_once( dirname(__FILE__).'/../../Db2Obj/AO.php' );
require_once( dirname(__FILE__).'/../../Db2Obj/Table.php' );

class AS3AO extends AO{
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
		
		$buffer = '
package{
	class '.$this->Table->name.'{
		private static var instance:'.$this->Table->name.';
	   
		public static function getInstance():'.$this->Table->name.'{
			if (!instance) instance = new '.$this->Table->name.'();
			return instance;
		}
		
		/** 
		 * Constructor 
		 * @param lock The Singleton lock class to pevent outside instantiation. 
		 */  
		public function '.$this->Table->name.'( lock:SingletonLock ){
		    // Normal construction can continue here
		}
		
		'.$this->insert().'
		
		'.$this->update().'
		
		'.$this->delete().'
		
		'.$this->select().'
		
		'.$this->search().'
		
	}
}

class SingletonLock{} // end class  
';
		return $buffer;
	}
	
	protected function insert(){
		return '
	public function insert( '.$this->Table->name.' $row ):void {
		var sql:String = \'INSERT INTO '.$this->Table->name.'('.implode(', ',$this->column_names).') VALUES (@'.implode(', @',$this->column_names).')\';
	}';
	}
	
	protected function update(){
		return '
	public function update( '.$this->Table->name.' $row ):void {
		var sql:String = \'UPDATE '.$this->Table->name.' SET '.$this->escaped_fields.' WHERE '.$this->escaped_primary_fields.'\';
	}';
	}
	
	protected function delete(){
		return '
	public function delete( '.$this->Table->name.' $row ):void {
		var sql:String = \'DELETE FROM '.$this->Table->name.' WHERE '.$this->escaped_primary_fields.'\';
	}';
	}
	
	protected function select(){
		return '
	public function select( '.$this->Table->name.' $row ):void {
		var sql:String = \'SELECT * FROM '.$this->Table->name.';\';
	}';
	}
	
	protected function search(){
		return ;
	}
	
}