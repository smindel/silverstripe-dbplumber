<?php

class DBP_Field extends ViewableData {
	
	protected $id;
	protected $Table;
	protected $value;
	
	function __construct($id) {
		parent::__construct();
		if(preg_match('/^(\w+)\.(\w+)$/i', $id, $match)) {
			$this->id = $id;
		}
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
	
	function type() {
		return preg_match('/^\w+/i', $this->Spec(), $match) ? strtolower($match[0]) : false;
	}
	
	function val() {
		return $this->value;
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