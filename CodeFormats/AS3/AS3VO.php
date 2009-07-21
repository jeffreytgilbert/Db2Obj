<?php

require_once( dirname(__FILE__).'/../../Db2Obj/VO.php' );
require_once( dirname(__FILE__).'/../../Db2Obj/Column.php' );

class AS3VO extends VO{
	public function build(){
		$buffer = '
package{
	[Bindable]
	final class '.$this->Table->name.' {
		'.$this->constructor().'
		'.$this->toString().'
		'.$this->toArray().'
	';
			foreach($this->Table->columns as $Column){
				$buffer .= $this->value($Column);
			}
			$buffer .= '
	}
}
';
		return $buffer;
	}
	
	// unused. hate the getter setter syntax in php.
	protected function value(Column $Column){
		return ''
.($Column->primary?"\t// PRIMARY KEY\n":'')
.(in_array($Column->name, $this->Table->foreign_keys)?"\t// FOREIGN KEY\n":'')
.($Column->unique?"\t// UNIQUE\n":'')
.($Column->fulltext?"\t// FULLTEXT\n":'')
.($Column->index?"\t// INDEX\n":'')
.'		private var _'.$Column->name.';
		public function get '.$Column->name.'():String{ // obviously incorrect until i create a type mapper
			return _'.$Column->name.';
		}
		public function set '.$Column->name.'(v:String):void{
			_'.$Column->name.' = v;
		}
		
';
	}
	
	protected function constructor(){
		return '
		public function __construct(Table=array()){
			for(var property:String in Table){ this[property] = Table[property]; }
		}';
	}
	
	protected function toString(){
		return '
		public function toString():String{
			return "";
		}';
	}
	
	protected function toArray(){
		return '
		public function toArray():Array{
			return [];
		}';
	}
}