<?php

class DBP {
	
	public static $truncate_text_longer = 50;

	public static $records_per_page = 10;
	
	static function select($column, $table, $filter = null, $order = null, $limit = null, $offset = null) {
		switch(DB::getConn()->getDatabaseServer()) {
			case 'mssql':
				if($filter) $filter = sprintf(" WHERE (%s)\n", $filter);
				if(empty($order)) $order = '"ID" ASC';
				if($limit || $offset) {
					return sprintf('WITH results AS (
						    SELECT 
						        %s,
						        rowNo_hide = ROW_NUMBER() OVER( ORDER BY %s )
						    FROM "%s"
							%s
						) 
						SELECT * 
						FROM results
						WHERE rowNo_hide between %d and %d
						', $column, $order, $table, $filter, $offset, $offset + $limit);
				} else {
					return sprintf('SELECT %s FROM "%s"%s', $column, $table, $filter);
				}
			default:
				if($filter) $filter = sprintf(" WHERE (%s)", $filter);
				if($order) $order = sprintf(" ORDER BY %s", $order);
				if($limit) $limit = isset($offset) ? sprintf(" LIMIT %d OFFSET %d", $limit, $offset) : sprintf(" LIMIT %d", $limit);
				return sprintf('SELECT %s FROM "%s"%s%s%s', $column, $table, $filter, $order, $limit);
		}
	}
}

class DBP_Controller extends Controller {

	protected $dataRecord;

	static $url_handlers = array(
		'$Model//$Action//$ID' => 'handleAction',
	);
	
	public function __construct($dataRecord = null) {
		parent::__construct();
		
		if($dataRecord && preg_match('/^(DBP_\w+)_Controller$/', get_class($this), $matches)) {
			$model = $matches[1];
			$dataRecord = new $matches[1]($dataRecord);
		}
		
		$this->dataRecord = $dataRecord;
		$this->failover = $this->dataRecord;
	}

	function data() {
		return $this->dataRecord;
	}
	
	function LeftAndMain() {
		foreach(self::$controller_stack as $c) if($c instanceof LeftAndMain) return $c;
		return false;
	}

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
	
}