<?php

class DBP_Record extends ViewableData {
	
	protected $id;
	protected $table;
	protected $data;
	
	function __construct($id = null) {
		parent::__construct();
		if(preg_match('/^(\w+)\.(\d+)$/i', $id, $match)) {
			$this->table = new DBP_Table($match[1]);
			$this->id = $match[2];
			$this->data = DB::query('SELECT * FROM "' . $this->table->Name() . '" WHERE "ID" = \'' . $this->id . '\'')->first();
		}
	}
	
	function Data($data = null) {
		if(is_array($data)) $this->data = $data;
		return $this->data;
	}
	
	function Cells() {
		$cells = new DataObjectSet();
		foreach($this->data as $f => $v) {
			$v = htmlentities($v);
			$v = strlen($v) > 64 ? substr($v,0,63) . '<span class="truncated">&hellip;</span>' : $v;
			$cells->push(new ArrayData(array('Column' => new DBP_Field($this->table . '.' . $f), 'Value' => $v)));
		}
		return $cells;
	}
	
	function Table() {
		return $this->table;
	}
	
	function ID() {
		return $this->id;
	}
	
	static function get($table, $order, $limit, $start) {
		$return = new DataObjectSet();
		$result = DB::query(DBP::select('*', $table, null, $order, $limit, $start));
		foreach($result as $r) {
			$rec = new DBP_Record();
			$rec->id = $r['ID'];
			$rec->table = $table;
			$rec->data = $r;
			$return->push($rec);
		}
		return $return;
	}
	
}

class DBP_Record_Controller extends DBP_Controller {

	function delete($request) {
		foreach($request->postVar('delete') as $id) {
			$record = new DBP_Record($id);
			DB:: query('DELETE FROM "' . $record->Table()->Name() . '" WHERE "ID" = \'' . $record->ID() . '\'');
		}
		return json_encode(array('msg' => 'Records deleted', 'status' => 'good', 'redirect' => $record->Table()->DBPLink() . 'table/index/' . $record->Table()->Name()));
	}

	function form($request) {
		Debug::dump($this->instance);
		return $this->instance->renderWith('DBP_Record_form');
	}

}