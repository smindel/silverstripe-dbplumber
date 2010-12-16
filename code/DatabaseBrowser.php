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
		'truncate' => 'ADMIN',
		'drop' => 'ADMIN',
		'showartefact' => 'ADMIN',
	);
	
	// limit DBPlumber to certain environments
	static $trusted_envs = array('live', 'test', 'dev');

	// limit DBPlumber to trusted IPs
	static $trusted_ips = null;

	// deactivate DBPlumber, useful in combination with _ss_environment.php
	static $activated = true;
	
	// expose config in info tab, password will always be omitted
	static $expose_config = true;
	
	public static $truncate_text_longer = 50;

	public static $records_per_page = 10;

	function canView() {
		if(self::$trusted_envs && !in_array(Director::get_environment_type(), self::$trusted_envs)) return false;
		if(self::$trusted_ips && !in_array($_SERVER['REMOTE_ADDR'], self::$trusted_ips)) return false;
		if(!self::$activated) return false;
		return parent::canView();
	}
	
	// hide DBPlumber from the CMS menu. Useful if DBPlumber is accessible but
	// you don't want it to appear in the CMS but only access it through http://your-domain.com/admin/dbplumber
	static function hide_from_menu() {
		CMSMenu::remove_menu_item('DatabaseBrowser');
	}
	
	function init() {		
		parent::init();
		
		// somehow themed css gets mixed in, remove it
		$reqbe = Requirements::backend();
		foreach($reqbe->get_css() as $file => $val) if(preg_match('/^themes\//', $file)) Requirements::block($file);
		

		Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.core.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.widget.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.mouse.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.tabs.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.button.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.position.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.dialog.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.draggable.js');

		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.core.css');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.dialog.css');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.theme.css');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/base/jquery.ui.tabs.css');
		
		Requirements::clear('jsparty/prototype.js');

		Requirements::javascript("dbplumber/javascript/DatabaseBrowser.js");
		Requirements::javascript("dbplumber/thirdparty/jquery.event.drag-1.4.js");
		Requirements::javascript("dbplumber/thirdparty/jquery.kiketable.colsizable-1.1.js");
		Requirements::javascript("dbplumber/thirdparty/jquery.textarea-expander.js");
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
	
	function index(SS_HTTPRequest $request) {
		if(Director::is_ajax()) {
			return $this->delegate($request);
		} else {
			return $this;
		}
	}

	function show(SS_HTTPRequest $request) { return $this->delegate($request); }
	function delete(SS_HTTPRequest $request) { return $this->delegate($request); }
	function execute(SS_HTTPRequest $request) { return $this->delegate($request); }
	function form(SS_HTTPRequest $request) { return $this->delegate($request); }
	function save(SS_HTTPRequest $request) { return $this->delegate($request); }
	function export(SS_HTTPRequest $request) { return $this->delegate($request); }
	function import(SS_HTTPRequest $request) { return $this->delegate($request); }
	function truncate(SS_HTTPRequest $request) { return $this->delegate($request); }
	function drop(SS_HTTPRequest $request) { return $this->delegate($request); }
	function showartefact(SS_HTTPRequest $request) { return $this->delegate($request); }
	
	protected function delegate(SS_HTTPRequest $request) {
		if(array_search(strtolower($request->Param('Control')), self::$managed_models) === false) throw new Exception('Invalid Sub Controller "' . $request->Param('Control') . '"');
		if(!preg_match('/^[a-z0-9_\.]*$/i', $request->Param('ID'))) throw new Exception('Invalid ' . $request->Param('Control') . ' ID "' . $request->Param('ID') . '"');
		$subcontrollerclass = 'DBP_' . ucfirst(strtolower($request->Param('Control'))) . '_Controller';
		$subcontroller = new $subcontrollerclass($request->Param('ID'));
		$response = $subcontroller->handleRequest($request);
		return $response;
	}
}