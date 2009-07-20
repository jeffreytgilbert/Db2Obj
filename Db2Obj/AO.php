<?php

require_once( dirname(__FILE__).'/Table.php' );

abstract class AO{
	protected $Table;
	public function __construct(Table $Table){
		$this->Table = $Table;
	}
	
	public abstract function build();
	
	protected abstract function insert();
	protected abstract function update();
	protected abstract function delete();
	protected abstract function select();
	protected abstract function search();
	
}