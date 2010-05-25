<?php

class DatabaseBrowser extends LeftAndMain {

	static $url_segment = 'dbplumber';
	
	static $url_rule = '/$Action/$ID';
	
	static $menu_title = 'DB Plumber';

	function init() {
		parent::init();
		if(!Permission::check('ADMIN')) return Security::permissionFailure();

		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.widget.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.tabs.js');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.theme.css');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.tabs.css');
		
		Requirements::javascript("dbplumber/javascript/DatabaseBrowser.js");
		Requirements::javascript("dbplumber/thirdparty/jquery.event.drag-1.4.js");
		Requirements::javascript("dbplumber/thirdparty/jquery.kiketable.colsizable-1.1.js");
		Requirements::css("dbplumber/thirdparty/jquery.kiketable.colsizable-1.1.css");
		Requirements::css("dbplumber/css/DatabaseBrowser_left.css");
		Requirements::css("dbplumber/css/DatabaseBrowser_right.css");

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

	function Database() {
		return new DBP_Database();
	}

	function Table() {
		return $this->urlParams['ID'] ? new DBP_Table($this->urlParams['ID']) : false;
	}

	function execute() {
		if(Director::is_ajax()) {
			return $this->renderWith('DatabaseBrowser_right_sql');
		} else {
			return $this->renderWith('LeftAndMain');
		}
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
			$result = DB::getConn()->query($query, E_USER_NOTICE);
		} catch(Exception $e) {
			$msg = new ArrayData(array('text' => $e->getMessage(), 'type' => 'error'));
		}
		restore_error_handler();

		if($result) {
			if(0) {
				// @todo: add routine to determine the number of affected records on a write query
				// no hook for the result ;(
				// any ideas? 
				$msg = new ArrayData(array('text' => '4711 records affected', 'type' => 'highlight'));
			} else {
				foreach($result as $record) {
					$row = new DataObjectSet();
					foreach($record as $name => $cell) {
						if(empty($head[$name])) {
							$fields->push(new ArrayData(array('Name' => $name)));
							$head[$name] = true;
						}
						$row->push(new ArrayData(array('Val' => htmlentities(substr($cell, 0, 50)))));
					}
					$rows->push(new ArrayData(array('Cells' => $row)));
					// highlight
				}
			}
		}
		
		return new ArrayData(
			array(
				'Query' => $query,
				'Fields' => $fields,
				'Rows' => $rows,
				'Message' => $msg,
			)			
		);
	}
}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new Exception($errstr);
}
