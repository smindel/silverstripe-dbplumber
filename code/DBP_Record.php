<?php

class DBP_Record extends ViewableData {
	
	protected $id;
	protected $table;
	protected $data;
	
	function __construct($id) {
		parent::__construct();
		if(preg_match('/^(\w+)\.(\d+)$/i', $id, $match)) {
			$this->table = new DBP_Table($match[1]);
			$this->id = $match[2];
			$this->data = DB::query('SELECT * FROM "' . $this->table . '" WHERE "ID" = \'' . $this->id . '\'')->first();
		}
	}
	
	function Data() {
		return $this->data;
	}
	
	function Table() {
		return $this->table;
	}
}

class DBP_Record_Controller extends DBP_Controller {

}