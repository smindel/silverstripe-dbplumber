<?php

class DBP_Field extends ViewableData {
	
	protected $Name;
	protected $Table;
	
	function __construct($table, $name) {
		parent::__construct();
		$this->Table = $table;
		$this->Name = $name;
	}
	
	function Spec() {
		$fl = DB::fieldList($this->Table->Name);
		if(is_array($fl[$this->Name])) {
			$out ='';
			foreach($fl[$this->Name] as $key => $val) $out .= "$key: $val<br />";
			return $out;
		} else {
			return $fl[$this->Name];
		}
	}
	
	function Table() {
		return $this->Table;
	}

	function Ordered() {
		$vars = Controller::curr()->getRequest()->requestVars();
		if(empty($vars['orderby']) || $vars['orderby'] != $this->Name) return false;
		$vars['orderdir'] = (isset($vars['orderdir']) && $vars['orderdir'] == 'DESC') ? 'DESC' : 'ASC';
		return $vars['orderdir'];
	}
}