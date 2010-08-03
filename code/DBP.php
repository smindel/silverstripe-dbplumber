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
			case 'oracle':
				if($filter) $filter = sprintf(" WHERE (%s)\n", $filter);
				if(empty($order)) $order = '"ID" ASC';
				if($limit || $offset) {
					$select = sprintf('SELECT %s FROM "%s"%s', $column, $table, $filter);
					if(isset($offset)) $text = "SELECT $column FROM ($select) WHERE ROWNUM BETWEEN $offset AND " . ($offset + $limit);
					else $text = "SELECT $column FROM ($select) WHERE ROWNUM <= " . $limit;
					return $text;
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

	protected $instance;
	protected $model;
	
	public function __construct($id = null) {
		parent::__construct();
		if(!preg_match('/^DBP_([a-z]+)_Controller$/i', get_class($this), $matches)) throw new Exception(get_class($this) . ' can\'t be instantiated');
		$this->model = $model = $matches[1];
		$modelClass = 'DBP_' . $matches[1];
		if(!preg_match('/^[a-z0-9_\.]*$/i', $id)) throw new Exception('Invalid ' . $model . ' ID "' . $request->Param('ID') . '"');
		$this->instance = new $modelClass($id);
	}
	
	function init() {
		parent::init();
		if(!Permission::check('ADMIN')) return Security::permissionFailure($this);
	}
	
	function show() {
		return $this->instance->renderWith('DBP_' . $this->model);
	}

	function Link() {
		foreach(self::$controller_stack as $c) if($c != $this && method_exists($c, 'Link')) return $c->Link();
	}
}