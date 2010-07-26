<?php

class DBP_Record extends ViewableData {
	
	protected $id;
	protected $table;
	protected $data = array();
	
	function __construct($id = null) {
		parent::__construct();
		if(preg_match('/^(\w+)\.(\d+)$/i', $id, $match)) {
			$this->table = $match[1];
			$this->id = $match[2];
			$this->data = DB::query('SELECT * FROM "' . $match[1] . '" WHERE "ID" = \'' . $this->id . '\'')->first();
		} else if(preg_match('/^(\w+)$/i', $id, $match)) {
			$this->table = $match[1];
			foreach(DB::fieldList($match[1]) as $name => $spec) $this->data[$name] = null;
		}
	}
	
	function Data($data = null) {
		if(is_array($data)) $this->data = $data;
		return $this->data;
	}
	
	function Cells() {
		$cells = new DataObjectSet();
		foreach($this->data as $f => $v) {
			$escaped = htmlentities($v,ENT_COMPAT, 'UTF-8');
			$truncate = strlen($escaped) > 64 ? substr($escaped,0,63) . '<span class="truncated">&hellip;</span>' : $escaped;
			$cells->push(
				new ArrayData(
					array(
						'Column' => new DBP_Field($this->table . '.' . $f),
						'Value' => array(
							'raw' => $v, 
							'escaped' => $escaped, 
							'isNull' => is_null($v), 
							'truncated' => $truncate
						)
					)
				)
			);
		}
		return $cells;
	}
	
	function Table() {
		return $this->table;
	}
	
	function ID() {
		return $this->id;
	}
	
	function save() {
		$sets = $keys = $vals = array();
		if($this->id) {
			foreach($this->data as $key => $val) {
				$val = str_replace("'", "''", $val);
				$sets[$key] = "\"$key\" = '$val'";
			}
			$query = 'UPDATE "' . $this->table . '" SET ' . implode(', ', $sets) . ' WHERE "ID" = \'' . $this->id . '\'';
		} else {
			foreach($this->data as $key => $val) {
				if($key == 'ID' && empty($val)) continue;
				$val = str_replace("'", "''", $val);
				$keys[] = "\"$key\"";
				$vals[] = "'$val'";
			}
			$query = 'INSERT INTO "' . $this->table . '" (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $vals) . ')';
		}
		DB:: query($query);
		if($this->id) {
			$this->id = $this->data['ID'] ? $this->data['ID'] : $this->id;
		} else {
			$this->id = DB::getConn()->getGeneratedID($this->table);
		}
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
	
	function DBPLink() {
		return Controller::curr()->Link();
	}
}

class DBP_Record_Controller extends DBP_Controller {

	function delete($request) {
		foreach($request->postVar('ids') as $id) {
			$record = new DBP_Record($id);
			DB:: query('DELETE FROM "' . $record->Table() . '" WHERE "ID" = \'' . $record->ID() . '\'');
		}
		return json_encode(array('msg' => 'Records deleted', 'status' => 'good', 'redirect' => $this->Link() . 'table/index/' . $record->Table()));
	}

	function save($request) {
		$record = new DBP_Record($request->postVar('oldid'));
		foreach($request->postVars() as $key => $val) {
			if(preg_match('/^update_([a-z0-9_]+)$/i', $key, $match)) {
				$data[$match[1]] = "$val";
			}
		}
		$record->data($data);
		$record->save();
		return json_encode(array('msg' => 'Record saved', 'status' => 'good', 'id' => $record->ID()));
	}

	function form($request) {
		return $this->instance->renderWith('DBP_Record_form');
	}

}