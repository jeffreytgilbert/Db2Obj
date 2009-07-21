<?php

require_once( dirname(__FILE__).'/SQL2Obj.php' );

abstract class Obj2Files{
	protected $_file_buffer;
	protected $_dbo;
	
	public function __construct(SQL2Obj $DataObject){
		$this->_dbo = $DataObject;
	}
	
	public function db(){ return $this->_dbo->db(); }
	
	public abstract function convert();
	
	public function saveFile($name, $path, $contents){
		if(!is_dir(dirname(__FILE__).'/../'.$path)){
			mkdir(dirname(__FILE__).'/../'.$path, 0777, true);
		}
		file_put_contents(dirname(__FILE__).'/../'.$path.'/'.$name, $contents);
	}
}
