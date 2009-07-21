<?php

require_once( dirname(__FILE__).'/../../Db2Obj/Obj2Files.php' );
require_once( dirname(__FILE__).'/AS3AO.php' );
require_once( dirname(__FILE__).'/AS3VO.php' );

class Obj2PHP extends Obj2Files{
	
	public function convert(){
		foreach($this->db() as $Table){
			$VO = new AS3VO($Table);
			$AO = new AS3AO($Table);
			$this->saveFile($Table->name.'.as', 'models/VO', $VO->build());
			$this->saveFile($Table->name.'.as', 'models/AO', $AO->build());
		}
	}
	
}
