<?php

/*
 * @TODO There is a bug with handling composite key sets or UNIQUE(`id`,`name`,`pass`); << only gets id, ignores the rest due to comma exploding way early on.
 */

class Table{
	public $table_name;
	public $primary_keys=array();
	public $foreign_keys=array();
	public $possible_foreign_keys=array();
	public $index_keys=array();
	public $unique_keys=array();
	public $columns=array();
	
	public function toString(){
		return print_r($this->toArray(),true);
	}
	
	public function toArray(){
		return get_object_vars($this);
	}
}

class Column{
	public $column_name;
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
	protected $_dbObj;
	public function __construct(SQL2Obj $DataObject){
		$_dbObj = $DataObject;
	}
	public abstract function convert();
	protected function saveToFiles(){
		
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
			$TableDef->table_name = trim($matches[1]);
			unset($matches);
			
			$column_pieces = explode('`'.$TableDef->table_name.'`', $table);
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
					// do appropriate stuff
					$original_column = $column = preg_replace('/.*?`(.*?)/', '`$1', $column,1);
					
					// thank you mr gregory
					preg_match_all('/`([a-zA-Z0-9_]+)`[\s]*([\w]+)[\s]*\(*[\s]*([0-9]*)[\s]*\)*[\s]+([^,]+)./', $column, $matches);
					// $matches[0]; // bunk as always
					$Col->column_name = $matches[1][0];
					$Col->type = $matches[2][0];
					$Col->size = (isset($matches[3][0]) && !empty($matches[3][0]))?(int)$matches[3][0]:null;
					$column = ' '.trim($matches[4][0]).' ';
					unset($matches);
					
					if(substr($Col->column_name,-3) == '_id'){
						$TableDef->possible_foreign_keys[] = $Col->column_name;
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
						$TableDef->primary_keys[] = $Col->column_name;
					} else if(stripos($column,' unique')){
						$Col->unique = true;
						$TableDef->unique_keys[] = $Col->column_name;
					} else if(stripos($column,' fulltext')){
						$Col->fulltext = true;
					} else if(stripos($column,' index')){
						$Col->index = true;
						$TableDef->index_keys[] = $Col->column_name;
					}
							
					// match auto_increment for itself
					if(stripos($column,' auto_increment')){
						$Col->auto_increment = true;
					}
					
					// match COMMENT '*' for comments
					preg_match('/\'(.*?)\'/', $column, $matches);
					$Col->comments = trim($matches[1]);
					unset($matches);
					
					$Col->extra = $column;
					if(trim($column) == '') echo $original_column;
					
					$TableDef->columns[$Col->column_name] = $Col;
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
		foreach($this->_dbObj as $table){
			foreach($table->columns as $column){
				$column->column_name;
			}
		}
		// save out to file
		// repeat until done
		$this->saveToFiles();
	}
}

$DbObj = new MySQL2Obj('example.sql');
$DbObj->parseToDataObj();
$PHPObj = new Obj2PHP( $DbObj );
$PHPObj->convert();

//write out files based on foreach of $db structure;