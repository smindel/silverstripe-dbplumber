<?php

class DatabaseBrowser extends LeftAndMain implements NestedController {

	static $url_segment = 'dbplumber';
	
	static $url_rule = '/$Model/$Action/$ID';
	
	static $menu_title = 'DB Plumber';
	
	function handleRequest(SS_HTTPRequest $request) {

		$this->pushCurrent();

		$this->urlParams = $request->allParams();
		$this->request = $request;
		$this->response = new SS_HTTPResponse();

		if($nestedcontroller = $this->getNestedController()) {
			$response = $nestedcontroller->handleRequest($this->request);
		} else {
			$response = parent::handleRequest($request);
		}
		
		if($response instanceof SS_HTTPResponse) {
			$this->response = $response;
		} else {
			$this->response->setBody($body);
		}
		
		$this->popCurrent();
		return $this->response;
	}
	
	function getNestedController() {
		$nestedmodelclass = $this->urlParams['Model'] ? 'DBP_' . ucfirst(strtolower($this->urlParams['Model'])) : false;
		$nestedcontrollerclass = $this->urlParams['Model'] ? 'DBP_' . ucfirst(strtolower($this->urlParams['Model'])) . '_Controller' : false;
		if(class_exists($nestedcontrollerclass)) {
			$object = null;
			if(class_exists($nestedmodelclass) && $this->urlParams['ID']) {
				$object = new $nestedmodelclass($this->urlParams['ID']);
			}
			return new $nestedcontrollerclass($object);
		}
		return false;
	}
	
	function index() {		
		if(Director::is_ajax()) {
			return $this->renderWith('DBP_Database');
		} else {
			return $this->renderWith('LeftAndMain');
		}
	}

	function show() {
		if(Director::is_ajax()) {
			$vars = $this->getRequest()->requestVars();
			$delete = $this->getRequest()->postVar('delete');
			if($delete) { $this->deleteRecords($delete); }
			if(isset($vars['start']) || isset($vars['orderdir']) || isset($vars['orderby'])) return $this->customise($this->Table($this->table))->renderWith('DatabaseBrowser_right_data');
			return $this->renderWith('DatabaseBrowser_right_table');
		} else {
			return $this->renderWith('LeftAndMain');
		}
	}
	
	function deleteRecords($records) {
		if(!Permission::check('ADMIN')) return Security::permissionFailure();
		if(is_array($records)) foreach($records as $record) if(preg_match('/^(\w+)\.(\w+)\.(\d+)$/i', $record, $match)) {
			DB::query('DELETE FROM "' . $match[1] . '" WHERE "' . $match[2] . '" = \'' . $match[3] . '\'');
		}
	}

	function Database() {
		return new DBP_Database();
	}

	function Table() {
		return $this->table;
	}
	
	function Record() {
		return $this->record;
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
						$cell = strlen($cell) > DBP::$truncate_text_longer ? htmlentities(substr($cell, 0, DBP::$truncate_text_longer)) . '<div class="truncated" />' : htmlentities($cell);
						$row->push(new ArrayData(array('Val' => $cell)));
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
