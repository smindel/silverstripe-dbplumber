<?php

class DatabaseBrowser extends LeftAndMain {

	static $url_segment = 'dbplumber';
	
	static $url_rule = '/$Action/$ID';
	
	static $menu_title = 'DB Plumber';

	static $records_per_page = 10;
	
	protected $table;
	
	function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.widget.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.tabs.js');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.theme.css');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.tabs.css');
		
		Requirements::javascript("dbplumber/javascript/DatabaseBrowser.js");
		Requirements::css("dbplumber/css/DatabaseBrowser_left.css");
		Requirements::css("dbplumber/css/DatabaseBrowser_right.css");
		
		$this->table = $this->urlParams['ID'];
	}
	
	function index() {		
		if(Director::is_ajax()) {
			return $this->renderWith('DatabaseBrowser_right_db');
		} else {
			return $this->renderWith('LeftAndMain');
		}
	}

	function show() {
		if(Director::is_ajax()) {
			$vars = $this->getRequest()->requestVars();
			if(isset($vars['start']) || isset($vars['orderdir']) || isset($vars['orderby'])) return $this->customise($this->Table($this->table))->renderWith('DatabaseBrowser_right_data');
			return $this->renderWith('DatabaseBrowser_right_table');
		} else {
			return $this->renderWith('LeftAndMain');
		}
	}

	function execute() {
		if(Director::is_ajax()) {
			return $this->renderWith('DatabaseBrowser_right_sql');
		} else {
			return $this->renderWith('LeftAndMain');
		}
	}

	function Database() {
		return new ArrayData(array(
			'Name' => DB::getConn()->currentDatabase(),
			'Type' => DB::getConn()->getDatabaseServer(),
			'Version' => DB::getConn()->getVersion(),
			'Adapter' => get_class(DB::getConn()),
		));
	}

	function Tables() {

		$tables = new DataObjectSet();

		foreach(DB::tableList() as $table) {
			$tables->push(
				new ArrayData(
					array(
						'Name' => $table,
						'Selected' => $this->table == $table,
						'Link' => $this->Link() . 'show/' . $table,
					)
				)
			);
		}
		
		$tables->sort('Name');

		return $tables;
	}
	
	function Table() {
		
		if(!$this->table) return false;
		
		$vars = $this->getRequest()->requestVars();
		
		$start = (Int)@$vars['start'];
		$total = DB::query('SELECT COUNT(*) FROM "' . $this->table . '"')->value();
		$end = $start + self::$records_per_page - 1 > $total - 1 ? $total - 1 : $start + self::$records_per_page - 1;
		$stats = array(
			'total' => $total, 
			'start' => $start, 
			'length' => self::$records_per_page,
			'end' => $end,
			'orderlink' => 'orderby=' . @$vars['orderby'] . '&orderdir=' . @$vars['orderdir'],
		);
		if($start > 0) { $stats['firstlink'] = 'start=0'; $stats['prevlink'] = 'start=' . ($start - self::$records_per_page); }
		if(isset($stats['prevlink']) && $stats['prevlink'] < 0) $stats['prevlink'] = 'start=0'; 
		if($start + self::$records_per_page < $stats['total']) { $stats['nextlink'] = 'start=' . ($start + self::$records_per_page); $stats['lastlink'] = 'start=' . (floor(($stats['total'] - 1) / self::$records_per_page) * self::$records_per_page); }
		$stats = new ArrayData($stats);

		$fields = new DataObjectSet();
		foreach(DB::fieldList($this->table) as $name => $spec) $fields->push(new ArrayData(array(
			'Name' => $name,
			'Spec' => $spec,
			'Link' => $this->Link() . 'show/' . $this->table . '?start=' . $start . '&orderby=' . $name . '&orderdir=' . (@$vars['orderby'] == $name && @$vars['orderdir'] == 'ASC' ? 'DESC' : 'ASC'),
		)));

		$rows = new DataObjectSet();
		$o = isset($vars['orderby']) && $vars['orderby'] ? " ORDER BY \"{$vars['orderby']}\" " . $vars['orderdir'] : '';
		$result = DB::query('SELECT * FROM "' . $this->table . '"' . $o . ' LIMIT ' . $start . ', ' . self::$records_per_page);
		foreach($result as $record) {
			$row = new DataObjectSet();
			foreach($record as $cell) $row->push(new ArrayData(array('Val' => htmlentities(substr($cell, 0, 100)))));
			$rows->push(new ArrayData(array('Cells' => $row)));
		}
		
		return new ArrayData(
			array(
				'Name' => $this->table,
				'Fields' => $fields,
				'Rows' => $rows,
				'Stats' => $stats,
				'Link' => $this->Link(),
			)			
		);
	}
	
	function Sql() {

		$vars = $this->getRequest()->requestVars();
		if(empty($vars['query'])) return false;
		$error = false;
		$query = $vars['query'];
		$fields = new DataObjectSet();
		$rows = new DataObjectSet();
		set_error_handler('exception_error_handler');
		try {
			foreach(DB::getConn()->query($query, E_USER_NOTICE) as $record) {
				$head = false;
				$row = new DataObjectSet();
				foreach($record as $name => $cell) {
					if(!$head) $fields->push(new ArrayData(array('Name' => $name)));
					$row->push(new ArrayData(array('Val' => htmlentities(substr($cell, 0, 100)))));
				}
				$head = true;
				$rows->push(new ArrayData(array('Cells' => $row)));
			}
		} catch(Exception $e) {
			$error = $e->getMessage();
		}
		restore_error_handler();
		
		return new ArrayData(
			array(
				'Query' => $query,
				'Fields' => $fields,
				'Rows' => $rows,
				'Error' => $error,
			)			
		);
	}
	
}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new Exception($errstr);
}
