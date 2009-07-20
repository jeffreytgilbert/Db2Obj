<?php

require_once( dirname(__FILE__).'/../../Db2Obj/Obj2Files.php' );
require_once( dirname(__FILE__).'/PHPAO.php' );
require_once( dirname(__FILE__).'/PHPVO.php' );

class Obj2PHP extends Obj2Files{
	
	public function convert(){
		foreach($this->db() as $Table){
			$VO = new PHPVO($Table);
			$AO = new PHPAO($Table);
			$this->saveFile($Table->name.'.php', 'models/VO', $VO->build());
			$this->saveFile($Table->name.'.php', 'models/AO', $AO->build());
		}
	}
	
}
