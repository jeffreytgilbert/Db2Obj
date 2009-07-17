<?php

/*
 * @TODO There is a bug with handling composite key sets or UNIQUE(`id`,`name`,`pass`); << only gets id, ignores the rest due to comma exploding way early on.
 */

class Table{
	public $name;
	public $primary_keys=array();
	public $foreign_keys=array();
	public $possible_foreign_keys=array();
	public $index_keys=array();
	public $unique_keys=array();
	public $columns=array();
	
	public function __construct($table=array()){
		foreach($table as $property => $value){ $this->$property = $value; }
	}
	
	public function toString(){
		return print_r($this->toArray(),true);
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
}

class Column{
	public $name;
	public $type;
	public $size;
	public $default; // nice to have -- unused
	public $collation; // nice to have -- unused
	public $attributes;
	public $nullable = 'null';
	public $primary = false;
	public $unique = false;
	public $index = false;
	public $fulltext = false;
	public $auto_increment = false;
	public $comments = '';
	
	public function __construct($column=array()){
		foreach($column as $property => $value){ $this->$property = $value; }
	}
	
	public function toString(){
		return print_r($this->toArray(),true);
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
}

abstract class SQL2Obj{
	protected $_sql_file;
	protected $_file_buffer;
	protected $_db = array();
	
	public function __construct($sql_file){
		$this->_sql_file = $sql_file;
		$this->_file_buffer = file_get_contents($sql_file);
	}
	
	public function db(){ return $this->_db; }
	
	public abstract function parseToDataObj();
}

abstract class Obj2Files{
	protected $_file_buffer;
	protected $_dbo;
	public function __construct(SQL2Obj $DataObject){
		$this->_dbo = $DataObject;
	}
	
	public function db(){ return $this->_dbo->db(); }
	public abstract function convert();
	public abstract function createVO($Table);
	public abstract function createAO($Table);
	public function saveFile($name, $path, $contents){
		if(!is_dir(dirname(__FILE__).'/'.$path)){
			mkdir(dirname(__FILE__).'/'.$path, 0777, true);
		}
		file_put_contents(dirname(__FILE__).'/'.$path.'/'.$name, $contents);
	}
}

class MySQL2Obj extends SQL2Obj{

	public function parseToDataObj(){
		$file_buffer = strtolower($this->_file_buffer);
		
		$file_buffer = str_replace("\n", ' ', $file_buffer);
		$file_buffer = str_replace("\t", ' ', $file_buffer);
		$file_buffer = preg_replace('/\s+/si', " ", $file_buffer);
		
		$tables = explode('create table ', $file_buffer);
		
		// first ones throw away since all table definitions start with "create table" and this is exploding by it
		array_shift($tables);
		
		$db = array(); // empty table array
		
		foreach($tables as $table){
			$TableDef = new Table();
			trim($table);
			
			preg_match('/`(.*?)`/', $table, $matches);
			$TableDef->name = trim($matches[1]);
			unset($matches);
			
			$column_pieces = explode('`'.$TableDef->name.'`', $table);
			$table_columns = $column_pieces[1];
			
			$columns = explode(',', $table_columns);
			foreach($columns as $column){
				$Col = new Column();
				
				$column = ' '.trim($column).' ';
				
				// strip out things before a  ` unless it's UNIQUE or PRIMARY, and then parse those.
				if(stripos($column,'unique') == 1){
					$keys = explode('`',$column);
					foreach($keys as $key){
						if(isset($TableDef->columns[$key])){
							$TableDef->columns[$key]->unique = true;
							$TableDef->unique_keys[] = $key;
						}
					}
				} else if(stripos($column,'fulltext') == 1){
					$keys = explode('`',$column);
					foreach($keys as $key){
						if(isset($TableDef->columns[$key])){
							$TableDef->columns[$key]->fulltext = true;
						}
					}
				} else if(stripos($column,'primary') == 1){
					$keys = explode('`',$column);
					foreach($keys as $key){
						if(isset($TableDef->columns[$key])){
							$TableDef->columns[$key]->primary = true;
							$TableDef->primary_keys[] = $key;
						}
					}
				} else if(stripos($column,'index') == 1){
					$keys = explode('`',$column);
					foreach($keys as $key){
						if(isset($TableDef->columns[$key])){
							$TableDef->columns[$key]->index = true;
							$TableDef->index_keys[] = $key;
						}
					}
				} else {
					$original_column = $column;
					// do appropriate stuff
					$column = preg_replace('/.*?`(.*?)/', '`$1', $column,1);
					
					// thank you mr gregory
					preg_match_all('/`([a-zA-Z0-9_]+)`[\s]*([\w]+)[\s]*\(*[\s]*([0-9]*)[\s]*\)*[\s]+([^,]+)./', $column, $matches);
					
					if(!isset($matches[1][0])){
						print_r($original_column);
						continue;
					}
					$Col->name = $matches[1][0];
					$Col->type = $matches[2][0];
					$Col->size = (isset($matches[3][0]) && !empty($matches[3][0]))?(int)$matches[3][0]:null;
					$column = ' '.trim($matches[4][0]).' ';
					unset($matches);
					
					if(substr($Col->name,-3) == '_id'){
						$TableDef->possible_foreign_keys[] = $Col->name;
					}
					
					// match for binary, unsigned, or unsigned zerofill for attributes
					if(stripos($column,'unsigned zerofill')){
						$Col->attributes = 'unsigned zerofill';
					} else if(stripos($column,'unsigned')){
						$Col->attributes = 'unsigned';
					} else if(stripos($column,'binary')){
						$Col->attributes = 'binary';
					}
					
					// match "null" or "not null" and define the nullable attribute
					if(stripos($column,'not null')){
						$Col->nullable = 'not null';
					} else {
						$Col->nullable = 'null';
					}
							
					// match primary, unique, index, or fulltext for index
					if(stripos($column,' primary')){
						$Col->primary = true;
						$TableDef->primary_keys[] = $Col->name;
					} else if(stripos($column,' unique')){
						$Col->unique = true;
						$TableDef->unique_keys[] = $Col->name;
					} else if(stripos($column,' fulltext')){
						$Col->fulltext = true;
					} else if(stripos($column,' index')){
						$Col->index = true;
						$TableDef->index_keys[] = $Col->name;
					}
							
					// match auto_increment for itself
					if(stripos($column,' auto_increment')){
						$Col->auto_increment = true;
					}
					
					// match COMMENT '*' for comments
					preg_match('/\'(.*?)\'/', $column, $matches);
					$Col->comments = isset($matches[1])?trim($matches[1]):null;
					unset($matches);
					
					$Col->extra = $column;
					if(trim($column) == '') echo "Failed: ".$original_column."\n";
					
					$TableDef->columns[$Col->name] = $Col;
				}
				
				foreach($TableDef->possible_foreign_keys as $fk_guess){
					if(in_array($fk_guess, $TableDef->index_keys) || in_array($fk_guess, $TableDef->primary_keys) || in_array($fk_guess, $TableDef->unique_keys)){
						$TableDef->foreign_keys[] = $fk_guess;
					}
				}
			}
			$db[] = $TableDef;
		}
		
		$this->_db = $db;
	}
}

class Obj2PHP extends Obj2Files{
	public function convert(){
		// do stuff to make buffer
		foreach($this->db() as $Table){
			$this->saveFile($Table->name.'.php', 'models/VO', $this->createVO($Table));
			$this->saveFile($Table->name.'.php', 'models/AO', $this->createAO($Table));
		}
		
		// save out to file
		// repeat until done
	}
	
	public function createVO($Table){
			$buffer = '<?php
class '.$Table->name.' {
	
	public function __construct($table=array()){
		foreach($table as $property => $value){ $this->$property = $value; }
	}
	
	public function toString(){
		return print_r($this->toArray(),true);
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
	
';
			
			foreach($Table->columns as $Column){
				$buffer .= ''
.($Column->primary?"\t// PRIMARY KEY\n":'')
.(in_array($Column->name, $Table->foreign_keys)?"\t// FOREIGN KEY\n":'')
.($Column->unique?"\t// UNIQUE\n":'')
.($Column->fulltext?"\t// FULLTEXT\n":'')
.($Column->index?"\t// INDEX\n":'')
.'	private $_'.$Column->name.';
	public function '.$Column->name.'($value = null){
		if(isset($value)){ $_'.$Column->name.' = $value; }
		else { return $_'.$Column->name.'; }
	}
	
';
			}
			$buffer .= ''
.'	
}
';
		return $buffer;
	}
	
	public function createAO($Table){
		
		$columns = array();
		foreach($Table->columns as $Column){
			$columns[] = $Column->name.'=@'.$Column->name;
			$column_names[] = $Column->name;
		}
		$escaped_fields = implode(', ',$columns);
		
		$columns = array();
		if(count($Table->primary_keys)>0){
			foreach($Table->primary_keys as $column_name){
				// woefully inefficient but for what it's doing, doesn't matter
				foreach($Table->columns as $Column){
					if($column_name == $Column->name){
						$columns[] = $Column->name.'=@'.$Column->name;
					}
				}
			}
			$escaped_primary_fields = implode(' AND ',$columns);
		}else{
			$escaped_primary_fields = 1;
		}
		
		$buffer = '<?php
class '.$Table->name.'{
	private static $instance;
	
	public static function getInstance(){
	    if(!self::$instance){ self::$instance = new '.$Table->name.'(); }
	    return self::$instance;
	}
	
	private function __construct() {}

	public function getTableContent() {
		$sql = \'SELECT * FROM '.$Table->name.';\';
	}
	
	public function updateRow( '.$Table->name.' $row ) {
		$sql = \'UPDATE '.$Table->name.' SET '.$escaped_fields.' WHERE '.$escaped_primary_fields.'\';
	}
	
	public function insertRow( '.$Table->name.' $row ) {
		$sql = \'INSERT INTO '.$Table->name.'('.implode(', ',$column_names).') VALUES (@'.implode(', @',$column_names).')\';
	}
	
	public function deleteRow( '.$Table->name.' $row ) {
		$sql = \'DELETE FROM '.$Table->name.' WHERE '.$escaped_primary_fields.'\';
	}
	
}';
		return $buffer;
	}
}

$DbObj = new MySQL2Obj('example.sql');
$DbObj->parseToDataObj();
$PHPObj = new Obj2PHP( $DbObj );
$PHPObj->convert();

echo "Done writing files\n";

//write out files based on foreach of $db structure;