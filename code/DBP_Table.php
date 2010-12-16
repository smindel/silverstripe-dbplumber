<?php

class DBP_Table extends ViewableData {
	
	protected $Name;
	
	function __construct($name) {
		parent::__construct();
		if(preg_match('/^(\w+)$/i', $name, $match)) {
			$this->Name = $match[1];
		}
	}
	
	function Name() {
		return $this->Name;
	}
	
	function getLowerCaseName() {
		return strtolower($this->Name);
	}
	
	function Selected() {
		return preg_match('/' . $this->Name . '(\..+)/', Controller::curr()->urlParams['ID']);
	}
	
	function Fields() {

		$fields = new DataObjectSet();
		foreach(DB::fieldList($this->Name) as $name => $spec) $fields->push(new DBP_Field($this->Name . '.' . $name));
		return $fields;

	}
	
	function truncate() {
		if($this->Name) DB::query('DELETE FROM "' . $this->Name . '"');
	}

	function requestVar($key) {
		$vars = Controller::curr()->getRequest()->requestVars();
		return @$vars[$key];
	}

	function Record() {
		if($id = $this->requestVar('record')) return new DBP_Record($this->Name . ".$id");
		return new DBP_Record($this->Name);
	}

	function Records() {

		$vars = Controller::curr()->getRequest()->requestVars();
		$start = (Int)@$vars['start'];

		$rows = new DataObjectSet();
		$order = isset($vars['orderby']) && $vars['orderby'] ? "\"{$vars['orderby']}\" " . $vars['orderdir'] : '';
		
		$records = DBP_Record::get($this->Name, $order, DatabaseBrowser::$records_per_page, $start);
		$num = DB::query('SELECT COUNT(*) FROM "' . $this->Name . '"')->value();

		$records->setPageLimits($start, DatabaseBrowser::$records_per_page, $num);

		return $records;
	}
	
	function Pagination() {

		$start = (Int)$this->requestVar('start');
		$total = DB::query(DBP_SQLDialect::select('COUNT(*)', $this->Name))->value();
		$end = $start + DatabaseBrowser::$records_per_page - 1 > $total - 1 ? $total - 1 : $start + DatabaseBrowser::$records_per_page - 1;
		$pagination = array(
			'total' => $total, 
			'start' => $start, 
			'length' => DatabaseBrowser::$records_per_page,
			'end' => $end,
			'orderby' => $this->requestVar('orderby'),
			'orderdir' => $this->requestVar('orderdir'),
		);
		if($start > 0) { $pagination['firstlink'] = 'start=0'; $pagination['prevlink'] = 'start=' . ($start - DatabaseBrowser::$records_per_page); }
		if(isset($pagination['prevlink']) && $pagination['prevlink'] < 0) $pagination['prevlink'] = 'start=0'; 
		if($start + DatabaseBrowser::$records_per_page < $pagination['total']) { $pagination['nextlink'] = 'start=' . ($start + DatabaseBrowser::$records_per_page); $pagination['lastlink'] = 'start=' . (floor(($pagination['total'] - 1) / DatabaseBrowser::$records_per_page) * DatabaseBrowser::$records_per_page); }
		return new ArrayData($pagination);
	}

	function DBPLink() {
		return Controller::curr()->Link();
	}

	function forTemplate() {
		return $this->renderWith($this->class);
	}
}

class DBP_Table_Controller extends DBP_Controller {

	function index() {
		return $this->instance->renderWith('DBP_Table_index');
	}

	function truncate() {
		$this->instance->truncate();
		return $this->instance->renderWith('DBP_Table');
	}

}