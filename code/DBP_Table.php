<?php

class DBP_Table extends ViewableData {
	
	protected $Name;
	
	static $records_per_page = 10;

	function __construct($name) {
		parent::__construct();
		$this->Name = $name;
	}
	
	function Database() {
		return DB::getConn()->currentDatabase();
	}
	
	function Selected() {
		return Controller::curr()->urlParams['ID'] == $this->Name;
	}
	
	function Fields() {

		$fields = new DataObjectSet();

		foreach(DB::fieldList($this->Name) as $name => $spec) $fields->push(new DBP_Field($this, $name));
		
		return $fields;
	}

	function requestVar($key) {
		$vars = Controller::curr()->getRequest()->requestVars();
		return @$vars[$key];
	}

	function Rows() {

		$vars = Controller::curr()->getRequest()->requestVars();
		
		$start = (Int)@$vars['start'];

		$rows = new DataObjectSet();
		$order = isset($vars['orderby']) && $vars['orderby'] ? "\"{$vars['orderby']}\" " . $vars['orderdir'] : '';
		$result = DB::query(DBP::select('*', $this->Name, null, $order, self::$records_per_page, $start));
		foreach($result as $key => $record) {
			$row = new DataObjectSet();
			foreach($record as $key => $cell) {
				if($key == 'rowNo_hide') continue;
				$row->push(new ArrayData(array('Val' => htmlentities(substr($cell, 0, 100)))));
			}
			$rows->push(new ArrayData(array('Cells' => $row)));
		}
		
		return $rows;
	}

	function Stats() {

		$start = (Int)$this->requestVar('start');
		$total = DB::query(DBP::select('COUNT(*)', $this->Name))->value();
		$end = $start + self::$records_per_page - 1 > $total - 1 ? $total - 1 : $start + self::$records_per_page - 1;
		$stats = array(
			'total' => $total, 
			'start' => $start, 
			'length' => self::$records_per_page,
			'end' => $end,
			'orderlink' => 'orderby=' . $this->requestVar('orderby') . '&orderdir=' . $this->requestVar('orderdir'),
		);
		if($start > 0) { $stats['firstlink'] = 'start=0'; $stats['prevlink'] = 'start=' . ($start - self::$records_per_page); }
		if(isset($stats['prevlink']) && $stats['prevlink'] < 0) $stats['prevlink'] = 'start=0'; 
		if($start + self::$records_per_page < $stats['total']) { $stats['nextlink'] = 'start=' . ($start + self::$records_per_page); $stats['lastlink'] = 'start=' . (floor(($stats['total'] - 1) / self::$records_per_page) * self::$records_per_page); }
		return new ArrayData($stats);
	}

	function Link() {
		return Controller::curr()->Link() . 'show/' . $this->Name;
	}
}