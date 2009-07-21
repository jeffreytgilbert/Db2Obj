<?php

require_once( dirname(__FILE__).'/Table.php' );
require_once( dirname(__FILE__).'/Column.php' );

abstract class VO{
	protected $Table;
	public function __construct(Table $Table){
		$this->Table = $Table;
	}
	
	public abstract function build();
	
	/*
	 * the reason behind making these abstract is to be reasonably sure they'll be created for other languages.
	 * though not required, you'll have to make the function anyway so while you're there why not add the 
	 * functionality unless the language doesn't support it in which case, whatever... php you old cuss! get with the times!
	 */
	protected abstract function value(Column $Column);
	protected abstract function constructor();
	protected abstract function toString();
	protected abstract function toArray();
}