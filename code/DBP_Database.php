<?php

class DBP_Database extends ViewableData {
	
	function Name() {
		return DB::getConn()->currentDatabase();
	}

	function Type() {
		return DB::getConn()->getDatabaseServer();
	}

	function Version() {
		return @DB::getConn()->getVersion();
	}

	function Adapter() {
		return get_class(DB::getConn());
	}

	function Tables() {

		$tables = new DataObjectSet();

		foreach(DB::tableList() as $table) $tables->push(new DBP_Table($table));
		
		$tables->sort('id');

		return $tables;
	}
	
}

class DBP_Database_Controller extends DBP_Controller {

	public function __construct() {
		parent::__construct();
		
		$this->dataRecord = new DBP_Database();
		$this->failover = $this->dataRecord;
	}
	
	function Link() {
		return $this->LeftAndMain()->Link() . 'database/show';
	}

	function show(SS_HTTPRequest $request) {
		if(Director::is_ajax()) {
			return $this->renderWith('DBP_Database');
		} else {
			return $this->customise($this->LeftAndMain())->renderWith(array('DBP_Database', 'LeftAndMain'));
			return $this->LeftAndMain();
		}
	}

}