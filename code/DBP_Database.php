<?php

class DBP_Database extends ViewableData {
	
	protected $Name;
	protected $Type;
	protected $Version;
	protected $Adapter;

	function __construct() {
		parent::__construct();
		
		$this->Name = DB::getConn()->currentDatabase(),
		$this->Type = DB::getConn()->getDatabaseServer(),
		$this->Version = DB::getConn()->getVersion(),
		$this->Adapter = get_class(DB::getConn()),
	}

	function Tables() {

		$tables = new DataObjectSet();

		foreach(DB::tableList() as $table) $tables->push(new DBP_Table($table));
		
		$tables->sort('Name');

		return $tables;
	}
	
}