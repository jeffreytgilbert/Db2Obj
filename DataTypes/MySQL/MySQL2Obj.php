<?php

require_once( dirname(__FILE__).'/../../Db2Obj/SQL2Obj.php' );

class MySQL2Obj extends SQL2Obj{
	
	public function convertToStandardType($type){
		switch(strtolower($type)){
			case 'datetime': return 'datetime';
			case 'date': return 'date';
			case 'timestamp': return 'timestamp';
			case 'bool':
			case 'boolean': return 'boolean';
			case 'integer':
			case 'int':
			case 'serial':
			case 'bigint':
			case 'mediumint':
			case 'tinyint':
			case 'smallint': return 'integer';
			case 'enum':
			case 'char':
			case 'varchar':
			case 'text': return 'string';
			case 'blob': // is clob supported? i forget. when i get online i'll make this extensive
			case 'binary': return 'binary';
			case 'set': return 'array';
			case 'decimal':
			case 'dec':
			case 'float':
			case 'double precision':
			case 'double': return 'double';
		}
	}
	
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
					$Col->setType( $this->convertToStandardType($matches[2][0]) );
					$size_array = $this->convertToStandardSizeArray($matches[3][0]);
					$Col->setSize( $size_array[0], $size_array[1] );
					$column = ' '.trim($matches[4][0]).' ';
					unset($matches);
					
					if(substr($Col->name,-3) == '_id'){
						$TableDef->possible_foreign_keys[] = $Col->name;
					}
					
					// match for binary, unsigned, or unsigned zerofill for attributes
					if(stripos($column,'unsigned zerofill')){
						$Col->setAttributes('unsigned zerofill');
					} else if(stripos($column,'')){
						$Col->setAttributes('unsigned');
					} else if(stripos($column,'binary')){
						$Col->setAttributes('binary');
					}
					
					// match "null" or "not null" and define the nullable attribute
					if(stripos($column,'not null')){
						$Col->nullable = false;
					} else {
						$Col->nullable = true;
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
					// Look at this part
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
