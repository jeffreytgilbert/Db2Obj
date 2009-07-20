<?php

require_once( dirname(__FILE__).'/../../Db2Obj/VO.php' );
require_once( dirname(__FILE__).'/../../Db2Obj/Column.php' );

class PHPVO extends VO{
	public function build(){
		$buffer = '<?php
final class '.$this->Table->name.' {
	'.$this->constructor().'
	'.$this->toString().'
	'.$this->toArray().'
';
		foreach($this->Table->columns as $Column){
			$buffer .= $this->getter($Column);
		}
		$buffer .= '
}
';
		return $buffer;
	}
	
	// unused. hate the getter setter syntax in php.
	protected function getter(Column $Column){
		return ''
.($Column->primary?"\t// PRIMARY KEY\n":'')
.(in_array($Column->name, $this->Table->foreign_keys)?"\t// FOREIGN KEY\n":'')
.($Column->unique?"\t// UNIQUE\n":'')
.($Column->fulltext?"\t// FULLTEXT\n":'')
.($Column->index?"\t// INDEX\n":'')
.'	public $'.$Column->name.';

';
	}
	
	// unused
	protected function setter(Column $Column){}
	
	protected function constructor(){
		return '
	public function __construct($table=array()){
		foreach($table as $property => $value){ $this->$property = $value; }
	}';
	}
	
	protected function toString(){
		return '
	public function toString(){
		return serialize($this->toArray());
	}';
	}
	
	protected function toArray(){
		return '
	public function toArray(){
		return get_object_vars($this);
	}';
	}
}