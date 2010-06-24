<?php

class DatabaseBrowser extends LeftAndMain {

	static $url_segment = 'dbplumber';
	
	static $url_rule = '/$Control/$Action/$ID';

	static $url_handlers = array(
		'$Control/$Action/$ID' => '$Action'
	);

	static $managed_models = array('database', 'table', 'record');

	static $menu_title = 'DB Plumber';
	
	static $allowed_actions = array(
		'show' => 'ADMIN',
		'delete' => 'ADMIN',
		'execute' => 'ADMIN',
		'form' => 'ADMIN',
		'save' => 'ADMIN',
		'export' => 'ADMIN',
		'import' => 'ADMIN',
	);

	function init() {
		parent::init();

		Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
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
	
	function Database() {
		return new DBP_Database();
	}
	
	function Table() {
	 	return preg_match('/^(\w+)/', $this->urlParams['ID'], $matches) ? new DBP_Table($matches[1]) : false;
	}
	
	function index(SS_HTTP_Request $request) {
		if(Director::is_ajax()) {
			return $this->delegate($request);
		} else {
			return $this;
		}
	}

	function show(SS_HTTP_Request $request) { return $this->delegate($request); }
	function delete(SS_HTTP_Request $request) { return $this->delegate($request); }
	function execute(SS_HTTP_Request $request) { return $this->delegate($request); }
	function form(SS_HTTP_Request $request) { return $this->delegate($request); }
	function save(SS_HTTP_Request $request) { return $this->delegate($request); }
	function export(SS_HTTP_Request $request) { return $this->delegate($request); }
	function import(SS_HTTP_Request $request) { return $this->delegate($request); }
	
	protected function delegate(SS_HTTP_Request $request) {
		if(array_search(strtolower($request->Param('Control')), self::$managed_models) === false) throw new Exception('Invalid Sub Controller "' . $request->Param('Control') . '"');
		if(!preg_match('/^[a-z0-9_\.]*$/i', $request->Param('ID'))) throw new Exception('Invalid ' . $request->Param('Control') . ' ID "' . $request->Param('ID') . '"');
		$subcontrollerclass = 'DBP_' . ucfirst(strtolower($request->Param('Control'))) . '_Controller';
		$subcontroller = new $subcontrollerclass($request->Param('ID'));
		$response = $subcontroller->handleRequest($request);
		return $response;
	}
}