<?php

class DBP_Record extends ViewableData {
	
	protected $ID;
	protected $Table;
	protected $data;
	
	function __construct($table, $id) {
		parent::__construct();
		$this->Table = $table;
		$this->ID = $ID;
		$this->data = DB::query('SELECT * FROM "' . $table . '" WHERE "ID" = \'' . $id . '\'')->first();
	}
	
	function Data() {
		return $this->data;
	}
	
	function Table() {
		return $this->Table;
	}
}