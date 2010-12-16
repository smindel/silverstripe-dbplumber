<?php

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